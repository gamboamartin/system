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
     * Obtiene los botones permitidos definidos por la session en ejecucion
     * @param array $acciones_permitidas Conjunto de acciones permitidas
     * @param int $cols Columnas de div css
     * @param html_controler $html Html base
     * @param array $params Parametros para GET
     * @param array $params_ajustados parametros para GET custom
     * @param array $registro Registro en proceso
     * @param int $registro_id Identificador
     * @param array $styles Estilos css a integrar en contenedor como style
     * @return array
     * @version 10.24.0
     */
    private function buttons_permitidos(array $acciones_permitidas, int $cols, html_controler $html,
                                        array $params, array $params_ajustados, array $registro, int $registro_id,
                                        array $styles =  array('margin-bottom'=>'5px')): array
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro esta vacio',data:  $registro);
        }
        $buttons = array();
        foreach ($acciones_permitidas as $accion_permitida){
            $params_btn = $params;
            if(isset($params_ajustados[$accion_permitida['adm_accion_descripcion']])){
                $params_btn = $params_ajustados[$accion_permitida['adm_accion_descripcion']];
            }
            $link = $this->link_btn_action(accion_permitida: $accion_permitida, cols: $cols,
                html: $html, params: $params_btn, registro: $registro, registro_id: $registro_id, styles: $styles);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar link',data:  $link);
            }
            $buttons[] = $link;
        }
        return $buttons;
    }

    /**
     * Integra los botones de ejecucion de acciones permitidas en una vista
     * @param system $controler Controlador en ejecucion
     * @param array $not_actions Acciones a omitir
     * @param array $params Parametros para GET
     * @param array $params_ajustados Parametros para GET
     * @param array $styles Estilos css por incrustar en div o contenedor
     * @return array
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
     * Genera un link de tipo accion
     * @param array $accion_permitida Accion permitida
     * @param int $cols N cols css
     * @param html_controler $html Base html
     * @param array $params Parametros para GET
     * @param array $registro Registro en proceso
     * @param int $registro_id Identificador de registro
     * @param array $styles Estilos css
     * @return array|string
     * @version 0.253.37
     */

    private function link_btn_action(array $accion_permitida, int $cols, html_controler $html, array $params,
                                     array $registro, int $registro_id,
                                     array $styles =  array('margin-bottom'=>'5px')): array|string
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro esta vacio',data:  $registro);
        }
        $valida = $this->valida_data_action(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion_permitida',data:  $valida);
        }

        $style = $html->style_btn(accion_permitida: $accion_permitida, row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }

        $icon = $accion_permitida['adm_accion_icono'];



        $data_icon = (new params())->data_icon(adm_accion: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }


        $link = $html->button_href(accion: $accion_permitida['adm_accion_descripcion'],
            etiqueta: $accion_permitida['adm_accion_titulo'], registro_id: $registro_id,
            seccion: $accion_permitida['adm_seccion_descripcion'], style: $style, cols: $cols, icon: $icon,
            muestra_icono_btn: $data_icon->muestra_icono_btn, muestra_titulo_btn: $data_icon->muestra_titulo_btn,
            params: $params, styles: $styles);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link',data:  $link);
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
