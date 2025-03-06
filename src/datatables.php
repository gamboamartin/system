<?php
namespace gamboamartin\system;
use config\generales;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\system\datatables\acciones;
use gamboamartin\system\datatables\filtros;
use gamboamartin\system\datatables\validacion_dt;
use gamboamartin\system\html_controler\params;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;
use Throwable;

class datatables{
    private errores $error;
    private validacion $valida;
    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();
    }


    /**
     * REG
     * Obtiene las acciones permitidas para un grupo de usuario y una sección específica.
     *
     * Este método realiza una serie de validaciones y genera filtros para obtener las acciones que están permitidas
     * para un grupo determinado, excluyendo algunas acciones específicas si así se indican.
     *
     * **Pasos clave:**
     * 1. Valida que la sección no esté vacía utilizando el método `valida_data_column`.
     * 2. Genera un filtro de acciones permitidas a partir de la sección utilizando el método `filtro_accion_permitida`.
     * 3. Obtiene un filtro para excluir acciones específicas mediante `not_in_accion`.
     * 4. Ejecuta una consulta en el modelo `adm_accion_grupo` para obtener las acciones permitidas.
     * 5. Retorna las acciones que corresponden, o un error si ocurre algún problema en los pasos anteriores.
     *
     * @param PDO $link Instancia de la conexión a la base de datos.
     * @param string $seccion Nombre de la sección para la cual se desean obtener las acciones permitidas.
     *                        Este parámetro es crucial y no puede ser vacío.
     *
     * @param array $not_actions Lista de identificadores de acciones que deben ser excluidas de los resultados.
     *                            Este parámetro es opcional, con un valor predeterminado de un arreglo vacío.
     *
     * @param array $columnas Lista de columnas que se desean obtener de la base de datos. Si se omiten, se utilizarán las columnas predeterminadas.
     *
     * @return array Retorna un arreglo con las acciones permitidas o un objeto `stdClass` con un error en caso de fallo.
     *
     * @example Ejemplo 1: Obtener acciones permitidas para una sección "facturacion", sin excluir acciones.
     * ```php
     * $link = new PDO(...); // Conexión a la base de datos
     * $seccion = 'facturacion';
     * $acciones = $this->acciones_permitidas($link, $seccion);
     * print_r($acciones);
     * // Salida: Un arreglo con las acciones permitidas para el grupo de usuario en la sección "facturacion".
     * ```
     *
     * @example Ejemplo 2: Obtener acciones permitidas para la misma sección, excluyendo algunas acciones.
     * ```php
     * $link = new PDO(...); // Conexión a la base de datos
     * $seccion = 'facturacion';
     * $not_actions = ['action1', 'action2'];
     * $acciones = $this->acciones_permitidas($link, $seccion, $not_actions);
     * print_r($acciones);
     * // Salida: Un arreglo con las acciones permitidas excluyendo 'action1' y 'action2'.
     * ```
     *
     * @example Ejemplo 3: Manejo de error al pasar una sección vacía.
     * ```php
     * $link = new PDO(...); // Conexión a la base de datos
     * $seccion = '';
     * $acciones = $this->acciones_permitidas($link, $seccion);
     * print_r($acciones);
     * // Salida: Un arreglo con un mensaje de error indicando que la sección no puede estar vacía.
     * ```
     */
    final public function acciones_permitidas(PDO $link, string $seccion, array $not_actions = array(),
                                              array $columnas = array()): array
    {
        // 1. Validar la sección
        $valida = (new validacion_dt())->valida_data_column(seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        // 2. Obtener el filtro de acciones permitidas
        $filtro = (new filtros())->filtro_accion_permitida(seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener not in', data: $filtro);
        }

        // 3. Excluir acciones específicas
        $not_in = $this->not_in_accion(not_actions: $not_actions);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener not in', data: $not_in);
        }

        // 4. Obtener las acciones permitidas desde el modelo
        $r_accion_grupo = (new adm_accion_grupo($link))->filtro_and(
            columnas: $columnas, filtro: $filtro, not_in: $not_in);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener acciones', data: $r_accion_grupo);
        }

        // 5. Retornar los resultados
        return $r_accion_grupo->registros;
    }


    final public function ajusta_data_result(array $acciones_permitidas, array $data_result, html $html_base,
                                             string $seccion){
        foreach ($data_result['registros'] as $key => $row){


            $links = $this->integra_links(acciones_permitidas: $acciones_permitidas,
                data_result:  $data_result,html_base:  $html_base,key:  $key,row:  $row,seccion:  $seccion);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link', data: $links);
            }

            $data_result['registros'][$key] = array_merge($row,$links);
        }
        return $data_result;
    }


    /**
     * REG
     * Inicializa las columnas de un DataTable basándose en la información proporcionada en `$datatables` o genera nuevas columnas.
     *
     * Esta función primero verifica si `$datatables` ya contiene columnas definidas. Si es así, valida que el formato sea correcto.
     * Si no se encuentra ninguna definición de columnas, genera nuevas columnas utilizando la función `columns_datatable`
     * basándose en los parámetros proporcionados (`rows_lista` y `seccion`).
     *
     * ## Pasos clave:
     * 1. **Validación de parámetros**:
     *    - Se valida que el parámetro `$seccion` no esté vacío.
     *    - Se verifica si `$datatables` tiene la propiedad `columns` y si esta es un arreglo.
     * 2. **Generación de columnas**:
     *    - Si `$datatables->columns` no está definido o no es un arreglo, se genera un nuevo conjunto de columnas a partir de `rows_lista` y `seccion`.
     * 3. **Retorno de columnas**:
     *    - Si todo es correcto, se retorna el arreglo de columnas.
     *    - Si ocurre un error durante la validación o generación, se retorna un mensaje de error adecuado.
     *
     * ## Ejemplo de uso:
     *
     * ### Ejemplo 1: Generación de columnas a partir de `datatables` con columnas predefinidas
     * ```php
     * $datatables = new stdClass();
     * $datatables->columns = [
     *     ['titulo' => 'Nombre', 'type' => 'text'],
     *     ['titulo' => 'Email', 'type' => 'text']
     * ];
     * $rows_lista = ['nombre', 'email'];
     * $seccion = 'usuarios';
     *
     * $resultado = $this->column_datable_init($datatables, $rows_lista, $seccion);
     *
     * // El resultado será:
     * [
     *     ['titulo' => 'Nombre', 'type' => 'text'],
     *     ['titulo' => 'Email', 'type' => 'text']
     * ]
     * ```
     *
     * ### Ejemplo 2: Generación de columnas cuando no están definidas en `datatables`
     * ```php
     * $datatables = new stdClass();
     * $rows_lista = ['nombre', 'email'];
     * $seccion = 'usuarios';
     *
     * $resultado = $this->column_datable_init($datatables, $rows_lista, $seccion);
     *
     * // El resultado será un arreglo generado con los títulos formateados:
     * [
     *     'usuarios_nombre' => ['titulo' => 'Nombre'],
     *     'usuarios_email'  => ['titulo' => 'Email']
     * ]
     * ```
     *
     * ### Ejemplo 3: Error cuando `seccion` está vacío
     * ```php
     * $datatables = new stdClass();
     * $rows_lista = ['nombre', 'email'];
     * $seccion = '';
     *
     * $resultado = $this->column_datable_init($datatables, $rows_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error seccion debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 4: Error cuando `datatables->columns` no es un arreglo
     * ```php
     * $datatables = new stdClass();
     * $datatables->columns = 'no es un arreglo';
     * $rows_lista = ['nombre', 'email'];
     * $seccion = 'usuarios';
     *
     * $resultado = $this->column_datable_init($datatables, $rows_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error $datatables->columns debe se run array',
     * //     'data' => ...
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param stdClass $datatables Objeto que contiene la configuración de los datatables. Si tiene la propiedad `columns`,
     *                              debe ser un arreglo de columnas ya definidas.
     * @param array $rows_lista Un arreglo con las claves de las filas que se utilizarán para generar los títulos de las columnas.
     *                          Cada clave de fila debe ser una cadena no vacía.
     * @param string $seccion La sección a la que pertenece la columna. Se espera que sea una cadena no vacía.
     *
     * ## Retorno:
     * @return array El arreglo de columnas actualizado, con las claves de columna y sus respectivos títulos.
     *               Si ocurre un error, se retorna un arreglo con el mensaje de error y los datos relacionados.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * [
     *     'usuarios_nombre' => ['titulo' => 'Nombre'],
     *     'usuarios_email'  => ['titulo' => 'Email']
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 1.0.0
     */
    private function column_datable_init(stdClass $datatables, array $rows_lista, string $seccion): array
    {
        // Validación de la sección
        if ($seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data: $seccion);
        }

        // Verificación de las columnas en $datatables
        if (isset($datatables->columns)) {
            if (!is_array($datatables->columns)) {
                return $this->error->error(mensaje: 'Error $datatables->columns debe ser un array ', data: $datatables);
            }
            $columns = $datatables->columns;
        } else {
            // Si no hay columnas definidas, generamos nuevas columnas
            $columns = $this->columns_datatable(rows_lista: $rows_lista, seccion: $seccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);
            }
        }

        return $columns;
    }


    /**
     * Inicializa una columna
     * @param array|string $column Columna a inicializar
     * @param string $indice Indice de row
     * @return stdClass|array
     * @version 0.144.33
     */
    private function column_init(array|string $column, string $indice): stdClass|array
    {
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = new stdClass();
        $column_obj->title = is_string($column)? $column:$indice;
        $column_obj->data = $indice;
        return $column_obj;
    }

    /**
     * Integra el titulo de columna
     * @param array $column Columna a integrar titulo
     * @param stdClass $column_obj Conjunto de datos de retorno
     * @param string $indice Key del elemento a integrar
     * @return stdClass|array
     * @version 0.146.34
     */
    private function column_titulo(array $column, stdClass $column_obj, string $indice): stdClass|array
    {
        $keys = array('titulo');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar column', data:  $valida);
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }
        $column_obj->title = $indice;
        if(is_string($column["titulo"])){
            $column_obj->title = $column["titulo"];
        }
        return$column_obj;
    }

    /**
     * Genera columnas para datatable
     * @param array $columns Columnas
     * @param array $datatable Objeto inicializado
     * @return array
     * @version 0.150.33
     */
    private function columns(array $columns, array $datatable, bool $multi_selects = false): array
    {
        $index_button = -1;

        if ($multi_selects === true){
            $check = array("check" => array("titulo" => " "));
            $columns = array_merge($check, $columns);
        }

        foreach ($columns as $indice => $column){

            $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
            }

            $data = $this->genera_column(column: $column,columns:  $columns,datatable:  $datatable,
                indice:  $indice, index_button: $index_button);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column', data:  $data);
            }
            $datatable = $data->datatable;
            $index_button = $data->index_button;
        }

        return $datatable;
    }

    /**
     * REG
     * Asigna títulos a las columnas de un DataTable a partir de las claves de fila y la sección proporcionadas.
     *
     * Esta función recorre un conjunto de claves de fila (`rows_lista`) y, para cada una, genera un título legible para la columna
     * correspondiente en un DataTable. Los títulos se generan utilizando la función `columns_title` para formatear las claves y
     * se asignan a las columnas dentro de un arreglo `$columns`. Se verifica que tanto la clave de fila como la sección no estén vacías.
     *
     * ## Pasos clave:
     * 1. **Validación de parámetros**:
     *    - Se valida que la sección no esté vacía.
     *    - Para cada clave de fila en `rows_lista`, se valida que no esté vacía.
     * 2. **Generación de títulos de columnas**:
     *    - Utiliza la función `columns_title` para generar y asignar los títulos a cada columna.
     * 3. **Construcción del arreglo `$columns`**:
     *    - El arreglo `$columns` es actualizado con cada título generado, usando las claves de fila y la sección.
     *
     * ## Ejemplo de uso:
     *
     * ### Ejemplo 1: Generación exitosa de títulos de columnas
     * ```php
     * $rows_lista = ['usuario_nombre', 'usuario_email'];
     * $seccion = 'usuarios';
     *
     * $resultado = $this->columns_datatable($rows_lista, $seccion);
     *
     * // El resultado será un arreglo con los títulos de las columnas:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre'],
     *     'usuarios_usuario_email'  => ['titulo' => 'Usuario Email']
     * ]
     * ```
     *
     * ### Ejemplo 2: Error al pasar un parámetro vacío para la sección
     * ```php
     * $rows_lista = ['usuario_nombre', 'usuario_email'];
     * $seccion = '';
     *
     * $resultado = $this->columns_datatable($rows_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error seccion debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al pasar un parámetro vacío para la clave de fila
     * ```php
     * $rows_lista = ['', 'usuario_email'];
     * $seccion = 'usuarios';
     *
     * $resultado = $this->columns_datatable($rows_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $rows_lista Un arreglo con las claves de las filas que se utilizarán para generar los títulos de las columnas.
     *                          Cada clave de fila debe ser una cadena no vacía.
     * @param string $seccion La sección a la que pertenece la columna. Se espera que sea una cadena no vacía.
     *
     * ## Retorno:
     * @return array El arreglo de columnas actualizado, con las claves de columna y sus respectivos títulos.
     *               Si ocurre un error, se retorna un arreglo con el mensaje de error y los datos relacionados.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre'],
     *     'usuarios_usuario_email'  => ['titulo' => 'Usuario Email']
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 1.0.0
     */
    private function columns_datatable(array $rows_lista, string $seccion): array
    {
        // Validación de la sección
        if ($seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data: $seccion);
        }

        $columns = array();

        // Iteración sobre cada clave de fila
        foreach ($rows_lista as $key_row_lista) {
            $key_row_lista = trim($key_row_lista);

            // Validación de la clave de fila
            if ($key_row_lista === '') {
                return $this->error->error(
                    mensaje: 'Error $key_row_lista debe ser un string con datos', data: $key_row_lista);
            }

            // Generación del título de la columna
            $columns = $this->columns_title(
                columns: $columns, key_row_lista: $key_row_lista, seccion: $seccion);

            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar column titulo ', data: $columns);
            }
        }

        return $columns;
    }


    /**
     * Genera las columnas para datatables
     * @param array|string $column Columna
     * @param string $indice Indice o key
     * @param int $targets n columna
     * @param string $type Typo button or text
     * @return stdClass|array
     * @version 0.143.33
     */
    private function columns_defs(array|string $column, string $indice, int $targets, string $type): stdClass|array
    {

        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $rendered = $this->rendered(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rendered', data:  $rendered);
        }
        $type = trim($type);
        if($type === ''){
            $type = 'text';
        }


        $columns_defs_obj = new stdClass();
        $columns_defs_obj->targets = $targets;
        $columns_defs_obj->data = null;
        $columns_defs_obj->type = $type;
        $columns_defs_obj->rendered = $rendered;

        if ($indice === "check"){
            $columns_defs_obj->defaultContent = '';
            $columns_defs_obj->orderable = false;
            $columns_defs_obj->className = 'select-checkbox';
        }

        array_unshift($columns_defs_obj->rendered,$indice);

        return $columns_defs_obj;
    }

    /**
     * REG
     * Genera y configura las columnas para un DataTable, y opcionalmente agrega acciones dependiendo del tipo (`datatable` o no).
     *
     * Esta función valida el parámetro `$seccion`, genera las columnas necesarias (usando la función `column_datable_init`),
     * y luego, si el tipo es `datatable`, agrega las acciones permitidas para cada columna a través de la función `acciones_columnas`.
     * En caso de errores en cualquier etapa, retorna un mensaje de error detallado.
     *
     * ## Pasos clave:
     * 1. **Validación de parámetros**:
     *    - Se valida que `$seccion` no esté vacío.
     *    - Se pasa el parámetro `$datatables` junto con los parámetros `$rows_lista` y `$seccion` para inicializar las columnas con `column_datable_init`.
     * 2. **Generación de columnas**:
     *    - Si el tipo es `datatable`, se integran las acciones para las columnas utilizando `acciones_columnas`.
     * 3. **Retorno de columnas**:
     *    - Si todo es correcto, se retorna el arreglo de columnas.
     *    - Si ocurre un error, se retorna un mensaje de error adecuado.
     *
     * ## Ejemplo de uso:
     *
     * ### Ejemplo 1: Generación de columnas con acciones
     * ```php
     * $datatables = new stdClass();
     * $datatables->columns = array(
     *     'column1' => array('title' => 'Column 1'),
     *     'column2' => array('title' => 'Column 2')
     * );
     * $link = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');
     * $not_actions = array('action1', 'action2');
     * $rows_lista = array('column1', 'column2');
     * $seccion = 'seccion_test';
     *
     * $columns = $this->columns_dt($datatables, $link, $not_actions, $rows_lista, $seccion);
     * // Resultado esperado: Un arreglo con las columnas configuradas, y si el tipo es `datatable`, con las acciones integradas.
     * ```
     *
     * ### Ejemplo 2: Validación de parámetros incorrectos
     * ```php
     * $datatables = new stdClass();
     * $datatables->columns = array();
     * $link = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');
     * $not_actions = array();
     * $rows_lista = array('column1');
     * $seccion = '';  // Sección vacía, generará un error
     *
     * $columns = $this->columns_dt($datatables, $link, $not_actions, $rows_lista, $seccion);
     * // Resultado esperado: Un error indicando que la sección no puede estar vacía.
     * ```
     *
     * ### Ejemplo 3: Sin acciones
     * ```php
     * $datatables = new stdClass();
     * $datatables->columns = array(
     *     'column1' => array('title' => 'Column 1')
     * );
     * $link = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');
     * $not_actions = array();
     * $rows_lista = array('column1');
     * $seccion = 'seccion_test';
     *
     * $columns = $this->columns_dt($datatables, $link, $not_actions, $rows_lista, $seccion, 'other');
     * // Resultado esperado: Un arreglo con las columnas configuradas, pero sin las acciones añadidas.
     * ```
     *
     * ## Parámetros:
     *
     * @param stdClass $datatables Objeto que contiene la configuración del DataTable, incluyendo las columnas.
     * @param PDO $link Conexión a la base de datos.
     * @param array $not_actions Acciones que deben ser excluidas de la configuración.
     * @param array $rows_lista Lista de las filas/columnas que se deben mostrar en el DataTable.
     * @param string $seccion Sección actual que se está procesando. No puede ser una cadena vacía.
     * @param string $type Tipo de procesamiento de columnas. Por defecto es `datatable`, pero puede ser otro valor para evitar la adición de acciones.
     *
     * ## Retorno:
     * @return array Un arreglo con las columnas configuradas y las acciones permitidas (si es tipo `datatable`).
     *               Si ocurre un error, se retorna un arreglo con la información del error.
     */

    private function columns_dt(stdClass $datatables, PDO $link, array $not_actions, array $rows_lista,
                                string $seccion, string $type = "datatable"): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }

        $columns = $this->column_datable_init(datatables: $datatables,rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);

        }

        if ($type === "datatable"){
            $columns = (new acciones())->acciones_columnas(columns: $columns, link: $link, seccion: $seccion,
                not_actions: $not_actions);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar acciones ', data: $columns);
            }
        }

        return $columns;
    }

    /**
     * REG
     * Asigna un título formateado a una columna de un DataTable y actualiza el arreglo de columnas.
     *
     * Esta función utiliza la clave de la fila (`key_row_lista`) y la sección (`seccion`) para generar un título legible para la columna en un DataTable.
     * El título generado se asigna a la columna correspondiente dentro del arreglo `$columns`.
     * Utiliza la función `titulo_column_datatable` para formatear el título.
     *
     * ## Pasos clave:
     * 1. **Validación de los parámetros**:
     *    - Se valida que la clave de la fila no esté vacía (`$key_row_lista`).
     *    - Se valida que la sección no esté vacía (`$seccion`).
     * 2. **Generación del título**:
     *    - El título se genera a partir de la clave de la fila, reemplazando los guiones bajos por espacios y capitalizando la primera letra de cada palabra.
     * 3. **Asignación del título**:
     *    - El título generado se asigna a la columna correspondiente dentro del arreglo `$columns`, bajo la clave construida con la sección y la clave de fila.
     *
     * ## Ejemplo de uso:
     *
     * ### Ejemplo 1: Generación exitosa del título de la columna
     * ```php
     * $columns = [
     *     'usuario_nombre' => ['titulo' => '']
     * ];
     * $key_row_lista = 'usuario_nombre';
     * $seccion = 'usuarios';
     *
     * $resultado = $this->columns_title($columns, $key_row_lista, $seccion);
     *
     * // El resultado será:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre']
     * ]
     * ```
     *
     * ### Ejemplo 2: Error al pasar un parámetro vacío para la clave de fila
     * ```php
     * $columns = [];
     * $key_row_lista = '';
     * $seccion = 'usuarios';
     *
     * $resultado = $this->columns_title($columns, $key_row_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al pasar un parámetro vacío para la sección
     * ```php
     * $columns = [];
     * $key_row_lista = 'usuario_nombre';
     * $seccion = '';
     *
     * $resultado = $this->columns_title($columns, $key_row_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error seccion debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $columns El arreglo de columnas que contiene los títulos de las columnas del DataTable.
     *                       Este arreglo será modificado para incluir el nuevo título generado.
     * @param string $key_row_lista La clave de la fila que representa el nombre de la columna. Se espera que
     *                              esta clave esté en formato `snake_case`, y se usará para generar el título.
     * @param string $seccion La sección a la que pertenece la columna. Este parámetro se usa para construir la clave
     *                        en el arreglo `$columns` (ej. 'usuarios_usuario_nombre').
     *
     * ## Retorno:
     * @return array El arreglo de columnas actualizado con el nuevo título asignado a la columna correspondiente.
     *               Si ocurre un error, se devuelve un arreglo con el mensaje de error y los datos relacionados.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre']
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 1.0.0
     */
    private function columns_title(array $columns, string $key_row_lista, string $seccion): array
    {
        // Validación de key_row_lista
        $key_row_lista = trim($key_row_lista);
        if ($key_row_lista === '') {
            return $this->error->error(
                mensaje: 'Error $key_row_lista debe ser un string con datos', data: $key_row_lista);
        }

        // Validación de seccion
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data: $seccion);
        }

        // Generación del título a partir de key_row_lista
        $columns = $this->titulo_column_datatable(
            columns: $columns, key_row_lista: $key_row_lista, seccion: $seccion);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar column titulo ', data: $columns);
        }

        return $columns;
    }


    /**
     * REG
     * Genera un enlace de acción basado en los permisos del usuario y los datos de la fila seleccionada.
     *
     * Este método valida la existencia de los datos necesarios en el conjunto de resultados (`$data_result`),
     * verifica los permisos de la acción (`$adm_accion_grupo`), y genera un enlace HTML utilizando la función
     * `database_link`. Devuelve un objeto `stdClass` con el enlace generado y la acción correspondiente.
     *
     * ## Pasos clave:
     * 1. **Validación de `$data_result`**: Se verifica que el conjunto de resultados contenga datos válidos.
     * 2. **Validación de `$adm_accion_grupo`**: Se comprueba que la acción es válida y que el usuario tiene permisos.
     * 3. **Validación de `$session_id`**: Se asegura que la sesión esté activa antes de generar el enlace.
     * 4. **Obtención del estilo del botón**: Se extrae el estilo CSS del botón desde `html_controler`.
     * 5. **Generación del enlace**: Se llama a `database_link` para crear el botón HTML.
     * 6. **Retorno del enlace generado**: Se devuelve un objeto con el enlace HTML y la acción asociada.
     *
     * ## Parámetros:
     *
     * @param array $adm_accion_grupo
     *      Arreglo con los detalles de la acción permitida. Debe incluir claves como:
     *      - `adm_accion_descripcion` (string) → Nombre de la acción (Ej: "editar").
     *      - `adm_accion_titulo` (string) → Título del botón (Ej: "Editar Usuario").
     *      - `adm_seccion_descripcion` (string) → Sección del sistema donde se encuentra la acción.
     *      - `adm_accion_icono` (string) → (Opcional) Icono CSS de la acción.
     *
     * @param array $data_result
     *      Arreglo que contiene los datos de la tabla, debe incluir:
     *      - `registros` (array) → Lista de registros obtenidos de la base de datos.
     *      - `n_registros` (int) → Número total de registros disponibles.
     *
     * @param html $html_base
     *      Objeto de la clase `html` utilizado para generar el botón HTML.
     *
     * @param string $key
     *      Clave que identifica el registro dentro de `$data_result['registros']`. Debe ser un string no vacío.
     *
     * @param int $registro_id
     *      ID del registro asociado al botón (Ej: ID de un usuario o factura).
     *
     * @param array $params_get
     *      Arreglo de parámetros adicionales que se añadirán al enlace como variables GET.
     *
     * @return array|stdClass
     *      Devuelve un objeto `stdClass` con las siguientes propiedades:
     *      - `link_con_id` (string) → HTML del botón generado.
     *      - `accion` (string) → Descripción de la acción generada.
     *
     *      En caso de error, devuelve un array con un mensaje de error.
     *
     * ## Ejemplo de uso:
     *
     * ```php
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => 'editar',
     *     'adm_accion_titulo' => 'Editar Usuario',
     *     'adm_seccion_descripcion' => 'usuarios',
     *     'adm_accion_icono' => 'fa fa-edit'
     * ];
     *
     * $data_result = [
     *     'registros' => [
     *         'usuario_1' => ['id' => 10, 'nombre' => 'Juan Pérez']
     *     ],
     *     'n_registros' => 1
     * ];
     *
     * $html = new html();
     * $key = 'usuario_1';
     * $registro_id = 10;
     * $params_get = ['id' => 10, 'token' => 'abcd1234'];
     *
     * $resultado = $this->data_link($adm_accion_grupo, $data_result, $html, $key, $registro_id, $params_get);
     * print_r($resultado);
     * ```
     *
     * ## Ejemplo de salida esperada:
     * ```php
     * stdClass Object
     * (
     *     [link_con_id] => '<a href="usuarios.php?accion=editar&id=10&token=abcd1234" class="btn btn-primary">
     *                      <i class="fa fa-edit"></i> Editar Usuario</a>'
     *     [accion] => 'editar'
     * )
     * ```
     *
     * ## Ejemplo de error:
     * ```php
     * $resultado = $this->data_link([], [], $html, '', 0, []);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada en caso de error:**
     * ```php
     * Array
     * (
     *     [mensaje] => 'Error data_result[registros] no existe'
     *     [data] => Array()
     * )
     * ```
     */
    final public function data_link(array $adm_accion_grupo, array $data_result, html $html_base, string $key,
                                    int $registro_id, array $params_get = array()): array|stdClass
    {

        // 1. Validación de `data_result`
        if(!isset($data_result['registros'])){
            return $this->error->error(mensaje: 'Error data_result[registros] no existe',data:  $data_result);
        }
        if(!is_array($data_result['registros'])){
            return $this->error->error(mensaje: 'Error data_result[registros] debe ser un array',data:  $data_result);
        }

        // 2. Validación de `key`
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key);
        }

        if(!isset($data_result['registros'][$key])){
            return $this->error->error(mensaje: 'Error $data_result[registros][key] no existe',data:  $data_result);
        }
        if(!is_array($data_result['registros'][$key])){
            return $this->error->error(mensaje: 'Error $data_result[registros][key] debe ser un array',
                data:  $data_result);
        }
        if(count($data_result['registros'][$key]) === 0){
            return $this->error->error(mensaje: 'Error $data_result[registros][key] esta vacio', data:  $data_result);
        }

        // 3. Validación de `adm_accion_grupo`
        $valida = (new html_controler(html: $html_base))->valida_boton_data_accion(accion_permitida: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo',data:  $valida);
        }

        $valida = $this->valida_data_permiso(adm_accion_grupo: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }

        // 4. Validar la sesión activa
        $session_id = (new generales())->session_id;
        if($session_id === ''){
            return $this->error->error(mensaje: 'Error la $session_id esta vacia', data: $session_id);
        }

        // 5. Obtener estilo del botón
        $style = (new html_controler(html: $html_base))->style_btn(
            accion_permitida: $adm_accion_grupo, row: $data_result['registros'][$key]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }

        // 6. Generar enlace de botón
        $data_link = $this->database_link(adm_accion_grupo: $adm_accion_grupo,
            html: (new html_controler(html: $html_base)), params_get: $params_get, registro_id: $registro_id,
            style: $style);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data para link', data: $data_link);
        }

        return $data_link;
    }


    /**
     * REG
     * Genera un enlace de acción basado en los permisos de un usuario y los estilos especificados.
     *
     * Este método construye un enlace de botón con los parámetros proporcionados, validando los permisos
     * del usuario y configurando los atributos HTML necesarios. Devuelve un objeto con el enlace generado
     * y la acción correspondiente.
     *
     * ## Pasos clave:
     * 1. **Validación de permisos:** Se valida que el usuario tenga permisos para ejecutar la acción.
     * 2. **Limpieza y validación del estilo:** Se asegura de que `$style` sea un string no vacío.
     * 3. **Validación del ID de sesión:** Se verifica que la sesión esté iniciada.
     * 4. **Configuración de iconos y atributos adicionales:** Se procesan los iconos y estilos opcionales.
     * 5. **Generación del botón:** Se usa `button_href` para construir el enlace del botón.
     * 6. **Retorno del enlace generado y la acción:** Se devuelve un objeto con los datos finales.
     *
     * ## Parámetros:
     *
     * @param array $adm_accion_grupo
     *      Arreglo con los detalles de la acción permitida. Debe incluir claves como:
     *      - `adm_accion_descripcion` (string) → Nombre de la acción (Ej: "editar").
     *      - `adm_accion_titulo` (string) → Título del botón (Ej: "Editar Usuario").
     *      - `adm_seccion_descripcion` (string) → Sección del sistema donde se encuentra la acción.
     *      - `adm_accion_icono` (string) → (Opcional) Icono CSS de la acción.
     *      - `adm_accion_id_css` (string) → (Opcional) ID del botón CSS.
     *      - `adm_accion_target` (string) → (Opcional) Target del enlace (`_blank`, `_self`, etc.).
     *
     * @param html_controler $html
     *      Objeto de la clase `html_controler` utilizado para generar el botón HTML.
     *
     * @param array $params_get
     *      Arreglo de parámetros adicionales que se añadirán al enlace como variables GET.
     *
     * @param int $registro_id
     *      ID del registro asociado al botón (Ej: ID de un usuario o factura).
     *
     * @param string $style
     *      Clase de estilo CSS que se aplicará al botón. Debe ser un string no vacío.
     *
     * @param array $styles
     *      Arreglo asociativo de estilos CSS opcionales, por defecto:
     *      ```php
     *      array('margin-left' => '2px', 'margin-bottom' => '2px')
     *      ```
     *
     * @return array|stdClass
     *      Devuelve un objeto `stdClass` con las siguientes propiedades:
     *      - `link_con_id` (string) → HTML del botón generado.
     *      - `accion` (string) → Descripción de la acción generada.
     *
     *      En caso de error, devuelve un array con un mensaje de error.
     *
     * ## Ejemplo de uso:
     *
     * ```php
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => 'editar',
     *     'adm_accion_titulo' => 'Editar Usuario',
     *     'adm_seccion_descripcion' => 'usuarios',
     *     'adm_accion_icono' => 'fa fa-edit'
     * ];
     *
     * $html = new html_controler();
     * $params_get = ['id' => 10, 'token' => 'abcd1234'];
     * $registro_id = 10;
     * $style = 'btn btn-primary';
     *
     * $resultado = $this->database_link($adm_accion_grupo, $html, $params_get, $registro_id, $style);
     * print_r($resultado);
     * ```
     *
     * ## Ejemplo de salida esperada:
     * ```php
     * stdClass Object
     * (
     *     [link_con_id] => '<a href="usuarios.php?accion=editar&id=10&token=abcd1234" class="btn btn-primary">
     *                      <i class="fa fa-edit"></i> Editar Usuario</a>'
     *     [accion] => 'editar'
     * )
     * ```
     *
     * ## Ejemplo de error:
     * ```php
     * $resultado = $this->database_link([], $html, [], 0, '');
     * print_r($resultado);
     * ```
     *
     * **Salida esperada en caso de error:**
     * ```php
     * Array
     * (
     *     [mensaje] => 'Error al validar adm_accion_grupo'
     *     [data] => Array()
     * )
     * ```
     */
    private function database_link(array $adm_accion_grupo, html_controler $html, array $params_get, int $registro_id,
                                   string $style,
                                   array $styles = array('margin-left'=>'2px', 'margin-bottom'=>'2px') ): array|stdClass
    {
        // 1. Validación de los datos de la acción permitida
        $valida = $this->valida_data_permiso(adm_accion_grupo: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }

        // 2. Validar que el estilo no esté vacío
        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error la $style esta vacia', data: $style);
        }

        // 3. Validar que la sesión esté iniciada
        $session_id = (new generales())->session_id;
        if($session_id === ''){
            return $this->error->error(mensaje: 'Error la $session_id esta vacia', data: $session_id);
        }

        // 4. Configurar icono
        if(!isset($adm_accion_grupo['adm_accion_icono'])){
            $adm_accion_grupo['adm_accion_icono']  = '';
        }
        $icon = trim($adm_accion_grupo['adm_accion_icono']);

        // 5. Obtener configuración del botón
        $data_icon = (new params())->data_icon(adm_accion: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }

        // 6. Configurar atributos opcionales
        $id_css = $adm_accion_grupo['adm_accion_id_css'] ?? '';
        $css_extra = $adm_accion_grupo['adm_accion_id_css'] ?? '';
        $onclick_event = $adm_accion_grupo['adm_accion_id_css'] ?? '';

        $target = $adm_accion_grupo['adm_accion_target'] ?? '';

        // 7. Generar el botón HTML
        $link_con_id = $html->button_href(
            accion: $adm_accion_grupo['adm_accion_descripcion'],
            etiqueta: $adm_accion_grupo['adm_accion_titulo'],
            registro_id: $registro_id,
            seccion: $adm_accion_grupo['adm_seccion_descripcion'],
            style: $style,
            css_extra: $css_extra,
            cols: -1,
            icon: $icon,
            id_css: $id_css,
            muestra_icono_btn: $data_icon->muestra_icono_btn,
            muestra_titulo_btn: $data_icon->muestra_titulo_btn,
            onclick_event: $onclick_event,
            params: $params_get,
            styles: $styles,
            target: $target
        );

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar button', data: $link_con_id);
        }

        // 8. Retornar datos generados
        $accion = $adm_accion_grupo['adm_accion_descripcion'];

        $data = new stdClass();
        $data->link_con_id = $link_con_id;
        $data->accion = $accion;

        return $data;
    }


    /**
     * Genera la estructura para datatables
     * @param array $columns Columnas
     * @param array $filtro Filtros
     * @param string $identificador
     * @param array $data
     * @param array $in
     * @param bool $multi_selects
     * @param bool $menu_active
     * @param string $type
     * @return array
     * @version 0.152.33
     */
    final public function datatable(array $columns, array $filtro = array(),string $identificador = ".datatable",
                                    array $data = array(), array $in = array(), bool $multi_selects = false,
                                    bool $menu_active = false, string $type = "datatable"): array
    {
        $datatable = (new \gamboamartin\system\datatables\init())->init_datatable(filtro:$filtro, identificador: $identificador,
            data: $data,in: $in, multi_selects: $multi_selects, type : $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datatable', data:  $datatable);
        }

        $datatable = $this->columns(columns: $columns, datatable: $datatable, multi_selects: $multi_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columns', data:  $datatable);
        }

        if ($menu_active){
            if ($datatable['columnDefs'][count($datatable['columnDefs']) - 1]->type === 'button') {
                $datatable['columnDefs'][count($datatable['columnDefs']) - 1]->type = 'menu';
            }
        }

        return $datatable;
    }

    /**
     * REG
     * Inicializa la estructura base para un DataTable, configurando los filtros, las columnas y otras opciones.
     *
     * Este método se encarga de inicializar el filtro de datos y las columnas para un DataTable. Además,
     * se encarga de configurar las opciones relacionadas como la selección múltiple (multi_selects) y el menú activo (menu_active).
     * Si alguno de los parámetros no está correctamente configurado, el método devolverá un error.
     *
     * El método verifica si la sección, los filtros y las opciones están correctamente definidas y, en base a ellas,
     * construye un objeto que incluye el filtro, las columnas, y las opciones de configuración del DataTable.
     *
     * @param stdClass $datatables Objeto que contiene la configuración del DataTable. Este objeto puede incluir
     *                             propiedades como `type`, `multi_selects`, y `menu_active`.
     *
     * @param PDO $link Conexión a la base de datos utilizada para obtener los datos.
     *
     * @param array $rows_lista Lista de campos (columnas) que se utilizarán para los filtros del DataTable.
     *
     * @param string $seccion Nombre de la sección que se utilizará en los filtros del DataTable. No debe ser una cadena vacía.
     *
     * @param array $not_actions Opciones de acciones que deben ser excluidas. Es un array opcional.
     *
     * @return array|stdClass Devuelve un objeto con la configuración del DataTable, o un arreglo con un mensaje de error
     *                        si alguna de las configuraciones no es válida.
     *
     * @example
     *  // Ejemplo 1: Inicialización exitosa
     *  $datatables = new stdClass();
     *  $datatables->type = 'scroll';
     *  $datatables->multi_selects = true;
     *  $datatables->menu_active = false;
     *  $rows_lista = ['campo1', 'campo2', 'campo3'];
     *  $seccion = 'usuarios';
     *  $resultado = $init->datatable_base_init($datatables, $link, $rows_lista, $seccion);
     *  // Devuelve un objeto stdClass con las configuraciones del DataTable, como el filtro, las columnas, y las opciones.
     *
     *  // Ejemplo 2: Error debido a una sección vacía
     *  $datatables = new stdClass();
     *  $rows_lista = ['campo1', 'campo2', 'campo3'];
     *  $seccion = '';
     *  $resultado = $init->datatable_base_init($datatables, $link, $rows_lista, $seccion);
     *  // Devuelve un arreglo con un mensaje de error: 'Error seccion debe ser un string con datos'.
     *
     *  // Ejemplo 3: Error debido a un tipo incorrecto en multi_selects
     *  $datatables = new stdClass();
     *  $datatables->multi_selects = 'yes'; // Este valor debería ser un booleano
     *  $rows_lista = ['campo1', 'campo2', 'campo3'];
     *  $seccion = 'usuarios';
     *  $resultado = $init->datatable_base_init($datatables, $link, $rows_lista, $seccion);
     *  // Devuelve un arreglo con un mensaje de error: 'Error multi_selects tiene que ser de tipo bool'.
     */
    final public function datatable_base_init(stdClass $datatables, PDO $link, array $rows_lista, string $seccion,
                                              array $not_actions = array()): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }

        // Inicialización de filtro
        $filtro = (new \gamboamartin\system\datatables\init())->init_filtro_datatables(datatables: $datatables,
            rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar filtro', data: $filtro);
        }

        // Determina el tipo de DataTable, si es 'scroll' o el tipo por defecto ('datatable')
        $type = "datatable";
        if (property_exists($datatables,"type")){
            if (strcasecmp($datatables->type, "scroll") == 0) {
                $type = $datatables->type;
            }
        }

        // Inicialización de columnas del DataTable
        $columns = $this->columns_dt(datatables: $datatables, link: $link, not_actions: $not_actions,
            rows_lista: $rows_lista, seccion: $seccion, type: $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);
        }

        // Determina si se permite la selección múltiple
        $multi_selects = false;
        if (property_exists($datatables,"multi_selects")){
            if (!is_bool($datatables->multi_selects)){
                return $this->error->error(mensaje: 'Error multi_selects tiene que ser de tipo bool', data: $datatables);
            }
            $multi_selects = $datatables->multi_selects;
        }

        // Determina si el menú está activo
        $menu_active = false;
        if (property_exists($datatables,"menu_active")){
            if (!is_bool($datatables->menu_active)){
                return $this->error->error(mensaje: 'Error menu_active tiene que ser de tipo bool', data: $datatables);
            }
            $menu_active = $datatables->menu_active;
        }

        // Construcción del objeto final con la configuración
        $data = new stdClass();
        $data->filtro = $filtro;
        $data->columns = $columns;
        $data->multi_selects = $multi_selects;
        $data->menu_active = $menu_active;
        $data->type = $type;

        return $data;
    }




    /**
     * Genera una columna para datatable
     * @param array|string $column Columna a integrar
     * @param array $columns Columnas
     * @param array $datatable obj inicializado de controler
     * @param string $indice indice de columna
     * @param int $index_button Index o posicion
     * @return array|stdClass
     * @version 0.149.33
     */
    private function genera_column(array|string $column, array $columns, array $datatable, string $indice,
                                   int $index_button): array|stdClass
    {
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->maqueta_column_obj(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }

        $datatable["columns"][] = $column_obj;

        $indice_columna = array_search($indice, array_keys($columns));

        $type = $this->type(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar type', data:  $type);
        }

        $targets = $indice_columna === count($columns) ? $index_button:$indice_columna;

        if ($indice === "check"){
            $type = "check";
        }

        $columnDefs_obj = $this->columns_defs(column: $column, indice: $indice, targets: $targets, type: $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnDefs', data:  $columnDefs_obj);
        }

        $datatable["columnDefs"][] = $columnDefs_obj;

        if ($type === 'button'){
            $index_button -= 1;
        }

        $data = new stdClass();
        $data->datatable = $datatable;
        $data->index_button = $index_button;
        return $data;
    }

    private function get_salida_format(array $data_result, stdClass $params): array
    {
        $salida = array(
            "draw"         => $params->draw,
            "recordsTotal"    => intval( $data_result['n_registros']),
            "recordsFiltered" => intval( $data_result['n_registros'] ),
            "data"            => $data_result['registros']);

        return $salida;
    }

    /**
     * @param array $adm_accion_grupo
     * @param array $data_result
     * @param html $html_base
     * @param string $key
     * @param array $links
     * @param array $row
     * @param string $seccion
     * @return array
     */
    private function integra_data_link(array $adm_accion_grupo, array $data_result, html $html_base, string $key,
                                            array $links, array $row, string $seccion): array
    {
        $registro_id = $row[$seccion.'_id'];

        $data_link = $this->data_link(adm_accion_grupo: $adm_accion_grupo,
            data_result: $data_result, html_base: $html_base, key: $key,registro_id:  $registro_id);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data para link', data: $data_link);
        }

        $links[$data_link->accion] = $data_link->link_con_id;
        return $links;
    }

    private function integra_links(array $acciones_permitidas, array $data_result, html $html_base,
                                        string $key, array $row, string $seccion){
        $links = array();
        foreach ($acciones_permitidas as $indice=>$adm_accion_grupo){

            $links = $this->integra_data_link(adm_accion_grupo: $adm_accion_grupo, data_result:  $data_result,
                html_base:  $html_base, key: $key,links:  $links,row:  $row, seccion:  $seccion);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link', data: $links);
            }
        }
        return $links;
    }




    /**
     * Integra el titulo en ele objeto de columna a generar
     * @param array|string $column Columna data
     * @param stdClass $column_obj Columnas de retorno inicializadas
     * @param string $indice Key del row de datos
     * @return array|stdClass
     * @version 0.146.33
     */
    private function integra_titulo(array|string $column, stdClass $column_obj, string $indice): array|stdClass
    {
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }

        if (is_array($column) && array_key_exists("titulo",$column)){

            $column_obj = $this->column_titulo(column: $column, column_obj: $column_obj, indice: $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
            }
        }
        return $column_obj;
    }

    /**
     * Maqueta una columna a integrar
     * @param array|string $column Columna
     * @param string $indice Key
     * @return array|stdClass
     * @version 0.147.33
     */
    private function maqueta_column_obj(array|string $column, string $indice): array|stdClass
    {
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->column_init(column: $column, indice: $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column', data:  $column_obj);
        }

        $column_obj = $this->integra_titulo(column: $column, column_obj: $column_obj,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }
        return $column_obj;
    }

    /**
     * REG
     * Genera un arreglo que representa un filtro de exclusión ("not_in") para la columna `adm_accion.descripcion`
     * basado en la lista de acciones proporcionada en `$not_actions`.
     *
     * - Si `$not_actions` está vacío, se retorna un arreglo vacío.
     * - Si `$not_actions` contiene elementos, se incluye la clave `'llave'` con el valor `'adm_accion.descripcion'`
     *   y la clave `'values'` con el contenido de `$not_actions`.
     *
     * @param array $not_actions Lista de acciones que se desean excluir (por ejemplo, ['editar', 'eliminar']).
     *
     * @return array Estructura de exclusión en formato:
     *  [
     *      'llave'  => 'adm_accion.descripcion',
     *      'values' => ['accion1', 'accion2', ...]
     *  ]
     * Si `$not_actions` está vacío, retorna un arreglo vacío (ej. `[]`).
     *
     * @example
     *  // Ejemplo 1: Lista vacía de acciones
     *  ----------------------------------------------------------------------------------
     *  $not_actions = [];
     *  $resultado = $this->not_in_accion($not_actions);
     *  // $resultado será [], indicando que no hay exclusión alguna.
     *
     * @example
     *  // Ejemplo 2: Lista con acciones para excluir
     *  ----------------------------------------------------------------------------------
     *  $not_actions = ['crear', 'editar'];
     *  $resultado = $this->not_in_accion($not_actions);
     *  // $resultado será:
     *  // [
     *  //   'llave'  => 'adm_accion.descripcion',
     *  //   'values' => ['crear', 'editar']
     *  // ]
     *
     * @example
     *  // Ejemplo 3: Uso en una consulta
     *  ----------------------------------------------------------------------------------
     *  // Supongamos que el método not_in_accion() se usa para construir filtros en un Query Builder.
     *  // Podrías utilizarlo así:
     *  $exclusion = $this->not_in_accion(['crear', 'eliminar']);
     *  if(!empty($exclusion)) {
     *      // Lógica para aplicar un WHERE NOT IN 'adm_accion.descripcion' con los valores del array
     *      // ...
     *  }
     */
    private function not_in_accion(array $not_actions): array
    {
        $not_in = [];
        if (count($not_actions) > 0) {
            $not_in['llave']  = 'adm_accion.descripcion';
            $not_in['values'] = $not_actions;
        }
        return $not_in;
    }


    final public function out_result(array $data_result, stdClass $params, bool $ws){
        $salida = $this->get_salida_format(data_result: $data_result,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar salida', data: $salida);
        }

        if($ws) {
            $out = $this->out_ws(salida: $salida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar out', data: $out);
            }
        }
        return $salida;
    }

    private function out_ws(array|stdClass $salida, bool $header = false)
    {
        ob_clean();
        header('Content-Type: application/json');
        try {
            echo json_encode($salida, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $error = $this->error->error(mensaje: 'Error al obtener registros', data: $e);
            print_r($error);
        }
        if(!$header){
            exit;
        }
        return $salida;
    }

    final public function params(array $datatable): array|stdClass
    {
        $draw = (new \gamboamartin\system\datatables\init())->draw();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener draw', data: $draw);
        }
        $n_rows_for_page = (new \gamboamartin\system\datatables\init())->n_rows_for_page();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener n_rows_for_page', data: $n_rows_for_page);
        }

        $pagina = (new \gamboamartin\system\datatables\init())->pagina(n_rows_for_page: $n_rows_for_page);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener pagina', data: $pagina);
        }
        $filtro = (new filtros())->filtro();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro);
        }

        $filtro_rango = (new filtros())->filtro_rango();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro_rango', data: $filtro_rango);
        }

        $filtro_especial = (new filtros())->genera_filtro_especial_datatable(datatable: $datatable);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
        }

        $in = (new \gamboamartin\system\datatables\init())->in();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener in', data: $in);
        }

        $order = (new \gamboamartin\system\datatables\init())->order(datatable: $datatable);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener order', data: $order);
        }


        $data = new stdClass();
        $data->draw = $draw;
        $data->n_rows_for_page = $n_rows_for_page;
        $data->pagina = $pagina;
        $data->filtro = $filtro;
        $data->filtro_rango = $filtro_rango;
        $data->filtro_especial = $filtro_especial;
        $data->in = $in;
        $data->order = $order;

        return $data;
    }

    private function rendered(string|array $column): array
    {
        $rendered = [];
        if(is_array($column)){
            if(array_key_exists('campos', $column)){

                if(!is_array($column['campos'])){
                    return $this->error->error(mensaje: 'Error $column[campos] debe seer un array', data:  $column);
                }

                $rendered = array_values($column["campos"]);
            }
        }
        return $rendered;
    }

    /**
     * REG
     * Asigna el título formateado a una columna de un DataTable.
     *
     * Esta función recibe una clave de fila y una sección, y genera un título legible para la columna en un DataTable.
     * El título se crea a partir de la clave de la fila (`key_row_lista`), convirtiéndola a formato de texto legible
     * (reemplazando los guiones bajos por espacios y poniendo la primera letra de cada palabra en mayúsculas).
     * El título generado se asigna a la columna correspondiente dentro de las columnas del DataTable.
     *
     * ## Pasos clave:
     * 1. **Validación de los parámetros**:
     *    - Se valida que la clave de la fila no esté vacía.
     *    - Se valida que la sección no esté vacía.
     * 2. **Generación del título**:
     *    - Se reemplazan los guiones bajos (`_`) por espacios en la clave de la fila.
     *    - Se convierte el texto a formato capitalizado, es decir, la primera letra de cada palabra en mayúsculas.
     * 3. **Asignación del título**:
     *    - El título generado se asigna a la columna correspondiente en el arreglo `$columns`, bajo la clave construida
     *      con la sección y la clave de fila.
     *
     * ## Ejemplo de uso:
     *
     * ### Ejemplo 1: Generación exitosa del título de la columna
     * ```php
     * $columns = [
     *     'usuario_nombre' => ['titulo' => '']
     * ];
     * $key_row_lista = 'usuario_nombre';
     * $seccion = 'usuarios';
     *
     * $resultado = $this->titulo_column_datatable($columns, $key_row_lista, $seccion);
     *
     * // El resultado será:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre']
     * ]
     * ```
     *
     * ### Ejemplo 2: Error al pasar un parámetro vacío para la clave de fila
     * ```php
     * $columns = [];
     * $key_row_lista = '';
     * $seccion = 'usuarios';
     *
     * $resultado = $this->titulo_column_datatable($columns, $key_row_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al pasar un parámetro vacío para la sección
     * ```php
     * $columns = [];
     * $key_row_lista = 'usuario_nombre';
     * $seccion = '';
     *
     * $resultado = $this->titulo_column_datatable($columns, $key_row_lista, $seccion);
     * // El resultado será un arreglo de error:
     * // [
     * //     'mensaje' => 'Error seccion debe ser un string con datos',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $columns El arreglo de columnas que contiene los títulos de las columnas del DataTable.
     *                       Este arreglo será modificado para incluir el nuevo título generado.
     * @param string $key_row_lista La clave de la fila que representa el nombre de la columna. Se espera que
     *                              esta clave esté en formato `snake_case`, y se usará para generar el título.
     * @param string $seccion La sección a la que pertenece la columna. Este parámetro se usa para construir la clave
     *                        en el arreglo `$columns` (ej. 'usuarios_usuario_nombre').
     *
     * ## Retorno:
     * @return array El arreglo de columnas actualizado con el nuevo título asignado a la columna correspondiente.
     *               Si ocurre un error, se devuelve un arreglo con el mensaje de error y los datos relacionados.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * [
     *     'usuarios_usuario_nombre' => ['titulo' => 'Usuario Nombre']
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error $key_row_lista debe ser un string con datos',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 1.0.0
     */
    private function titulo_column_datatable(array $columns, string $key_row_lista, string $seccion): array
    {
        // Validación de key_row_lista
        $key_row_lista = trim($key_row_lista);
        if ($key_row_lista === '') {
            return $this->error->error(
                mensaje: 'Error $key_row_lista debe ser un string con datos', data: $key_row_lista);
        }

        // Validación de seccion
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data: $seccion);
        }

        // Generación del título a partir de key_row_lista
        $titulo = str_replace('_', ' ', $key_row_lista);
        $titulo = ucwords($titulo);

        // Asignación del título a la columna correspondiente en el arreglo $columns
        $columns[$seccion . "_$key_row_lista"]["titulo"] = $titulo;

        return $columns;
    }


    /**
     * Obtiene el type data
     * @param array|string $column Columna a validar
     * @return string
     * @version 0.148.33
     */
    private function type(array|string $column): string
    {
        $type = 'text';
        if(is_array($column) && array_key_exists("type",$column) && $column["type"] === "button"){
            $type = $column["type"];
        }
        return $type;
    }

    /**
     * REG
     * Valida los datos de un grupo de acciones permitidas en el sistema.
     *
     * Esta función se encarga de verificar que todos los campos necesarios para una acción permitida estén presentes,
     * y que sus valores sean válidos. Asegura lo siguiente:
     *
     * 1. Verifica la existencia de claves esenciales como `adm_accion_muestra_icono_btn`, `adm_accion_muestra_titulo_btn`,
     *    `adm_accion_descripcion`, `adm_accion_titulo` y `adm_seccion_descripcion`.
     * 2. Valida que los campos `adm_accion_muestra_icono_btn` y `adm_accion_muestra_titulo_btn` tengan un valor válido, es decir,
     *    que su estado sea uno de los valores esperados ('activo' o 'inactivo').
     *
     * Si alguna de las validaciones falla, se devuelve un arreglo con el mensaje de error correspondiente.
     * Si todas las validaciones pasan, la función devuelve `true`.
     *
     * **Pasos de validación:**
     * 1. Valida que las claves necesarias estén presentes en el registro de la acción permitida.
     * 2. Valida que las claves `adm_accion_muestra_icono_btn` y `adm_accion_muestra_titulo_btn` tengan un estado válido.
     *
     * **Notas:**
     * - Si alguna validación falla, se lanza un error con un mensaje descriptivo.
     * - Si todas las validaciones son correctas, se devuelve `true`.
     *
     * @param array $adm_accion_grupo Registro de la acción permitida a validar. Debe contener las siguientes claves:
     * - `adm_accion_muestra_icono_btn`: Indica si se debe mostrar un ícono en el botón de acción. Debe ser 'activo' o 'inactivo'.
     * - `adm_accion_muestra_titulo_btn`: Indica si se debe mostrar un título en el botón de acción. Debe ser 'activo' o 'inactivo'.
     * - `adm_accion_descripcion`: Descripción de la acción.
     * - `adm_accion_titulo`: Título de la acción.
     * - `adm_seccion_descripcion`: Descripción de la sección a la que pertenece la acción.
     *
     * @return bool|array Devuelve:
     *  - `true` si todas las validaciones pasan correctamente.
     *  - Un arreglo con información del error si alguna validación falla.
     *
     * @throws errores Si alguna validación falla, se genera un error que se captura y devuelve como un mensaje.
     *
     * **Ejemplo 1: Acción permitida válida**
     * ```php
     * $adm_accion_grupo = [
     *     'adm_accion_muestra_icono_btn' => 'activo',
     *     'adm_accion_muestra_titulo_btn' => 'activo',
     *     'adm_accion_descripcion' => 'Crear Nuevo Usuario',
     *     'adm_accion_titulo' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios',
     * ];
     * $resultado = $this->valida_data_permiso($adm_accion_grupo);
     * // Retorna true si todas las claves son válidas.
     * ```
     *
     * **Ejemplo 2: Falta una clave necesaria**
     * ```php
     * $adm_accion_grupo = [
     *     'adm_accion_muestra_icono_btn' => 'activo',
     *     'adm_accion_descripcion' => 'Eliminar Usuario',
     *     'adm_accion_titulo' => 'Eliminar',
     * ];
     * $resultado = $this->valida_data_permiso($adm_accion_grupo);
     * // Retorna un arreglo con el mensaje de error: 'Error al validar adm_accion_grupo'
     * ```
     *
     * **Ejemplo 3: Estado inválido en una clave**
     * ```php
     * $adm_accion_grupo = [
     *     'adm_accion_muestra_icono_btn' => 'activo',
     *     'adm_accion_muestra_titulo_btn' => 'pendiente',  // Estado no válido
     *     'adm_accion_descripcion' => 'Crear Nuevo Usuario',
     *     'adm_accion_titulo' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios',
     * ];
     * $resultado = $this->valida_data_permiso($adm_accion_grupo);
     * // Retorna un arreglo con el mensaje de error: 'Error al validar adm_accion_grupo'
     * ```
     *
     * @version 1.0.0
     */
    final public function valida_data_permiso(array $adm_accion_grupo): bool|array
    {
        // Validación de existencia de claves en el array de la acción
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn','adm_accion_descripcion',
            'adm_accion_titulo','adm_seccion_descripcion');
        $valida = (new validacion())->valida_existencia_keys(keys:$keys, registro:  $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }

        // Validación de estado de los botones (si muestra icono y título)
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn');
        $valida = (new validacion())->valida_statuses(keys:$keys, registro:  $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }

        // Si todas las validaciones son exitosas, devuelve true
        return true;
    }





}
