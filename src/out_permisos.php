<?php
namespace gamboamartin\system;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\params;
use gamboamartin\validacion\validacion;
use stdClass;

class out_permisos{

    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Genera un conjunto de botones HTML a partir de una lista de acciones permitidas.
     *
     * Esta función itera sobre un arreglo de acciones permitidas, valida los datos de cada acción, y genera los botones
     * correspondientes en forma de enlaces HTML. Para cada acción permitida, se personaliza el botón, obteniendo
     * parámetros adicionales y generando el enlace correspondiente. Si algún paso falla, devuelve un mensaje de error.
     *
     * **Flujo de trabajo:**
     * 1. Verifica que el registro no esté vacío.
     * 2. Itera sobre el arreglo de acciones permitidas y genera un botón para cada acción.
     * 3. Personaliza los parámetros del botón con los valores proporcionados y ajustados.
     * 4. Genera el enlace HTML para el botón usando la función `link_btn_action`.
     * 5. Retorna el conjunto de botones generados o un mensaje de error si alguna de las validaciones falla.
     *
     * **Parámetros:**
     *
     * @param array $acciones_permitidas Un arreglo de acciones permitidas, cada una representada por un arreglo con los siguientes campos:
     *     - `adm_accion_descripcion`: Descripción de la acción (por ejemplo, 'guardar').
     *     - `adm_accion_titulo`: Título del botón (por ejemplo, 'Guardar cambios').
     *     - `adm_accion_icono`: Icono del botón (opcional, por ejemplo, `<span class='fa fa-check'></span>`).
     *     - `adm_seccion_descripcion`: Descripción de la sección donde se ejecuta la acción (por ejemplo, 'usuarios').
     *
     * **Ejemplo:**
     * ```php
     * $acciones_permitidas = [
     *     [
     *         'adm_accion_descripcion' => 'guardar',
     *         'adm_accion_titulo' => 'Guardar cambios',
     *         'adm_accion_icono' => '<span class="fa fa-check"></span>',
     *         'adm_seccion_descripcion' => 'usuarios'
     *     ],
     *     [
     *         'adm_accion_descripcion' => 'eliminar',
     *         'adm_accion_titulo' => 'Eliminar usuario',
     *         'adm_accion_icono' => '<span class="fa fa-trash"></span>',
     *         'adm_seccion_descripcion' => 'usuarios'
     *     ]
     * ];
     * ```
     *
     * @param int $cols Número de columnas que el botón ocupará en un diseño basado en la grilla de Bootstrap.
     *
     * **Ejemplo:**
     * ```php
     * $cols = 6;  // El botón ocupará 6 columnas en la grilla de Bootstrap.
     * ```
     *
     * @param html_controler $html Instancia del controlador HTML que manejará la creación del botón.
     *
     * **Ejemplo:**
     * ```php
     * $html = new html_controler();
     * ```
     *
     * @param array $params Parámetros adicionales que se incluirán en la URL del enlace como parámetros GET.
     *
     * **Ejemplo:**
     * ```php
     * $params = ['redirigir' => 'true'];  // Parámetros GET adicionales para el enlace
     * ```
     *
     * @param array $params_ajustados Parámetros ajustados para cada acción. Se usa para personalizar los botones de manera específica.
     *
     * **Ejemplo:**
     * ```php
     * $params_ajustados = [
     *     'guardar' => ['extra_param' => 'value'],
     *     'eliminar' => ['extra_param' => 'other_value']
     * ];
     * ```
     *
     * @param array $registro Datos del registro asociado a la acción. Usado para obtener valores relacionados con el registro.
     *
     * **Ejemplo:**
     * ```php
     * $registro = ['id' => 123, 'nombre' => 'Juan Pérez'];  // Datos del registro
     * ```
     *
     * @param int $registro_id El ID del registro relacionado con la acción.
     *
     * **Ejemplo:**
     * ```php
     * $registro_id = 123;  // El ID del registro relacionado con la acción
     * ```
     *
     * @param array $styles Estilos CSS adicionales aplicados al botón.
     *
     * **Ejemplo:**
     * ```php
     * $styles = ['color' => 'red', 'font-size' => '16px'];  // Estilos CSS adicionales
     * ```
     *
     * **Retorno:**
     * - Devuelve un arreglo de botones HTML generados.
     * - Si ocurre un error en cualquiera de los pasos, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con todos los parámetros válidos:**
     * ```html
     * <a role="button" title="Guardar cambios" href="index.php?accion=guardar&seccion=usuarios&registro_id=123&session_id=abc123&redirigir=true"
     *    class="btn btn-primary col-sm-6" style="margin-bottom: 5px;" id="guardarBtn">
     *     <span class="fa fa-check"></span> Guardar cambios
     * </a>
     * <a role="button" title="Eliminar usuario" href="index.php?accion=eliminar&seccion=usuarios&registro_id=123&session_id=abc123&redirigir=true"
     *    class="btn btn-danger col-sm-6" style="margin-bottom: 5px;" id="eliminarBtn">
     *     <span class="fa fa-trash"></span> Eliminar usuario
     * </a>
     * ```
     *
     * **Ejemplo 2: Error debido a un registro vacío:**
     * ```php
     * $registro = [];
     * // Salida: "Error registro esta vacio"
     * ```
     *
     * **Ejemplo 3: Error al generar un enlace debido a un parámetro inválido:**
     * ```php
     * $acciones_permitidas = [
     *     'adm_accion_descripcion' => 'guardar',
     *     'adm_accion_titulo' => 'Guardar cambios',
     *     'adm_accion_icono' => 'invalid_icon',  // Ícono inválido
     *     'adm_seccion_descripcion' => 'usuarios',
     * ];
     * // Salida: "Error al generar link"
     * ```
     *
     * **Excepciones:**
     * - Si alguno de los parámetros es inválido o falta, la función generará un mensaje de error detallado y devolverá un arreglo con el error correspondiente.
     *
     * **@version 1.0.0**
     */
    private function buttons_permitidos(array $acciones_permitidas, int $cols, html_controler $html,
                                        array $params, array $params_ajustados, array $registro, int $registro_id,
                                        array $styles =  array('margin-bottom'=>'5px')): array
    {
        // Validación si el registro está vacío
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro esta vacio', data:  $registro);
        }

        // Inicialización de la lista de botones
        $buttons = array();

        // Iterar sobre las acciones permitidas
        foreach ($acciones_permitidas as $accion_permitida) {
            // Ajustar parámetros para la acción específica
            $params_btn = $params;
            if (isset($params_ajustados[$accion_permitida['adm_accion_descripcion']])) {
                $params_btn = $params_ajustados[$accion_permitida['adm_accion_descripcion']];
            }

            // Generar el enlace para el botón
            $link = $this->link_btn_action(
                accion_permitida: $accion_permitida,
                cols: $cols,
                html: $html,
                params: $params_btn,
                registro: $registro,
                registro_id: $registro_id,
                styles: $styles
            );

            // Verificar si hubo error al generar el enlace
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar link', data:  $link);
            }

            // Agregar el botón al arreglo de botones
            $buttons[] = $link;
        }

        // Devolver los botones generados
        return $buttons;
    }


    /**
     * REG
     * Genera los botones HTML para un controlador dado, basándose en las acciones permitidas y sus parámetros.
     *
     * Esta función valida que los datos del controlador y las acciones permitidas sean correctos, calcula el número de columnas
     * necesarias para la interfaz, y genera los botones correspondientes en forma de enlaces HTML. Si algún paso falla,
     * la función devuelve un mensaje de error.
     *
     * **Flujo de trabajo:**
     * 1. Verifica que el registro del controlador no esté vacío.
     * 2. Obtiene las acciones permitidas a partir del controlador y los filtros especificados.
     * 3. Calcula el número de columnas necesarias usando el método `cols_btn_action`.
     * 4. Genera los botones HTML mediante la función `buttons_permitidos`.
     * 5. Retorna el conjunto de botones generados o un mensaje de error si alguna validación falla.
     *
     * **Parámetros:**
     *
     * @param system $controler Instancia del controlador que contiene los datos necesarios para generar los botones, como el registro y la sección.
     *
     * **Ejemplo:**
     * ```php
     * $controler = new system();  // Instancia del controlador
     * ```
     *
     * @param array $not_actions Lista de identificadores de acciones que deben ser excluidas de los resultados.
     *                            Este parámetro es opcional y tiene un valor predeterminado de un arreglo vacío.
     *
     * **Ejemplo:**
     * ```php
     * $not_actions = ['action1', 'action2'];  // Acciones a excluir
     * ```
     *
     * @param array $params Parámetros adicionales que se incluirán en la URL del enlace como parámetros GET.
     *
     * **Ejemplo:**
     * ```php
     * $params = ['redirigir' => 'true'];  // Parámetros GET adicionales
     * ```
     *
     * @param array $params_ajustados Parámetros ajustados para cada acción permitida. Se usa para personalizar los botones de manera específica.
     *
     * **Ejemplo:**
     * ```php
     * $params_ajustados = [
     *     'guardar' => ['extra_param' => 'value'],
     *     'eliminar' => ['extra_param' => 'other_value']
     * ];
     * ```
     *
     * @param array $styles Estilos CSS adicionales aplicados a los botones.
     *
     * **Ejemplo:**
     * ```php
     * $styles = ['margin-bottom' => '5px'];  // Estilo CSS para los botones
     * ```
     *
     * **Retorno:**
     * - Devuelve un arreglo de botones HTML generados.
     * - Si ocurre un error en cualquiera de los pasos, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con todos los parámetros válidos:**
     * ```html
     * <a role="button" title="Guardar cambios" href="index.php?accion=guardar&seccion=usuarios&registro_id=123&session_id=abc123&redirigir=true"
     *    class="btn btn-primary col-sm-6" style="margin-bottom: 5px;" id="guardarBtn">
     *     <span class="fa fa-check"></span> Guardar cambios
     * </a>
     * <a role="button" title="Eliminar usuario" href="index.php?accion=eliminar&seccion=usuarios&registro_id=123&session_id=abc123&redirigir=true"
     *    class="btn btn-danger col-sm-6" style="margin-bottom: 5px;" id="eliminarBtn">
     *     <span class="fa fa-trash"></span> Eliminar usuario
     * </a>
     * ```
     *
     * **Ejemplo 2: Error debido a un registro vacío:**
     * ```php
     * $controler = new system();  // Registro vacío
     * $buttons = $this->buttons_view($controler, $not_actions, $params);
     * // Salida: "Error controler->registro esta vacio"
     * ```
     *
     * **Ejemplo 3: Error al generar botones debido a un parámetro inválido:**
     * ```php
     * $not_actions = ['action1'];
     * $buttons = $this->buttons_view($controler, $not_actions, $params);
     * // Salida: "Error al generar botones"
     * ```
     *
     * **@version 1.0.0**
     */
    final public function buttons_view(system $controler, array $not_actions, array $params,
                                       array $params_ajustados = array(),
                                       array $styles = array('margin-bottom'=>'5px')): array
    {
        if(count($controler->registro) === 0){
            return $this->error->error(mensaje: 'Error controler->registro esta vacio',data:  $controler->registro);
        }
        $acciones_permitidas = (new datatables())->acciones_permitidas(link: $controler->link,
            seccion: $controler->seccion, not_actions: $not_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones',data:  $acciones_permitidas);
        }

        $cols = $this->cols_btn_action(acciones_permitidas: $acciones_permitidas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular cols',data:  $cols);
        }

        $html = (new html_controler(html: $controler->html_base));

        $buttons = $this->buttons_permitidos(acciones_permitidas: $acciones_permitidas, cols: $cols, html: $html,
            params: $params, params_ajustados: $params_ajustados, registro: $controler->registro,
            registro_id: $controler->registro_id, styles: $styles);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar botones',data:  $buttons);
        }
        return $buttons;
    }


    /**
     * REG
     * Calcula el número de columnas a utilizar en la interfaz en función de la cantidad de acciones permitidas.
     *
     * Esta función toma el número de acciones permitidas como entrada y calcula cuántas columnas de un grid CSS
     * se deben usar para mostrar los botones de las acciones de manera adecuada. El cálculo tiene en cuenta
     * la cantidad exacta de acciones y ajusta el número de columnas de acuerdo con ciertas reglas predefinidas.
     *
     * **Lógica de columnas:**
     * - Si el número de acciones es 1, se utiliza 12 columnas (un solo botón que ocupa toda la fila).
     * - Si el número de acciones es 2, se utiliza 6 columnas por acción (dos botones).
     * - Si el número de acciones es 3, se utilizan 4 columnas por acción.
     * - Si el número de acciones es 4, se utilizan 3 columnas por acción.
     * - Si el número de acciones es 6, se utilizan 2 columnas por acción.
     * - En otros casos, se calcula un valor predeterminado basado en la cantidad de acciones, pero nunca será menor a 3 columnas.
     *
     * @param array $acciones_permitidas Un arreglo de acciones permitidas que determina cuántos botones deben mostrarse.
     *                                      Cada elemento de este arreglo representa una acción que puede ser ejecutada.
     *
     * @return int El número de columnas a utilizar en el diseño. Este valor puede ser uno de los siguientes:
     *             - 12 para una sola acción.
     *             - 6 para dos acciones.
     *             - 4 para tres acciones.
     *             - 3 para cuatro acciones.
     *             - 2 para seis acciones.
     *             - Un valor calculado para otros casos.
     *
     * @throws array Si el parámetro `$acciones_permitidas` no es un arreglo.
     *
     * @example
     * // Ejemplo de uso:
     * $acciones = [
     *     ['accion' => 'alta'],
     *     ['accion' => 'modificar'],
     *     ['accion' => 'eliminar']
     * ];
     * $cols = $this->cols_btn_action($acciones);
     * echo $cols; // Imprimirá 4, ya que hay 3 acciones.
     *
     * @example
     * // Si se pasa solo una acción:
     * $acciones = [['accion' => 'alta']];
     * $cols = $this->cols_btn_action($acciones);
     * echo $cols; // Imprimirá 12, ya que hay solo una acción.
     */
    private function cols_btn_action(array $acciones_permitidas): int
    {
        $n_acciones = count($acciones_permitidas);

        // Determinar el número de columnas por defecto
        $cols = (int)($n_acciones / 4);
        $cols = max($cols, 3);  // Aseguramos que no sea menor que 3

        // Ajustar las columnas según el número exacto de acciones
        switch ($n_acciones) {
            case 1:
                $cols = 12;
                break;
            case 2:
                $cols = 6;
                break;
            case 3:
                $cols = 4;
                break;
            case 4:
                $cols = 3;
                break;
            case 6:
                $cols = 2;
                break;
        }

        return $cols;
    }



    /**
     * Genera el conjunto de botones
     * @param array $acciones_permitidas Acciones permitidas
     * @param html_controler $html template
     * @param string $key_id Key de row
     * @param array $rows conjunto de registros
     * @param array $params Parametros get extra
     * @return array
     * @version 0.172.34
     */
    final public function genera_buttons_permiso(
        array $acciones_permitidas, html_controler$html, string $key_id, array $rows, array $params = array()): array
    {
        foreach ($rows as $indice=>$row){
            $rows = $this->integra_acciones_permitidas(acciones_permitidas: $acciones_permitidas, html: $html,
                indice:  $indice,key_id:  $key_id, row: $row,rows:  $rows, params: $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }

    /**
     * Integra las acciones permitidas a un row para lista
     * @param array $acciones_permitidas Conjunto de acciones
     * @param html_controler $html Template html
     * @param int $indice Indice de la matriz de los registros a mostrar
     * @param string $key_id key de valor para registro id
     * @param array $row registro en proceso
     * @param array $rows conjunto de registros
     * @param array $params Extraparams para link
     * @return array
     * @version 0.167.34
     */
    private function integra_acciones_permitidas(
        array $acciones_permitidas, html_controler $html, int $indice, string $key_id, array $row, array $rows,
        array $params = array()): array
    {

        if($indice < 0){
            return $this->error->error(mensaje: 'Error indice debe ser mayor o igual a 0',data:  $indice);
        }
        $key_id = trim($key_id);
        if($key_id ===''){
            return $this->error->error(mensaje: 'Error key_id esta vacio',data:  $key_id);
        }
        if(is_numeric($key_id)){
            return $this->error->error(mensaje: 'Error key_id debe ser un campo con texto',data:  $key_id);
        }
        if(!isset($rows[$indice])){
            return $this->error->error(mensaje: 'Error no existe el registro en proceso',data:  $rows);
        }

        if(!isset($rows[$indice]['acciones'])){
            $rows[$indice]['acciones'] = array();
        }

        foreach ($acciones_permitidas as $accion_permitida){

            $valida = $this->valida_data_btn(
                accion_permitida: $accion_permitida,key_id:  $key_id, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
            }
            $valida = $html->valida_boton_data_accion(accion_permitida: $accion_permitida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar accion_permitida',data:  $valida);
            }

            $rows = $html->boton_link_permitido(accion_permitida: $accion_permitida,indice:  $indice,
                registro_id:  $row[$key_id],rows:  $rows, params: $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }


    /**
     * REG
     * Genera un enlace HTML (`<a>`) que actúa como un botón con los parámetros proporcionados.
     *
     * Esta función valida que los datos de la acción permitida sean correctos, obtiene el estilo y el icono del botón,
     * y genera el enlace HTML que será renderizado como un botón. Si alguno de los pasos falla, devuelve un mensaje de error.
     * El enlace resultante puede incluir el título y el ícono del botón dependiendo de los parámetros de entrada.
     *
     * **Flujo de trabajo:**
     * 1. Verifica que el registro no esté vacío.
     * 2. Valida los datos de la acción permitida con el método `valida_data_action`.
     * 3. Obtiene el estilo del botón usando el método `style_btn`.
     * 4. Asigna un ícono de acción utilizando el método `data_icon`.
     * 5. Genera un enlace HTML (`<a>`) usando el método `button_href`.
     * 6. Devuelve el enlace HTML generado o un mensaje de error si alguna de las validaciones falla.
     *
     * **Parámetros:**
     *
     * @param array $accion_permitida Un arreglo que contiene los datos de la acción permitida, incluyendo:
     *     - `adm_accion_descripcion`: Descripción de la acción (por ejemplo, 'guardar').
     *     - `adm_accion_titulo`: Título del botón (por ejemplo, 'Guardar cambios').
     *     - `adm_accion_icono`: Icono del botón (opcional, por ejemplo, `<span class='fa fa-check'></span>`).
     *     - `adm_seccion_descripcion`: Descripción de la sección donde se ejecuta la acción (por ejemplo, 'usuarios').
     *
     * **Ejemplo:**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_descripcion' => 'guardar',
     *     'adm_accion_titulo' => 'Guardar cambios',
     *     'adm_accion_icono' => '<span class="fa fa-check"></span>',
     *     'adm_seccion_descripcion' => 'usuarios',
     * ];
     * ```
     *
     * @param int $cols Número de columnas que el botón ocupará en un diseño basado en la grilla de Bootstrap.
     *
     * **Ejemplo:**
     * ```php
     * $cols = 6;  // El botón ocupará 6 columnas en la grilla de Bootstrap.
     * ```
     *
     * @param html_controler $html Instancia del controlador HTML que manejará la creación del botón.
     *
     * **Ejemplo:**
     * ```php
     * $html = new html_controler();
     * ```
     *
     * @param array $params Parámetros adicionales que se incluirán en la URL del enlace como parámetros GET.
     *
     * **Ejemplo:**
     * ```php
     * $params = ['redirigir' => 'true'];  // Parámetros GET adicionales para el enlace
     * ```
     *
     * @param array $registro Datos del registro asociado a la acción. Usado para obtener valores relacionados con el registro.
     *
     * **Ejemplo:**
     * ```php
     * $registro = ['id' => 123, 'nombre' => 'Juan Pérez'];  // Datos del registro
     * ```
     *
     * @param int $registro_id El ID del registro relacionado con la acción.
     *
     * **Ejemplo:**
     * ```php
     * $registro_id = 123;  // El ID del registro relacionado con la acción
     * ```
     *
     * @param array $styles Estilos CSS adicionales aplicados al botón.
     *
     * **Ejemplo:**
     * ```php
     * $styles = ['color' => 'red', 'font-size' => '16px'];  // Estilos CSS adicionales
     * ```
     *
     * **Retorno:**
     * - Devuelve un enlace HTML completo si todos los parámetros son válidos.
     * - Si ocurre un error en cualquiera de los pasos, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con todos los parámetros válidos:**
     * ```html
     * <a role="button" title="Guardar cambios" href="index.php?accion=guardar&seccion=usuarios&registro_id=123&session_id=abc123&redirigir=true"
     *    class="btn btn-primary col-sm-6" style="margin-bottom: 5px;" id="guardarBtn">
     *     <span class="fa fa-check"></span> Guardar cambios
     * </a>
     * ```
     *
     * **Ejemplo 2: Error debido a un registro vacío:**
     * ```php
     * $registro = [];
     * // Salida: "Error registro esta vacio"
     * ```
     *
     * **Ejemplo 3: Error en validación de la acción permitida:**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_descripcion' => 'guardar',
     *     'adm_accion_titulo' => 'Guardar cambios',
     *     'adm_accion_icono' => '<span class="fa fa-check"></span>',
     *     'adm_seccion_descripcion' => 'usuarios',
     * ];
     * $valida = false;  // Simulación de error en validación
     * // Salida: "Error al validar accion_permitida"
     * ```
     *
     * **Excepciones:**
     * - Si alguno de los parámetros es inválido o falta, la función generará un mensaje de error detallado y devolverá un arreglo con el error correspondiente.
     *
     * **@version 1.0.0**
     */
    private function link_btn_action(array $accion_permitida, int $cols, html_controler $html, array $params,
                                     array $registro, int $registro_id,
                                     array $styles =  array('margin-bottom'=>'5px')): array|string
    {
        // Validación si el registro está vacío
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro esta vacio', data:  $registro);
        }

        // Validación de los datos de la acción permitida
        $valida = $this->valida_data_action(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion_permitida', data:  $valida);
        }

        // Obtener el estilo del botón
        $style = $html->style_btn(accion_permitida: $accion_permitida, row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style', data:  $style);
        }

        // Asignación del icono de la acción
        $icon = $accion_permitida['adm_accion_icono'];

        // Obtener datos del ícono
        $data_icon = (new params())->data_icon(adm_accion: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }

        // Generación del enlace (botón)
        $link = $html->button_href(accion: $accion_permitida['adm_accion_descripcion'],
            etiqueta: $accion_permitida['adm_accion_titulo'], registro_id: $registro_id,
            seccion: $accion_permitida['adm_seccion_descripcion'], style: $style, cols: $cols, icon: $icon,
            muestra_icono_btn: $data_icon->muestra_icono_btn, muestra_titulo_btn: $data_icon->muestra_titulo_btn,
            params: $params, styles: $styles);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link', data:  $link);
        }

        return $link;
    }




    /**
     * REG
     * Valida los datos de una acción permitida.
     *
     * Esta función valida la existencia y la validez de varios campos en un registro de acción permitida.
     * Se asegura de que los campos requeridos estén presentes y no vacíos, que el estilo CSS sea válido,
     * y que el ícono de la acción esté definido.
     *
     * **Pasos de validación:**
     * 1. Verifica que los campos esenciales como `adm_accion_descripcion`, `adm_accion_titulo`, etc.,
     *    estén presentes en el arreglo de la acción permitida.
     * 2. Valida que el estilo CSS proporcionado en `adm_accion_css` sea un valor válido, según una lista predefinida de estilos.
     * 3. Asegura que el campo `adm_accion_icono` esté presente en el arreglo de la acción permitida.
     *
     * Si alguna de las validaciones falla, se genera un error con un mensaje específico. Si todas las validaciones
     * pasan correctamente, la función devuelve `true`.
     *
     * @param array $accion_permitida Registro de la acción permitida que se va a validar.
     *     Este parámetro debe ser un arreglo que contenga los siguientes campos:
     *     - `adm_accion_descripcion`: Descripción de la acción.
     *     - `adm_accion_titulo`: Título de la acción.
     *     - `adm_seccion_descripcion`: Descripción de la sección a la que pertenece la acción.
     *     - `adm_accion_css`: Estilo CSS para la acción.
     *     - `adm_accion_es_status`: Estado de la acción.
     *     - `adm_accion_muestra_icono_btn`: Define si la acción muestra un ícono en el botón.
     *     - `adm_accion_muestra_titulo_btn`: Define si la acción muestra un título en el botón.
     *     - `adm_accion_icono`: Icono asociado a la acción (opcional).
     *
     * @return bool|array Devuelve:
     *  - `true` si todas las validaciones pasan correctamente.
     *  - Un arreglo con el mensaje de error si alguna de las validaciones falla.
     *
     * @throws errores Si alguna validación falla, se genera un error que se captura y devuelve como un mensaje.
     *
     * @example Ejemplo 1: Validar una acción permitida correcta
     * ```php
     * $accion_permitida = [
     *     'adm_accion_descripcion' => 'Alta',
     *     'adm_accion_titulo' => 'Crear nuevo',
     *     'adm_seccion_descripcion' => 'Usuarios',
     *     'adm_accion_css' => 'info',
     *     'adm_accion_es_status' => true,
     *     'adm_accion_muestra_icono_btn' => true,
     *     'adm_accion_muestra_titulo_btn' => true,
     *     'adm_accion_icono' => 'add'
     * ];
     * $resultado = $this->valida_data_action($accion_permitida);
     * // Retorna true si todos los campos son válidos.
     * ```
     *
     * @example Ejemplo 2: Validar acción permitida con campo CSS inválido
     * ```php
     * $accion_permitida = [
     *     'adm_accion_descripcion' => 'Alta',
     *     'adm_accion_titulo' => 'Crear nuevo',
     *     'adm_seccion_descripcion' => 'Usuarios',
     *     'adm_accion_css' => 'invalid_css',
     *     'adm_accion_es_status' => true,
     *     'adm_accion_muestra_icono_btn' => true,
     *     'adm_accion_muestra_titulo_btn' => true,
     *     'adm_accion_icono' => 'add'
     * ];
     * $resultado = $this->valida_data_action($accion_permitida);
     * // Retorna un arreglo con el mensaje de error: 'Error style invalido invalid_css'.
     * ```
     *
     * @version 1.0.0
     */
    final public function valida_data_action(array $accion_permitida): bool|array
    {
        // Validación de la existencia de los campos esenciales
        $keys = array('adm_accion_descripcion', 'adm_accion_titulo', 'adm_seccion_descripcion', 'adm_accion_css',
            'adm_accion_es_status', 'adm_accion_muestra_icono_btn', 'adm_accion_muestra_titulo_btn');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $accion_permitida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar  accion_permitida', data: $valida);
        }

        // Validación del estilo CSS
        $valida = $this->validacion->valida_estilo_css(style: $accion_permitida['adm_accion_css']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener style', data: $valida);
        }

        // Validación de la existencia del ícono de la acción
        $keys = array('adm_accion_icono');
        $valida = $this->validacion->valida_existencia_keys(
            keys: $keys, registro: $accion_permitida, valida_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar  accion_permitida', data: $valida);
        }

        // Si todas las validaciones son exitosas, devuelve true
        return true;
    }


    /**
     * @param mixed $accion_permitida Accion a validar
     * @param string $key_id key a validar
     * @param array|stdClass $row Registro en proceso
     * @return bool|array
     * @version 0.236.37
     */
    private function valida_data_btn(mixed $accion_permitida, string $key_id, array|stdClass $row): bool|array
    {
        $key_id = trim($key_id);
        if($key_id === ''){
            return $this->error->error(mensaje: 'Error key_id esta vacio',data:  $key_id);
        }
        $keys = array($key_id);
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row',data:  $valida);
        }
        if(!is_array($accion_permitida)){
            return $this->error->error(mensaje: 'Error accion_permitida debe ser array',data:  $accion_permitida);
        }

        $valida = $this->valida_data_action(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }
        return true;
    }




}
