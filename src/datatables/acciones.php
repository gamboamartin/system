<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\system\datatables;
use gamboamartin\validacion\validacion;
use PDO;


class acciones{
    private errores $error;
    private validacion $valida;

    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();
    }

    /**
     * REG
     * Asigna la primera acción de un grupo de acciones.
     *
     * Este método recorre un conjunto de acciones y valida que cada elemento sea un arreglo con la clave `adm_accion_descripcion`.
     * Si encuentra una acción válida, asigna el valor de `adm_accion_descripcion` a la variable `$adm_accion_base` y la retorna.
     * Si algún error ocurre durante el proceso, se retorna un arreglo con el error detallado.
     *
     * ## Pasos clave:
     * 1. Recibe un arreglo `$acciones_grupo`, que contiene las acciones permitidas para un grupo específico.
     * 2. Valida que cada elemento del arreglo sea un arreglo y contenga la clave `adm_accion_descripcion`.
     * 3. Si el primer elemento es válido, asigna su valor a `$adm_accion_base` y lo retorna.
     * 4. Si ocurre un error, devuelve un arreglo con el mensaje de error.
     *
     * ## Ejemplos:
     *
     * ### Ejemplo 1: Acción base válida
     * ```php
     * $acciones_grupo = [
     *     ['adm_accion_descripcion' => 'Ver detalles'],
     *     ['adm_accion_descripcion' => 'Editar']
     * ];
     *
     * $accion_base = $this->accion_base($acciones_grupo);
     * // El resultado será "Ver detalles"
     * ```
     *
     * ### Ejemplo 2: Error por falta de clave `adm_accion_descripcion`
     * ```php
     * $acciones_grupo = [
     *     ['adm_accion_otra_clave' => 'Ver detalles']
     * ];
     *
     * $accion_base = $this->accion_base($acciones_grupo);
     * // El resultado será un arreglo con el mensaje de error: "Error adm_accion_grupo debe ser un array"
     * ```
     *
     * ## Parámetros:
     * @param array $acciones_grupo Conjunto de acciones asociadas a un grupo. Cada acción debe ser un arreglo con la clave `adm_accion_descripcion`.
     *
     * ## Retorno:
     * @return string|array Retorna el valor de `adm_accion_descripcion` de la primera acción válida encontrada. Si hay un error, devuelve un arreglo con los detalles del error.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * "Ver detalles"
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     "mensaje" => "Error adm_accion_grupo debe ser un array",
     *     "data" => ['adm_accion_otra_clave' => 'Ver detalles']
     * ]
     * ```
     *
     * @version 0.154.33
     */
    private function accion_base(array $acciones_grupo): string|array
    {
        $adm_accion_base = '';
        foreach ($acciones_grupo as $adm_accion_grupo){
            if(!is_array($adm_accion_grupo)){
                return $this->error->error(mensaje: 'Error adm_accion_grupo debe ser un array', data: $adm_accion_grupo);
            }
            $keys = array('adm_accion_descripcion');
            $valida = $this->valida->valida_existencia_keys(keys:$keys,registro:  $adm_accion_grupo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar accion', data: $valida);
            }
            $adm_accion_base = $adm_accion_grupo['adm_accion_descripcion'];
            break;
        }
        return $adm_accion_base;
    }


    /**
     * REG
     * Genera las columnas para un DataTable, integrando las acciones permitidas y la acción base.
     *
     * Esta función valida los datos de entrada, obtiene las acciones permitidas para la sección y luego las integra a las
     * columnas del DataTable. Se asegura de que las acciones sean válidas y las agrega correctamente a las columnas.
     *
     * ## Pasos clave:
     * 1. Valida la existencia de los datos de la columna y la sección.
     * 2. Obtiene las acciones permitidas para la sección usando la función `acciones_permitidas`.
     * 3. Extrae la acción base a partir de las acciones obtenidas.
     * 4. Integra la acción base a las columnas del DataTable.
     * 5. Integra las demás acciones a las columnas del DataTable.
     * 6. Retorna las columnas del DataTable con las acciones integradas. Si ocurre algún error, se devuelve el arreglo
     *    de error correspondiente.
     *
     * ## Ejemplo:
     *
     * ### Ejemplo 1: Generación exitosa de columnas con acciones
     * ```php
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => []
     *     ]
     * ];
     * $link = new PDO('mysql:host=localhost;dbname=test', 'user', 'password');
     * $seccion = 'usuarios';
     * $not_actions = ['eliminar'];
     *
     * $resultado = $this->acciones_columnas($columns, $link, $seccion, $not_actions);
     * // El resultado será:
     * // [
     * //     'acciones' => [
     * //         'titulo' => 'Acciones',
     * //         'type' => 'button',
     * //         'campos' => ['Editar', 'Ver']
     * //     ]
     * // ]
     * ```
     *
     * ### Ejemplo 2: Error al validar datos de columna
     * ```php
     * $columns = [];
     * $link = new PDO('mysql:host=localhost;dbname=test', 'user', 'password');
     * $seccion = '';
     * $not_actions = [];
     *
     * $resultado = $this->acciones_columnas($columns, $link, $seccion, $not_actions);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error al validar datos',
     * //     'data' => 'Sección no proporcionada o vacía'
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $columns Las columnas del DataTable que se están generando. Este arreglo se modifica para agregar
     *                       las acciones permitidas.
     * @param PDO $link Objeto de conexión a la base de datos para ejecutar las consultas relacionadas con las acciones.
     * @param string $seccion La sección para la cual se deben obtener las acciones permitidas.
     * @param array $not_actions Opcional. Lista de acciones que deben ser excluidas de los resultados. El valor predeterminado
     *                           es un arreglo vacío.
     *
     * ## Retorno:
     * @return array Devuelve el arreglo de columnas actualizado con las acciones integradas. Si se produce un error en algún
     *               paso del proceso, se devuelve un arreglo con el mensaje de error y los datos relacionados.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => ['Editar', 'Ver']
     *     ]
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error al obtener acciones',
     *     'data' => 'Acciones no encontradas para la sección'
     * ]
     * ```
     *
     * @version 0.224.37
     */
    final public function acciones_columnas(array $columns, PDO $link, string $seccion, array $not_actions = array()): array
    {
        // 1. Validar datos de columna
        $valida = (new validacion_dt())->valida_data_column(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        // 2. Obtener las acciones permitidas
        $acciones_grupo = (new datatables())->acciones_permitidas(
            link: $link, seccion: $seccion, not_actions: $not_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones', data: $acciones_grupo);
        }

        // 3. Obtener la acción base
        $adm_accion_base = $this->accion_base(acciones_grupo: $acciones_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener accion base', data: $adm_accion_base);
        }

        // 4. Maquetar la acción base en las columnas
        $columns = $this->maqueta_accion_base_column(
            acciones_grupo: $acciones_grupo, adm_accion_base: $adm_accion_base, columns: $columns);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar accion base', data: $columns);
        }

        // 5. Maquetar las demás acciones en las columnas
        $columns = $this->columnas_accion(
            acciones_grupo: $acciones_grupo, adm_accion_base: $adm_accion_base, columns: $columns);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar acciones', data: $columns);
        }

        // 6. Retornar las columnas actualizadas
        return $columns;
    }


    /**
     * REG
     * Integra las acciones dentro de las columnas de un DataTable, añadiendo la acción base y otras acciones relacionadas.
     *
     * Esta función toma un grupo de acciones y las agrega a las columnas de un DataTable. Se asegura de que cada acción
     * sea válida y la agrega al conjunto de columnas. Si la validación falla en algún punto, se retorna un error con el mensaje
     * correspondiente.
     *
     * ## Pasos clave:
     * 1. La función recorre el grupo de acciones, validando que cada acción sea un array.
     * 2. Valida que cada grupo de acciones contenga la clave `adm_accion_descripcion`.
     * 3. Si todo es válido, se agrega la acción a las columnas de DataTable.
     * 4. Si ocurre algún error, se devuelve un arreglo con el mensaje de error correspondiente.
     *
     * ## Ejemplos:
     *
     * ### Ejemplo 1: Integración exitosa de acciones en las columnas
     * ```php
     * $acciones_grupo = [
     *     ['adm_accion_descripcion' => 'Editar'],
     *     ['adm_accion_descripcion' => 'Eliminar']
     * ];
     * $adm_accion_base = 'acciones';
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => []
     *     ]
     * ];
     *
     * $columns = $this->columnas_accion($acciones_grupo, $adm_accion_base, $columns);
     * // El resultado será:
     * // $columns = [
     * //     'acciones' => [
     * //         'titulo' => 'Acciones',
     * //         'type' => 'button',
     * //         'campos' => ['Editar', 'Eliminar']
     * //     ]
     * // ]
     * ```
     *
     * ### Ejemplo 2: Error al proporcionar un grupo de acciones no válido
     * ```php
     * $acciones_grupo = ['invalid_action']; // No es un array válido
     * $adm_accion_base = 'acciones';
     * $columns = [];
     *
     * $columns = $this->columnas_accion($acciones_grupo, $adm_accion_base, $columns);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error adm_accion_grupo debe ser un array',
     * //     'data' => 'invalid_action'
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al faltar la clave `adm_accion_descripcion`
     * ```php
     * $acciones_grupo = [['invalid_key' => 'Editar']]; // No contiene 'adm_accion_descripcion'
     * $adm_accion_base = 'acciones';
     * $columns = [];
     *
     * $columns = $this->columnas_accion($acciones_grupo, $adm_accion_base, $columns);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error al validar adm_accion_grupo ',
     * //     'data' => ['invalid_key' => 'Editar']
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $acciones_grupo Conjunto de grupos de acciones, donde cada grupo debe ser un array que contenga
     *                               al menos la clave `adm_accion_descripcion`.
     * @param string $adm_accion_base El nombre base de la acción a agregar, que se utilizará como clave en el arreglo
     *                                de columnas.
     * @param array $columns Las columnas del DataTable que se están construyendo. Esta función modificará las columnas
     *                       existentes agregando las acciones proporcionadas.
     *
     * ## Retorno:
     * @return array Retorna las columnas de DataTable actualizadas con las acciones agregadas. Si ocurre algún error,
     *               retorna un arreglo con el mensaje y los detalles del error.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => ['Editar', 'Eliminar']
     *     ]
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error adm_accion_grupo debe ser un array',
     *     'data' => 'invalid_action'
     * ]
     * ```
     *
     * @version 0.224.37
     */
    private function columnas_accion(array $acciones_grupo, string $adm_accion_base, array $columns): array
    {
        $i = 0;
        foreach ($acciones_grupo as $adm_accion_grupo){
            if(!is_array($adm_accion_grupo)){
                return $this->error->error(
                    mensaje: 'Error adm_accion_grupo debe ser un array', data: $adm_accion_grupo);
            }

            $keys = array('adm_accion_descripcion');
            $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $adm_accion_grupo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar adm_accion_grupo ', data: $valida);
            }

            $columns = $this->genera_accion(
                adm_accion_base: $adm_accion_base,adm_accion_grupo:  $adm_accion_grupo,columns:  $columns,i:  $i);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar accion ', data: $columns);
            }
            $i++;
        }
        return $columns;
    }


    /**
     * REG
     * Integra una acción dentro de las columnas de un DataTable, basándose en la acción proporcionada.
     *
     * Esta función es responsable de agregar una acción a las columnas de un DataTable, utilizando la acción base y los datos
     * del grupo de acciones. Valida que los datos sean correctos y realiza las acciones necesarias para agregar las acciones
     * a las columnas de la tabla. Si ocurre algún error durante el proceso, devuelve un arreglo con los detalles del error.
     *
     * ## Pasos clave:
     * 1. La función valida que la clave `adm_accion_descripcion` exista en el grupo de acciones proporcionado.
     * 2. Si el índice `$i` es mayor que 0, la función valida que tanto `adm_accion_base` como `adm_accion` no estén vacíos.
     * 3. Si todas las validaciones son correctas, integra la acción en las columnas.
     * 4. Si ocurre un error, se devuelve un arreglo con el mensaje de error correspondiente.
     *
     * ## Ejemplos:
     *
     * ### Ejemplo 1: Acción válida agregada correctamente
     * ```php
     * $adm_accion_base = 'acciones';
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => 'Editar'
     * ];
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => []
     *     ]
     * ];
     *
     * $columns = $this->genera_accion($adm_accion_base, $adm_accion_grupo, $columns, 1);
     * // El resultado será:
     * // $columns = [
     * //     'acciones' => [
     * //         'titulo' => 'Acciones',
     * //         'type' => 'button',
     * //         'campos' => ['Editar']
     * //     ]
     * // ]
     * ```
     *
     * ### Ejemplo 2: Error al proporcionar un índice menor que 0
     * ```php
     * $adm_accion_base = 'acciones';
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => 'Ver detalles'
     * ];
     * $columns = [];
     * $i = -1;
     *
     * $columns = $this->genera_accion($adm_accion_base, $adm_accion_grupo, $columns, $i);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error i debe ser mayor o igual a 0',
     * //     'data' => -1
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al proporcionar una acción base vacía
     * ```php
     * $adm_accion_base = ''; // Acción base vacía
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => 'Eliminar'
     * ];
     * $columns = [];
     * $i = 1;
     *
     * $columns = $this->genera_accion($adm_accion_base, $adm_accion_grupo, $columns, $i);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error adm_accion_base esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 4: Error al proporcionar una acción vacía
     * ```php
     * $adm_accion_base = 'acciones';
     * $adm_accion_grupo = [
     *     'adm_accion_descripcion' => '' // Acción vacía
     * ];
     * $columns = [];
     * $i = 1;
     *
     * $columns = $this->genera_accion($adm_accion_base, $adm_accion_grupo, $columns, $i);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error adm_accion esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param string $adm_accion_base Nombre de la columna base de tipo acción que se agregará al DataTable.
     * @param array $adm_accion_grupo Conjunto de datos del grupo de acciones que contiene la descripción de la acción.
     * @param array $columns Columnas del DataTable a las que se les integrará la acción.
     * @param int $i Índice de la acción en el grupo de acciones. Debe ser mayor o igual a 0.
     *
     * ## Retorno:
     * @return array Si todo es correcto, retorna el arreglo de columnas del DataTable con la acción agregada.
     *               En caso de error, retorna un arreglo con el mensaje y los datos del error.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => ['Editar']
     *     ]
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error i debe ser mayor o igual a 0',
     *     'data' => -1
     * ]
     * ```
     *
     * @version 0.221.37
     */
    private function genera_accion(string $adm_accion_base, array $adm_accion_grupo, array $columns, int $i): array
    {
        $keys = array('adm_accion_descripcion');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo ', data: $valida);
        }
        if($i<0){
            return $this->error->error(mensaje: 'Error i debe ser mayor o igual a 0 ', data: $i);
        }

        $adm_accion = $adm_accion_grupo['adm_accion_descripcion'];
        if($i > 0){

            $adm_accion_base = trim($adm_accion_base);
            if($adm_accion_base === ''){
                return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
            }
            $adm_accion = trim($adm_accion);
            if($adm_accion === ''){
                return $this->error->error(mensaje: 'Error adm_accion esta vacia', data:  $adm_accion);
            }

            $columns = $this->integra_accion(
                adm_accion: $adm_accion,adm_accion_base:  $adm_accion_base,columns:  $columns);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar accion ', data: $columns);
            }

        }
        return $columns;
    }


    /**
     * REG
     * Integra una acción a una columna en un DataTable.
     *
     * Esta función permite agregar una acción a una columna de un DataTable. La acción se asocia a una columna base de tipo botón
     * para realizar operaciones específicas sobre los registros mostrados en el DataTable.
     * Si alguno de los parámetros está vacío o no es válido, la función devolverá un error con detalles específicos del problema.
     *
     * ## Pasos clave:
     * 1. La función valida que el nombre de la acción base (`$adm_accion_base`) no esté vacío.
     * 2. La función valida que la acción (`$adm_accion`) no esté vacía.
     * 3. Si ambos parámetros son válidos, la acción se integra a la columna correspondiente del DataTable.
     * 4. En caso de error, se devuelve un arreglo con el mensaje y la información del error.
     *
     * ## Ejemplos:
     *
     * ### Ejemplo 1: Acción válida agregada correctamente
     * ```php
     * $adm_accion = 'Editar';
     * $adm_accion_base = 'acciones';
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => []
     *     ]
     * ];
     *
     * $columns = $this->integra_accion($adm_accion, $adm_accion_base, $columns);
     * // El resultado será:
     * // $columns = [
     * //     'acciones' => [
     * //         'titulo' => 'Acciones',
     * //         'type' => 'button',
     * //         'campos' => ['Editar']
     * //     ]
     * // ]
     * ```
     *
     * ### Ejemplo 2: Error al proporcionar una acción base vacía
     * ```php
     * $adm_accion = 'Ver detalles';
     * $adm_accion_base = ''; // Acción base vacía
     * $columns = [];
     *
     * $columns = $this->integra_accion($adm_accion, $adm_accion_base, $columns);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error adm_accion_base esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo 3: Error al proporcionar una acción vacía
     * ```php
     * $adm_accion = ''; // Acción vacía
     * $adm_accion_base = 'acciones';
     * $columns = [];
     *
     * $columns = $this->integra_accion($adm_accion, $adm_accion_base, $columns);
     * // El resultado será:
     * // [
     * //     'mensaje' => 'Error adm_accion esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param string $adm_accion Descripción de la acción a agregar a la columna del DataTable.
     * @param string $adm_accion_base Nombre de la columna base (por ejemplo, 'acciones') donde se agregará la acción.
     * @param array $columns Arreglo de columnas del DataTable que contiene la configuración de cada columna.
     *
     * ## Retorno:
     * @return array El arreglo de columnas del DataTable con la nueva acción agregada a la columna correspondiente.
     *               Si ocurre un error, retorna un arreglo con un mensaje de error detallado.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => ['Editar']
     *     ]
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error adm_accion_base esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 0.221.37
     */
    private function integra_accion(string $adm_accion, string $adm_accion_base, array $columns): array
    {
        $adm_accion_base = trim($adm_accion_base);
        if($adm_accion_base === ''){
            return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
        }
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia', data:  $adm_accion);
        }
        $columns[$adm_accion_base]['campos'][] = $adm_accion;
        return $columns;
    }


    /**
     * REG
     * Maqueta una columna base para las acciones en un DataTable.
     *
     * Esta función se utiliza para maqueta una columna específica dentro de un DataTable, que estará destinada a mostrar las "acciones" disponibles para un grupo de registros.
     * Si se proporcionan acciones válidas (`$acciones_grupo`), la columna se configura con un título, un tipo de campo y se inicializa una lista vacía de "campos".
     * Si hay algún error, como que el nombre de la acción base esté vacío, se retorna un arreglo de error.
     *
     * ## Pasos clave:
     * 1. Verifica que el arreglo `$acciones_grupo` tenga al menos un elemento.
     * 2. Valida que el nombre de la acción base (`$adm_accion_base`) no esté vacío.
     * 3. Si ambos son válidos, configura la columna en el arreglo `$columns`, asignando un título, tipo de columna y campos vacíos.
     * 4. Si ocurre un error durante la validación, devuelve un arreglo con el mensaje de error correspondiente.
     *
     * ## Ejemplos:
     *
     * ### Ejemplo 1: Columna de acción válida
     * ```php
     * $acciones_grupo = [
     *     ['adm_accion_descripcion' => 'Ver detalles'],
     *     ['adm_accion_descripcion' => 'Editar']
     * ];
     * $adm_accion_base = 'acciones';
     * $columns = [];
     *
     * $columns = $this->maqueta_accion_base_column($acciones_grupo, $adm_accion_base, $columns);
     * // El resultado será:
     * // $columns = [
     * //     'acciones' => [
     * //         'titulo' => 'Acciones',
     * //         'type' => 'button',
     * //         'campos' => []
     * //     ]
     * // ]
     * ```
     *
     * ### Ejemplo 2: Acción base vacía
     * ```php
     * $acciones_grupo = [
     *     ['adm_accion_descripcion' => 'Ver detalles']
     * ];
     * $adm_accion_base = ''; // Acción base vacía
     * $columns = [];
     *
     * $columns = $this->maqueta_accion_base_column($acciones_grupo, $adm_accion_base, $columns);
     * // El resultado será:
     * // [
     * //     "mensaje" => "Error adm_accion_base esta vacia",
     * //     "data" => ""
     * // ]
     * ```
     *
     * ## Parámetros:
     * @param array $acciones_grupo Conjunto de acciones disponibles para un grupo. Cada acción debe ser un arreglo que contenga la clave `adm_accion_descripcion`.
     * @param string $adm_accion_base Nombre base de la acción a asociar con la columna.
     * @param array $columns Columnas de DataTable que se van a maquetar con las acciones.
     *
     * ## Retorno:
     * @return array El arreglo de columnas con la nueva columna configurada para las acciones. Si hay un error, se retorna un arreglo con el mensaje de error.
     *
     * ## Ejemplo de salida:
     * ### Ejemplo de salida exitosa:
     * ```php
     * // Salida:
     * $columns = [
     *     'acciones' => [
     *         'titulo' => 'Acciones',
     *         'type' => 'button',
     *         'campos' => []
     *     ]
     * ]
     * ```
     *
     * ### Ejemplo de salida con error:
     * ```php
     * // Salida:
     * [
     *     'mensaje' => 'Error adm_accion_base esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @version 0.170.34
     */
    private function maqueta_accion_base_column(array $acciones_grupo, string $adm_accion_base, array $columns): array
    {
        if(count($acciones_grupo) > 0){
            $adm_accion_base = trim($adm_accion_base);
            if($adm_accion_base === ''){
                return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
            }
            $columns[$adm_accion_base]['titulo'] = 'Acciones';
            $columns[$adm_accion_base]['type'] = 'button';
            $columns[$adm_accion_base]['campos'] = array();
        }

        return $columns;
    }


}
