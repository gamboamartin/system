<?php
namespace gamboamartin\system;

use base\controller\controler;
use base\orm\modelo;
use base\orm\modelo_base;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\params;
use gamboamartin\system\html_controler\select;
use gamboamartin\system\html_controler\template;
use gamboamartin\system\html_controler\texts;
use gamboamartin\system\html_controler\validacion_html;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;

use PDO;
use stdClass;

class html_controler{
    public directivas $directivas;
    protected errores $error;
    public html $html_base;
    protected validacion_html $validacion;

    public function __construct(html $html){
        $this->directivas = new directivas(html: $html);
        $this->error = new errores();
        $this->html_base = $html;
        $this->validacion = new validacion_html();
    }

    /**
     * Genera los parametros para un link de tipo button
     * @param string $cols_html Columnas css
     * @param string $link Link referencia ejecucion
     * @param string $role Role de boton submit o button
     * @param string $style Estilos base del boton
     * @param string $style_custom Estilos agregados
     * @param string $target Result de ejecucion link
     * @param string $title Titulo del boton
     * @return string|array
     * @version 8.83.0
     */
    private function a_params(string $cols_html, string $link, string $role, string $style, string $style_custom,
                              string $target, string $title): string|array
    {
        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error style esta vacio',data:  $style);
        }
        $title = trim($title);
        if($title === ''){
            return $this->error->error(mensaje: 'Error title esta vacio',data:  $title);
        }
        $role = trim($role);
        if($role === ''){
            return $this->error->error(mensaje: 'Error role esta vacio',data:  $role);
        }

        $target_html = '';
        $target = trim($target);
        if($target !==''){
            $target_html = "target='$target'";
        }

        $params = "role='$role' title='$title' href='$link' class='btn btn-$style $cols_html' $style_custom";
        $params .= " $target_html";
        $params = trim($params);
        $i=0;
        $iteraciones = 5;
        while ($i<=$iteraciones){
            $params =  str_replace('  ', ' ', $params);
            $i++;
        }
        return $params;

    }

    /**
     * Integra un href para btns
     * @param int $cols Columnas css
     * @param string $etiqueta_html Etiqueta a mostrar
     * @param string $icon_html Icono a mostrar
     * @param string $link Liga de href
     * @param string $role tipo de sole button o submit
     * @param string $style Stilo de boton
     * @param array $styles Estilos css
     * @param string $target Ejecucion de ventana resultado
     * @param string $title Titulo a mostrar del button
     * @return string|array
     */
    private function a_role(int $cols, string $etiqueta_html, string $icon_html, string $link, string $role,
                            string $style, array $styles, string $target, string $title): string|array
    {

        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error style esta vacio',data:  $style);
        }
        $title = trim($title);
        if($title === ''){
            $title = $etiqueta_html;
        }

        if($title === ''){
            return $this->error->error(mensaje: 'Error title esta vacio',data:  $title);
        }

        $cols_html = $this->cols_html(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar cols html', data: $cols_html);
        }

        $role = $this->role_button(role: $role);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar role', data: $role);
        }

        $style_custom = $this->genera_styles_custom(styles: $styles);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar style_custom', data: $style_custom);
        }

        $params = $this->a_params(cols_html: $cols_html, link: $link, role: $role, style: $style,
            style_custom: $style_custom, target: $target, title: $title);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params', data: $params);
        }

        $a = $this->a_role_button(etiqueta_html: $etiqueta_html,icon_html:  $icon_html, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar button', data: $a);
        }

        return $a;
    }

    private function a_role_button(string $etiqueta_html, string $icon_html, string $params): string|array
    {
        $etiqueta_html = trim($etiqueta_html);
        $icon_html = trim($icon_html);
        $params = trim($params);
        if($params === ''){
            return $this->error->error(mensaje: 'Error al params esta vacio', data: $params);
        }

        $data_a = $icon_html.' '.$etiqueta_html;
        $data_a = trim($data_a);
        if($data_a === ''){
            return $this->error->error(mensaje: 'Error al data_a esta vacio', data: $data_a);
        }

        $a = "<a $params>$data_a</a>";

        $i = 0;
        while($i<=5){
            $a = str_replace('  ', ' ', $a);
            $i++;
        }

        return $a;
    }

    /**
     * Genera los inputs base de un alta de cualquier controller que herede
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.16.5
     */
    final public function alta(system $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        $cols = new stdClass();
        $cols->codigo = 6;
        $cols->codigo_bis = 6;
        $inputs_base = $this->inputs_base(cols:$cols, controler: $controler, value_vacio: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }


    /**
     * Integra un boton link para rows de lista
     * @param array $accion_permitida Datos de accion
     * @param int $indice Indice de matriz de rows
     * @param int $registro_id Registro en proceso
     * @param array $rows registros
     * @param array $params Extraparams
     * @return array
     * @version 0.165.34
     */
    final public function boton_link_permitido(array $accion_permitida, int $indice, int $registro_id, array $rows,
                                         array $params = array()): array
    {
        $valida = $this->validacion->valida_boton_link(
            accion_permitida: $accion_permitida,indice:  $indice,registro_id:  $registro_id,rows:  $rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data:  $valida);
        }
        $valida = $this->valida_boton_data_accion(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion_permitida',data:  $valida);
        }


        $style = $this->style_btn(accion_permitida: $accion_permitida, row: $rows[$indice]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }

        $accion = $accion_permitida['adm_accion_descripcion'];
        $etiqueta = $accion_permitida['adm_accion_titulo'];
        $seccion = $accion_permitida['adm_seccion_descripcion'];

        $icon = $accion_permitida['adm_accion_icono'];



        $data_icon = (new params())->data_icon(adm_accion: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }


        $link = $this->button_href(accion: $accion, etiqueta: $etiqueta, registro_id: $registro_id, seccion: $seccion,
            style: $style, cols: -1, icon: $icon, muestra_icono_btn: $data_icon->muestra_icono_btn,
            muestra_titulo_btn: $data_icon->muestra_titulo_btn, params: $params, styles: array('margin-right'=>'2px'));
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link',data:  $link);
        }

        if(!is_array($rows[$indice])){
            return $this->error->error(mensaje: 'rows['.$indice.'] debe ser una array',data:  $rows);
        }

        if(!isset($rows[$indice]['acciones'])){
            $rows[$indice]['acciones'] = array();
        }

        if(array_key_exists($accion_permitida['adm_accion_descripcion'], $rows[$indice]['acciones'])){
            return $this->error->error(mensaje: 'Error la accion esta repetida',data:  $accion_permitida);
        }

        $rows[$indice]['acciones'][$accion_permitida['adm_accion_descripcion']] = $link;

        return $rows;
    }

    final public function boton_submit(string $class_button, string $class_control, string $style, string $tag,
                                       string $id_button = '' ): string
    {
        return "
            <div class='control-group $class_control'>
                <div class='controls'>
                    <button type='submit' class='btn btn-$style $class_button' id='$id_button'>$tag</button>
                </div>
            </div>";
    }

    /**
     * Genera un boton href
     * @param string $accion Accion a ejecutar
     * @param string $etiqueta Etiqueta de boton
     * @param int $registro_id Registro a integrar
     * @param string $seccion Seccion a ejecutar
     * @param string $style Stilo del boton
     * @param int $cols N columnas css
     * @param string $icon Icono a mostrar en boton
     * @param bool $muestra_icono_btn Si true entonces muestra icono definido en icon
     * @param bool $muestra_titulo_btn Si true entonces muestra etiqueta definido en etiqueta
     * @param array $params extra-params
     * @param string $role Role de link , button, submit etc
     * @param array $styles Propiedades css custom
     * @param string $target Ejecucion de ventana resultado
     * @return string|array
     */
    final public function button_href(string $accion, string $etiqueta, int $registro_id, string $seccion,
                                      string $style, int $cols = 12, string $icon = '', bool $muestra_icono_btn = false,
                                      bool $muestra_titulo_btn = true, array $params = array(),
                                      string $role = 'button', array $styles = array(),
                                      string $target = ''): string|array
    {

        $valida = $this->html_base->valida_input(accion: $accion,etiqueta:  $etiqueta, seccion: $seccion,style:  $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $session_id = (new generales())->session_id;

        if($session_id === ''){
            return $this->error->error(mensaje: 'Error la $session_id esta vacia', data: $session_id);
        }


        $params_btn = $this->params_btn(icon: $icon,etiqueta:  $etiqueta,muestra_icono_btn:  $muestra_icono_btn,
            muestra_titulo_btn:  $muestra_titulo_btn,params:  $params);

        if(errores::$error){
            $params_error = array();
            $params_error['accion'] = $accion;
            $params_error['seccion'] = $seccion;
            $params_error['muestra_titulo_btn'] = $muestra_titulo_btn;
            $params_error['icon'] = $icon;
            return $this->error->error(mensaje: 'Error al generar parametros de btn', data: $params_btn,
                params: $params_error);
        }

        $link = $this->link_a(accion: $accion, params_get: $params_btn->params_get, registro_id: $registro_id,
            seccion:  $seccion,session_id:  $session_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        $a = $this->a_role(cols: $cols, etiqueta_html: $params_btn->etiqueta_html, icon_html: $params_btn->icon_html,
            link: $link, role: $role, style: $style, styles: $styles, target: $target, title: $etiqueta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar a', data: $a);
        }

        return $a;
    }

    /**
     * Genera un boton para ser utilizado con java
     * @param string $id_css Identificador css
     * @param string $style Estilo del boton
     * @param string $tag Etiqueta del boton
     * @return string|array
     * @version 11.9.0
     */
    final public function button_para_java(string $id_css, string $style, string $tag): string|array
    {
        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error style esta vacio', data: $style);
        }
        $id_css = trim($id_css);
        if($id_css === ''){
            return $this->error->error(mensaje: 'Error id_css esta vacio', data: $id_css);
        }
        $tag = trim($tag);
        if($tag === ''){
            return $this->error->error(mensaje: 'Error tag esta vacio', data: $tag);
        }
        return "<a class='btn btn-$style' role='button' id='$id_css'>$tag</a>";

    }

    /**
     * Genera el elemento cols en forma de html
     * @param int $cols No de columnas css
     * @return string
     * @version 8.65.0
     */
    private function cols_html(int $cols): string
    {
        $cols_html = "col-sm-$cols";
        if($cols === -1){
            $cols_html = '';
        }
        return $cols_html;
    }

    /**
     * Genera los inputs de tipo fechas date
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de inputs
     * @return array|stdClass
     */
    final protected function dates_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $dates = new stdClass();

        foreach ($campos_view['dates'] as $item){

            $item = trim($item);
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }

            $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->dates_template(directivas: $this->directivas, params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $dates->$item = $date;
        }

        return $dates;
    }

    private function div_input_text(array $class_css,int $cols, bool $disabled, array $ids_css, string $name,
                                    string $place_holder, string $regex, bool $required, stdClass $row_upd,
                                    string $title, bool $value_vacio, string|null $value = '' ): array|string
    {

        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $html =$this->directivas->input_text_base(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, class_css: $class_css, ids_css: $ids_css, regex: $regex,
            required: $required, title: $title,value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }
        return $div;
    }

    /**
     * Integra un input de texto required en un div
     * @param int $cols Columnas css
     * @param bool $disabled attr disabled
     * @param array $ids_css Identificadores extra de id
     * @param string $name name input
     * @param string $place_holder etiqueta input
     * @param string $regex integra un regex a un pattern
     * @param stdClass $row_upd registro en proceso
     * @param string $title title de input
     * @param bool $value_vacio valor vacio
     * @return array|string
     * @version 7.43.2
     */
    private function div_input_text_required(int $cols, bool $disabled, array $ids_css, string $name,
                                             string $place_holder, string $regex, stdClass $row_upd,
                                             string $title, bool $value_vacio ): array|string
    {

        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, ids_css: $ids_css, regex: $regex, title: $title);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }
        return $div;
    }

    /**
     * Genera los email de una view
     * @param modelo $modelo Datos del modelo
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects parametros
     * @return array|stdClass
     * @version 5.1.0
     * @final rev
     */
    protected function emails_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $emails = new stdClass();

        foreach ($campos_view['emails'] as $item){

            $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->emails_template(directivas: $this->directivas, params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $emails->$item = $date;
        }

        return $emails;
    }

    /**
     * Integra la etiqueta html
     * @param string $etiqueta Etiqueta a convertir
     * @param bool $muestra_titulo_btn valida si se muestra o no
     * @return array|string
     */
    private function etiqueta_html(string $etiqueta, bool $muestra_titulo_btn): array|string
    {
        $etiqueta_html = '';
        if($muestra_titulo_btn){
            $etiqueta = trim($etiqueta);
            if($etiqueta === ''){
                return $this->error->error(
                    mensaje: 'Error si muestra_titulo_btn entonces etiqueta no puede venir vacio', data: $etiqueta);
            }
            $etiqueta_html = $etiqueta;
        }
        return $etiqueta_html;
    }

    /**
     * Genera los inputs de fecha
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Keys params
     * @return array|stdClass
     * @final rev
     * @version 7.38.2
     */
    protected function fechas_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $fechas = new stdClass();

        foreach ($campos_view['fechas'] as $item){

            $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $fecha = (new template())->fechas_template(directivas: $this->directivas, params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $fecha);
            }
            $fechas->$item = $fecha;
        }

        return $fechas;
    }

    /**
     * Genera un input de tipo file
     * @param array $campos_view campos de modelos para views
     * @param array $keys_selects parametros de selectores
     * @param stdClass $row_upd Registro en proceso
     * @return array|stdClass
     * @version 0.292.39
     */
    private function file_items(array $campos_view, array $keys_selects, stdClass $row_upd): array|stdClass
    {
        if(!isset($campos_view['files'])){
            $campos_view['files'] = array();
        }

        if(!is_array($campos_view['files'])){
            return $this->error->error(mensaje: 'Error campos_view[files] debe ser un array', data: $campos_view);
        }
        $files = new stdClass();
        foreach ($campos_view['files'] as $item){
            $item = trim($item);
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }
            if($item === ''){
                return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
            }

            $files = $this->text_item(item: $item,keys_selects:  $keys_selects,row_upd:  $row_upd, texts: $files);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $files);
            }
        }
        return $files;
    }

    /**
     * Genera los inputs de tipo file
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de inputs
     * @return array|stdClass
     * @version 0.293.39
     */

    final protected function files_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $files = $this->file_items(campos_view: $campos_view,keys_selects:  $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $files);
        }

        return $files;
    }

    /**
     * Integra un input de tipo FILE
     * @param stdClass $params_select Parametros de input
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 0.290.39
     */
    public function file_template(stdClass $params_select, stdClass $row_upd): array|string
    {
        $keys = array('cols','disabled','name','place_holder','required','value_vacio');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $params_select, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = (new validacion())->valida_ids(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('name','place_holder');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }


        $html =$this->directivas->input_file(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder, required: $params_select->required, row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    private function genera_styles_custom(array $styles){
        $propiedades = $this->propiedades_css(styles: $styles);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar propiedades', data: $propiedades);
        }


        $style_custom = $this->style_custom(propiedades: $propiedades);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar style_custom', data: $style_custom);
        }
        return $style_custom;
    }

    /**
     * Integra un header collapsible
     * @param string $id_css_button Identificador css
     * @param string $style_button Estilo de boton
     * @param string $tag_button Etiqueta de boton
     * @param string $tag_header Etiqueta de seccion
     * @return array|string
     * @version 13.83.2
     */
    final public function header_collapsible(string $id_css_button, string $style_button, string $tag_button,
                                             string $tag_header): array|string
    {
        $style_button = trim($style_button);
        if($style_button === ''){
            return $this->error->error(mensaje: 'Error style_button esta vacio', data: $style_button);
        }
        $id_css_button = trim($id_css_button);
        if($id_css_button === ''){
            return $this->error->error(mensaje: 'Error id_css_button esta vacio', data: $id_css_button);
        }
        $tag_button = trim($tag_button);
        if($tag_button === ''){
            return $this->error->error(mensaje: 'Error tag_button esta vacio', data: $tag_button);
        }

        $btn = $this->button_para_java(id_css: $id_css_button,style:  $style_button,tag:  $tag_button);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al btn_collapse_all',data:  $btn);
        }

        $html = "<div class='col-md-12'>";
        $html .= "<hr><h4>$tag_header $btn </h4><hr>";
        $html .= "</div>";

        return trim($html);
    }

    /**
     * Genera un input de tipo hidden
     * @param string $name Nombre del input
     * @param string $value Valor del input
     * @return array|string
     * @version 0.159.34
     */
    final public function hidden(string $name, string $value): array|string
    {
        $name = trim($name);
        if($name === ''){
            return $this->error->error(mensaje: 'Error name esta vacio',data:  $name);
        }
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data:  $value);
        }
        
        return "<input type='hidden' name='$name' value='$value'>";
    }

    protected function init_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $selects = $this->selects_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    private function icon_html(string $icon, bool $muestra_icono_btn): array|string
    {
        $icon_html = '';
        if($muestra_icono_btn){
            $icon = trim($icon);
            if($icon === ''){
                return $this->error->error(mensaje: 'Error si muestra_icono_btn entonces icon no puede venir vacio',
                    data: $icon);
            }
            $icon_html = "<span class='$icon'></span>";
        }
        return $icon_html;
    }

    /**
     * Integra los inputs para frontend
     * @param stdClass $row_upd Registro en proceso
     * @param modelo $modelo Modelo en proceso
     * @param array $keys_selects Parametros visuales de inputs
     * @return array|stdClass
     *
     */
    final public function init_alta2(stdClass $row_upd, modelo $modelo,
                                     array $keys_selects = array()): array|stdClass
    {
        $selects = $this->selects_alta2(modelo: $modelo, keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta2(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $files = $this->files_alta2(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar files', data: $texts);
        }

        $dates = $this->dates_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar dates', data: $dates);
        }

        $passwords = $this->passwords_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar passwords', data: $dates);
        }

        $telefonos = $this->telefonos_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar telefonos', data: $dates);
        }

        $emails = $this->emails_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emails', data: $dates);
        }
        $fechas = $this->fechas_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emails', data: $dates);
        }

        $fields = array();
        $fields['selects'] = $selects;
        $fields['inputs'] = $texts;
        $fields['files'] = $files;
        $fields['dates'] = $dates;
        $fields['passwords'] = $passwords;
        $fields['telefonos'] = $telefonos;
        $fields['emails'] = $emails;
        $fields['fechas'] = $fechas;

        return $fields;
    }


    /**
     * Genera un input de tipo codigo
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.72.32
     */
    public function input_codigo(int $cols, stdClass $row_upd, bool $value_vacio,bool $disabled = false,
                                 string $place_holder = 'Código'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'codigo',
            place_holder: $place_holder,row_upd: $row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo codigo bis
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.73.32
     */
    public function input_codigo_bis(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                 string $place_holder = 'Código BIS'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'codigo_bis',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo descripcion
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $con_label Si no con label deja vacio el input
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.74.32
     */
    public function input_descripcion(int $cols, stdClass $row_upd, bool $value_vacio, bool $con_label = true,
                                      bool $disabled = false, string $place_holder = 'Descripcion'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'descripcion',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, con_label: $con_label);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera un input de tipo fecha
     * @param int $cols Cols css
     * @param stdClass $row_upd registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled attr disabled
     * @param string $name Name input
     * @param string $place_holder Tag de input
     * @param bool $required Atributo required default true
     * @param mixed|null $value value input
     * @param bool $value_hora Integra hora si true
     * @return array|string
     * @version 13.66.1
     */
    public function input_fecha(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                string $name ='fecha', string $place_holder = 'Fecha', bool $required = true,
                                mixed $value = null, bool $value_hora = false): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols );
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_fecha_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, required: $required, value: $value, value_hora: $value_hora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera un input de tipo ID
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled si disabled deshabilita input
     * @param string $place_holder etiqueta a mostrar
     * @return array|string
     * @version 0.75.32
     */
    public function input_id(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                             string $place_holder = 'Id'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cols debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'id',place_holder:$place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Obtiene un input de tipo monto
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $con_label Si con label integra el label en el input
     * @param bool $disabled Si disabled integra atributo disabled en input
     * @param string $name Name del input
     * @param string $place_holder Info input
     * @param mixed|null $value Valor default
     * @return array|string
     * @version 8.63.0
     *
     */
    public function input_monto(int $cols, stdClass $row_upd, bool $value_vacio,bool $con_label = true,
                                bool $disabled = false, string $name = 'monto', string $place_holder = 'Monto',
                                mixed $value = null): array|string
    {

        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_monto_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, con_label: $con_label, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_numero_int(int $cols, string $name, stdClass $row_upd, bool $value_vacio,
                                     bool $disabled = false, array $ids_css = array(), string $place_holder = 'Numero',
                                     string $title = 'Numero'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cols debe ser menor o igual a  12', data: $cols);
        }

        $regex = $this->validacion->patterns['entero_positivo_html'];

        $div = $this->div_input_text_required(cols: $cols, disabled: $disabled, ids_css: $ids_css, name: $name,
            place_holder: $place_holder, regex: $regex, row_upd: $row_upd, title: $title, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * @param stdClass $cols Objeto con la definicion del numero de columnas a integrar en un input base
     * @version 0.11.5
     * @param system $controler
     * @param bool $value_vacio
     * @return array|stdClass
     */
    final protected function inputs_base(stdClass $cols, controler $controler, bool $value_vacio): array|stdClass
    {

        $keys = array('codigo','codigo_bis');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        $valida = (new validacion())->valida_numerics(keys: $keys, row: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }
        if(empty($controler->inputs)){
            $controler->inputs = new stdClass();
        }

        $html_codigo = $this->directivas->input_codigo(cols: $cols->codigo,row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }

        $controler->inputs->codigo = $html_codigo;

        $html_codigo_bis = $this->directivas->input_codigo_bis(cols: $cols->codigo_bis,
            row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }

        $controler->inputs->codigo_bis = $html_codigo_bis;

        $html_descripcion = $this->directivas->input_descripcion(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion);
        }
        $controler->inputs->descripcion = $html_descripcion;

        $html_alias = $this->directivas->input_alias(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_alias);
        }
        $controler->inputs->alias = $html_alias;

        $html_descripcion_select = $this->directivas->input_descripcion_select(row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion_select);
        }
        $controler->inputs->descripcion_select = $html_descripcion_select;

        return $controler->inputs;
    }

    public function input_file(int $cols, string $name, stdClass $row_upd, bool $value_vacio,bool $disabled = false,
                               string $place_holder = 'Documento', bool $required = true): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_file(disabled: $disabled, name: $name, place_holder: $place_holder,
            required: $required, row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    final public function input_text(int $cols, bool $disabled, string $name, string $place_holder, stdClass $row_upd,
                                     bool $value_vacio, array $class_css = array(), array $ids_css = array(),
                                     string $regex = '', bool $required = true, string $title = '',
                                     string|null $value = ''): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $class_css[] = 'form-control';
        $class_css[] = $name;

        $div = $this->div_input_text(class_css: $class_css, cols: $cols, disabled: $disabled,
            ids_css: $ids_css, name: $name, place_holder: $place_holder, regex: $regex, required: $required,
            row_upd: $row_upd, title: $title, value_vacio: $value_vacio, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }


    /**
     * Genera un input text required
     * @param int $cols N columnas css
     * @param bool $disabled attr disable
     * @param string $name Name input
     * @param string $place_holder Tag Input
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio si vacio no valida
     * @param array $ids_css Identificadores extra id de css y java
     * @param string $regex integra atributo pattern
     * @param string $title integra un title a input
     * @return array|string
     * @version 11.1.0
     */
    final public function input_text_required(int $cols, bool $disabled, string $name, string $place_holder,
                                              stdClass $row_upd, bool $value_vacio, array $ids_css = array(),
                                              string $regex = '', string $title = ''): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $div = $this->div_input_text_required(cols: $cols, disabled: $disabled, ids_css: $ids_css,
            name: $name, place_holder: $place_holder, regex: $regex, row_upd: $row_upd, title: $title,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    private function integra_password_item(string $item, array $keys_selects, stdClass $passwords, stdClass $row_upd){
        $item = $this->item(item: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar item', data: $item);
        }

        $passwords = $this->passwords(item: $item,keys_selects:  $keys_selects,passwords:  $passwords,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }

        return $passwords;
    }

    /**
     * Integra una propiedad css
     * @param string $propiedad Nombre de la propiedad
     * @param string $propiedades Propiedades previas cargadas
     * @param string $valor Valor de la propiedad a integrar
     * @return string|array
     * @version 13.68.1
     */
    private function integra_propiedad(string $propiedad, string $propiedades, string $valor): string|array
    {

        $valida = $this->valida_propiedad(propiedad: $propiedad,valor:  $valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar propiedad', data: $valida);
        }

        $propiedades.= $propiedad.': '.$valor.'; ';
        return $propiedades;
    }

    /**
     * Integra los inputs de tipo selects
     * @param array $keys_selects Keys a integrar
     * @param mixed $modelo Modelo de selector
     * @param string $item Nombre del input
     * @param stdClass $selects Selects previos cargados
     * @return array|stdClass
     * @version 10.9.0
     */
    private function integra_select(array $keys_selects, mixed $modelo, string $item, stdClass $selects): array|stdClass
    {
        $valida = $this->valida_data_select(keys_selects: $keys_selects,modelo:  $modelo,item:  $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida item', data: $valida);
        }

        $params_select = $this->params_select(item: $item, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa params_select', data: $params_select);
        }

        $select = $this->select_aut2(modelo: $modelo,params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->$item = $select;
        return $selects;
    }

    /**
     * Genera in item para salida de front
     * @param string $item Campo o input
     * @return array|string
     */
    private function item(string $item): array|string
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }
        return $item;
    }

    /**
     * @param string $accion Accion en ejecucion
     * @param string $params_get Parametros extra get
     * @param int $registro_id Registro en proceso
     * @param string $seccion Seccion en proceso
     * @param string $session_id Session
     * @return string
     */
    private function link_a(string $accion, string $params_get, int $registro_id, string $seccion, string $session_id): string
    {
        $adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $adm_menu_id = $_GET['adm_menu_id'];
        }
        $link = "index.php?seccion=$seccion&accion=$accion&registro_id=$registro_id&session_id=$session_id&adm_menu_id=$adm_menu_id";
        $link .= $params_get;
        return $link;
    }

    /**
     * Genera un menu lateral con titulo
     * @param string $etiqueta Etiqueta del menu
     * @return array|string
     * @version 0.93.32
     */
    public function menu_lateral_title(string $etiqueta): array|string
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la etiqueta esta vacia', data: $etiqueta);
        }
        $menu_lateral = $this->html_base->menu_lateral($etiqueta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar menu lateral texto', data: $menu_lateral);
        }
        return $menu_lateral;

    }

    /**
     * Inicializa la base para modifica frontend
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.102.32
     */
    final public function modifica(controler $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }

        $html_id = $this->directivas->input_id(cols:4,row_upd: $controler->row_upd,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_id);
        }
        $controler->inputs->id = $html_id;

        $cols = new stdClass();
        $cols->codigo = 4;
        $cols->codigo_bis = 4;
        $inputs_base = $this->inputs_base(cols:$cols,controler: $controler,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }

    /**
     * Obtiene el nombre del modelo
     * @param string $name_model nombre base del modelo
     * @param stdClass $params parametros precargados
     * @return string|array
     */
    private function name_model(string $name_model, stdClass $params): string|array
    {
        $name_model = trim($name_model);
        if(isset($params->name_model)){
            $name_model = $params->name_model;
        }
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        return $name_model;
    }

    /**
     * Obtiene el namespace de los parametros integrados
     * @param stdClass $params
     * @return string
     */
    private function namespace_model(stdClass $params): string
    {
        $namespace_model = '';
        if(isset($params->namespace_model)){
            $namespace_model = $params->namespace_model;
        }
        return $namespace_model;
    }

    /**
     * @param array|stdClass $campos_view Campos definidos desde modelo
     * @return array|stdClass
     */
    final protected function obtener_inputs(array|stdClass $campos_view): array|stdClass
    {
        $selects = array();
        $inputs = array();
        $files = array();
        $dates = array();
        $passwords = array();
        $telefonos = array();
        $emails = array();
        $fechas = array();

        foreach ($campos_view as $item => $campo){

            $es_campo_valido = false;
            if(is_object($campo)){
                $es_campo_valido = true;
            }
            if(is_array($campo)){
                $es_campo_valido = true;
            }
            if(!$es_campo_valido){
                return $this->error->error(mensaje: 'Error el campo debe ser un array o stdclass', data: $campo);
            }
            if (!isset($campo['type'])){
                return $this->error->error(mensaje: 'Error no existe key type', data: $campo);
            }
            if(!is_string($campo['type'])){
                return $this->error->error(mensaje: 'Error type debe ser un string', data: $campo);
            }

            $tipo_input = $this->obtener_tipo_input(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener el tipo de input', data: $tipo_input);
            }

            switch ($tipo_input) {

                case 'selects':
                    $select = $this->obtener_select(campo: $campo);
                    if(errores::$error){
                        return $this->error->error(mensaje: 'Error al obtener select', data: $select);
                    }
                    $selects[$item] = $select;
                    break;
                case 'inputs':
                    $inputs[] = $item;
                    break;
                case 'files':
                    $files[] = $item;
                    break;
                case 'passwords':
                    $passwords[] = $item;
                    break;
                case 'telefonos':
                    $telefonos[] = $item;
                    break;
                case 'emails':
                    $emails[] = $item;
                    break;
                case 'fechas':
                    $fechas[] = $item;
                    break;
                case 'dates':
                    $dates[] = $item;
                    break;

            }

        }
        return ['selects' => $selects,'inputs' => $inputs,'files' => $files,'dates' => $dates,'passwords'=>$passwords,
            'telefonos'=>$telefonos,'emails'=>$emails,'fechas'=>$fechas];
    }

    /**
     * Obtiene un modelo basado en campo
     * @param array $campo Conjunto de modelos
     * @return array|modelo
     * @version 0.120.33
     */
    final protected function obtener_select(array $campo): array|modelo
    {
        if (!isset($campo['model'])){
            return $this->error->error(mensaje: 'Error no existe key model', data: $campo);
        }

        if (!is_object($campo['model'])) {
            return $this->error->error(mensaje: 'Error: El modelo brindado no esta definido', data: $campo);
        }

        return $campo['model'];
    }

    /**
     * Obtiene el tipo de input para templates
     * @param array|stdClass $campo
     * @return string|array
     */
    final protected function obtener_tipo_input(array|stdClass $campo): string|array
    {
        if(is_object($campo)){
            $campo = (array)$campo;
        }
        if (!isset($campo['type'])){
            return $this->error->error(mensaje: 'Error no existe key type', data: $campo);
        }
        if(!is_string($campo['type'])){
            return $this->error->error(mensaje: 'Error type debe ser un string', data: $campo);
        }
        return trim($campo['type']);
    }

    private function params_btn(string $icon, string $etiqueta, bool $muestra_icono_btn, bool $muestra_titulo_btn, array $params){
        $params_get = $this->params_get(params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params_get', data: $params_get);
        }
        $icon_html = $this->icon_html(icon: $icon,muestra_icono_btn:  $muestra_icono_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar icon_html', data: $icon_html);
        }
        $etiqueta_html = $this->etiqueta_html(etiqueta: $etiqueta,muestra_titulo_btn:  $muestra_titulo_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar etiqueta_html', data: $etiqueta_html);
        }
        $data = new stdClass();
        $data->params_get = $params_get;
        $data->icon_html = $icon_html;
        $data->etiqueta_html = $etiqueta_html;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función toma un arreglo de parámetros y los convierte en una string tipo GET.
     * Esto es útil para hacer peticiones HTTP GET con múltiples parámetros.
     *
     * @param array $params Arreglo de parámetros que se convertirán en string tipo GET.
     *                      Cada llave y valor del arreglo se transforma en la forma 'llave=valor'.
     *                      Luego se unen todos los pares 'llave=valor' con el simbolo '&' entre ellos.
     *                      Es necesario que cada llave y valor estén no sean vacíos y que la llave no sea numérica.
     *
     *
     * @return string|array Devuelve la cadena formada con parámetros tipo GET.
     *                      En caso de error, devolverá un error indicando el problema encontrado con detallado en la data.
     *                      Los errores pueden ser 'Error en key no puede venir vacio', 'Error en key debe ser un texto' ó
     *                      'Error en value no puede venir vacio' con la data correspondiente al valor o llave con error.
     *
     * @throws errores Si ocurre alguna excepción, se arroja tal cual.
     * @version 18.15.0
     */
    private function params_get(array $params): string|array
    {
        $params_get = '';
        foreach ($params as $key=>$value){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error en key no puede venir vacio', data: $key);
            }
            if(is_numeric($key)){
                return $this->error->error(mensaje: 'Error en key debe ser un texto', data: $key);
            }
            $value = trim($value);
            if($value === ''){
                return $this->error->error(mensaje: 'Error en value no puede venir vacio', data: $value);
            }
            $params_get .= "&$key=$value";
        }
        return $params_get;
    }

    /**
     * Integra los parametros de un select
     * @param string $item Nombre del input
     * @param array $keys_selects Parametros de input select
     * @return array|stdClass
     * @version 10.8.0
     */
    private function params_select(string $item, array $keys_selects): array|stdClass
    {
        $item = trim($item);
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = new stdClass();

        if (array_key_exists($item, $keys_selects) ){
            $params_select = $keys_selects[$item];
        }

        $params_select = (new params())->params_select_col_6(params: $params_select,label: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }
        return $params_select;
    }

    /**
     * @param string $name_model
     * @param stdClass $params
     * @return array|stdClass
     */
    private function params_select_info(string $name_model, stdClass $params): array|stdClass
    {
        $tabla = $name_model;


        $name_model = $this->name_model(name_model: $name_model, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar name_model', data: $name_model);
        }
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        if(is_numeric($name_model)){
            return $this->error->error(mensaje: 'Error $name_model debe ser el nombre de un modelo valido',
                data: $name_model);
        }


        $namespace_model = $this->namespace_model(params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace_model', data: $namespace_model);
        }

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->name_model = $name_model;
        $data->namespace_model = $namespace_model;
        return $data;
    }

    /**
     * Obtiene el item de un input de tipo pass
     * @param array $campos_view Campos definidos en el modelo
     * @return array
     * @version 4.10.1
     */
    private function pass_item_init(array $campos_view): array
    {
        if(!isset($campos_view['passwords'])){
            $campos_view['passwords'] = array();
        }
        if(!is_array($campos_view['passwords'])){
            return $this->error->error(mensaje: 'Error campos_view[passwords] debe se run array', data: $campos_view);
        }
        return $campos_view;

    }

    /**
     * Integra los inputs de tipo password
     * @param string $item Campo
     * @param array $keys_selects Params de inputs
     * @param stdClass $passwords inputs previamente cargados
     * @param stdClass $row_upd Registro en proceso
     * @return array|stdClass
     * @version 3.9.1
     */
    private function passwords(string $item, array $keys_selects, stdClass $passwords, stdClass $row_upd): array|stdClass
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }
        $date = (new template())->passwords_template(directivas: $this->directivas, params_select: $params_select,
            row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $date);
        }
        $passwords->$item = $date;
        return $passwords;
    }

    /**
     * Integra para front los passwords para alta
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros para front
     * @return array|stdClass
     * @version 4.8.1
     */
    final protected function passwords_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $passwords = $this->passwords_campos(campos_view: $campos_view, keys_selects: $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }
        return $passwords;
    }

    /**
     * Genera los passwords inputs
     * @param array $campos_view Campos de modelo
     * @param array $keys_selects parametros de los inputs
     * @param stdClass $row_upd registro en proceso
     * @return array|stdClass
     * 3.12.1
     */
    private function passwords_campos(array $campos_view, array $keys_selects, stdClass $row_upd): array|stdClass
    {
        $campos_view = $this->pass_item_init(campos_view: $campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $campos_view);
        }
        $passwords = $this->passwords_campos_view(campos_view: $campos_view,keys_selects:  $keys_selects,
            row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }

        return $passwords;
    }

    private function passwords_campos_view(array $campos_view, array $keys_selects, stdClass $row_upd){
        $passwords = new stdClass();
        foreach ($campos_view['passwords'] as $item){
            $passwords = $this->integra_password_item(item: $item,keys_selects:  $keys_selects,passwords:  $passwords,row_upd:  $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
            }
        }
        return $passwords;
    }

    /**
     * Integra las propiedades css integradas en el elemento
     * @param array $styles Estilos previos css
     * @return array|string
     * @version 13.85.2
     */
    private function propiedades_css(array $styles): array|string
    {
        $propiedades = '';

        foreach ($styles as $propiedad=>$valor){

            $valida = $this->valida_propiedad(propiedad: $propiedad,valor:  $valor);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar propiedad', data: $valida);
            }

            $propiedades = $this->integra_propiedad(propiedad: $propiedad,propiedades:  $propiedades,valor:  $valor);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar propiedades', data: $propiedades);
            }
        }
        return $propiedades;
    }
    /**
     * Retornos hidden
     * @param int $registro_id Registro id a retornar
     * @param string $tabla Tabla a retornar
     * @return array|stdClass
     */
    final public function retornos(int $registro_id, string $tabla): array|stdClass
    {

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        $registro_id = trim($registro_id);
        if($registro_id === ''){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data:  $registro_id);
        }

        $hidden_id_retorno = $this->hidden(name: 'id_retorno', value: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener hidden_id_retorno',data:  $hidden_id_retorno);
        }
        $hidden_seccion_retorno = $this->hidden(name: 'seccion_retorno', value: $tabla);
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error al obtener hidden_seccion_retorno',data:  $hidden_seccion_retorno);
        }
        $data = new stdClass();
        $data->hidden_id_retorno = $hidden_id_retorno;
        $data->hidden_seccion_retorno = $hidden_seccion_retorno;
        return $data;
    }

    /**
     * Integra el role legible html boostrap
     * @param string $role Role de button
     * @return string
     * @version 13.67.1
     */
    private function role_button(string $role): string
    {
        $role = trim($role);
        if($role === ''){;
            $role = 'button';
        }
        return $role;
    }


    /**
     * Genera un select automatico conforme a params
     * @param PDO $link Conexion a la BD
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros a ejecutar para select
     * @param stdClass $selects Selects precargados
     * @param string $namespace_model Nombre del namespace
     * @param string $tabla Tabla de datos
     * @return array|stdClass
     * @version 8.93.1
     */
    private function select_aut(
        PDO $link, string $name_model, stdClass $params, stdClass $selects,string $namespace_model = '' ,
        string $tabla = ''): array|stdClass
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        $params_select = (new params())->params_select(name_model: $name_model, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar params', data: $params_select);
        }
        if($tabla === ''){
            $tabla = $name_model;
        }

        $name_select_id = $tabla.'_id';
        $modelo = (new modelo_base($link))->genera_modelo(modelo: $name_model,namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $select  = $this->select_catalogo(cols: $params_select->cols, con_registros: $params_select->con_registros,
            id_selected: $params_select->id_selected, modelo: $modelo, columns_ds: $params_select->columns_ds,
            disabled: $params_select->disabled, filtro: $params_select->filtro, label: $params_select->label,
            not_in: $params_select->not_in, required: $params_select->required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }

        $selects->$name_select_id = $select;

        return $selects;
    }

    /**
     * Genera un select
     * @param modelo $modelo Modelo del select
     * @param stdClass $params_select Parametros visuales
     * @return array|stdClass|string
     * @example $params_select->extra_params_keys[] = 'tabla_id'; integra un extra param al option de un select
     * @version 10.3.0
     */
    private function select_aut2(modelo $modelo, stdClass $params_select): array|stdClass|string
    {
        $keys = array('cols','con_registros','id_selected','disabled','extra_params_keys','filtro','label','not_in',
            'required','registros', 'in');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('key_descripcion_select');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $params_select, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols','id_selected');
        $valida = $this->validacion->valida_numerics(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('con_registros','disabled','required');
        $valida = $this->validacion->valida_bools(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('extra_params_keys','filtro','not_in', 'in', 'columns_ds','registros');
        $valida = $this->validacion->valida_arrays(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $modelo_preferido = false;

        if(is_object($params_select->modelo_preferido)){
            $modelo_preferido = $params_select->modelo_preferido;
        }

        $select  = $this->select_catalogo(cols: $params_select->cols, con_registros: $params_select->con_registros,
            id_selected: $params_select->id_selected, modelo: $modelo, modelo_preferido: $modelo_preferido,
            columns_ds: $params_select->columns_ds, disabled: $params_select->disabled,
            extra_params_keys: $params_select->extra_params_keys, filtro: $params_select->filtro,
            key_descripcion_select: $params_select->key_descripcion_select, label: $params_select->label,
            not_in: $params_select->not_in, in: $params_select->in, registros: $params_select->registros,
            required: $params_select->required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    /**
     * Genera el input de tipo select
     * @param int $cols Numero de columnas boostrap
     * @param bool $con_registros Si con registros , obtiene todos los registros activos del modelo en ejecucion
     *  para la asignacion de options, Si no, deja el select en blanco o vacio
     * @param int|string|float|null $id_selected Identificador de un registro y cargado utilizado para modifica, aplica selected
     * @param modelo $modelo Modelo de datos ejecucion
     * @param modelo|bool $modelo_preferido
     * @param array $class_css Integra elementos css como class en select
     * @param array $columns_ds
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param array $extra_params_keys Extraparams datos a obtener para integrar en data-extra
     * @param array $filtro Filtro para obtencion de datos
     * @param string $id_css Si esta vacio integra el id como name
     * @param string $key_descripcion
     * @param string $key_descripcion_select Key para mostrar en options
     * @param string $key_id Key para integrar el value
     * @param string $key_value_custom
     * @param string $label Etiqueta a mostrar en select
     * @param string $name Nombre del input
     * @param array $not_in Omite los elementos en obtencion de datos
     * @param array $in
     * @param array $registros
     * @param bool $required si required agrega el atributo required a input
     * @param bool $valida_key_id
     * @return array|string Un string con options en forma de html
     */
    final public function select_catalogo(int $cols, bool $con_registros, int|null|string|float $id_selected,
                                          modelo $modelo, modelo|bool $modelo_preferido = false,
                                          array $class_css = array(), array $columns_ds = array(),
                                          bool $disabled = false, array $extra_params_keys = array(),
                                          array $filtro=array(), string $id_css = '', string $key_descripcion = '',
                                          string $key_descripcion_select = '', string $key_id = '',
                                          string $key_value_custom = '', string $label = '', string $name = '',
                                          array $not_in = array(), array $in = array(), array $registros = array(),
                                          bool $required = false, bool $valida_key_id = true): array|string
    {

        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(is_object($modelo_preferido)){

            $id_selected = $modelo_preferido->id_preferido_detalle($modelo->tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener id preferido', data: $id_selected);
            }

        }

        $init = (new select())->init_data_select(con_registros: $con_registros, modelo: $modelo,
            columns_ds: $columns_ds, extra_params_keys: $extra_params_keys, filtro: $filtro,
            key_descripcion: $key_descripcion, key_descripcion_select: $key_descripcion_select, key_id: $key_id,
            key_value_custom: $key_value_custom, label: $label, name: $name, not_in: $not_in, in: $in,
            registros: $registros, valida_key_id: $valida_key_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $init);
        }

        $select = $this->html_base->select(cols: $cols, id_selected: $id_selected, label: $init->label,
            name: $init->name, values: $init->values, class_css: $class_css, columns_ds: $columns_ds,
            disabled: $disabled, extra_params_key: $extra_params_keys, id_css: $id_css,
            key_value_custom: $key_value_custom, required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


    private function selects(PDO $link, string $name_model, stdClass $params, stdClass $selects){
        $data_params = $this->params_select_info(name_model: $name_model,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar parametros de info select', data: $data_params);
        }


        $selects  = $this->select_aut(link: $link,name_model:  $data_params->name_model,params:  $params, selects: $selects,
            namespace_model: $data_params->namespace_model, tabla: $data_params->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $selects);
        }

        return $selects;
    }

    /**
     * Genera selects en volumen con parametros
     * @param array $keys_selects conjunto de selects
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.100.32
     */
    protected function selects_alta(array $keys_selects, PDO $link): array|stdClass
    {

        $selects = new stdClass();

        foreach ($keys_selects as $name_model=>$params){

            if(!is_object($params)){
                return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $params);
            }

            $selects  = $this->selects(link: $link, name_model: $name_model,params:  $params,selects:  $selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $selects);
            }

        }

        return $selects;

    }

    /**
     * Genera los selects para una view
     * @param modelo $modelo Modelo en ejecucion
     * @param array $keys_selects Parametros de selects
     * @return array|stdClass
     */
    final protected function selects_alta2(modelo $modelo,array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $selects = $this->selects_integra(campos_view: $campos_view, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar selects', data: $selects);
        }

        return $selects;
    }

    /**
     * Integra los selects para views
     * @param array $campos_view Campos precargados
     * @param array $keys_selects Selectores params
     * @return array|stdClass
     * @example keys_selects['name_input']->extra_params_keys[] = 'tabla_id';
     * integra un extra param al option de un select
     * @version 10.10.0
     */
    private function selects_integra(array $campos_view, array $keys_selects): array|stdClass
    {
        $selects = new stdClass();

        if(!isset($campos_view['selects'])){
            $campos_view['selects'] = array();
        }

        foreach ($campos_view['selects'] as $item => $modelo){
            $selects = $this->integra_select(keys_selects: $keys_selects,modelo:  $modelo,item:  $item,
                selects:  $selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
            }
        }
        return $selects;
    }

    /**
     * Obtiene el estilo de un boton
     * @param array $accion_permitida Accion del boton
     * @param array $row Registro en proceso
     * @return array|string
     * @version 0.237.37
     */
    final public function style_btn(array $accion_permitida, array $row):array|string{

        if(count($row) === 0){
            return $this->error->error(mensaje: 'Error row esta vacio',data:  $row);
        }
        $valida = $this->valida_boton_data_accion(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion_permitida',data:  $valida);
        }

        $style = $accion_permitida['adm_accion_css'];
        $es_status = $accion_permitida['adm_accion_es_status'];
        $accion = $accion_permitida['adm_accion_descripcion'];
        $seccion = $accion_permitida['adm_seccion_descripcion'];
        $key_es_status = $seccion.'_'.$accion;
        if($es_status === 'activo'){
            $style = $this->style_btn_status(key_es_status: $key_es_status, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
            }
        }
        return $style;
    }

    /**
     * Obtiene el estilo de un boton
     * @param string $key_es_status Key del boton
     * @param array $row Registro en proceso
     * @return array|string
     * @version 0.235.37
     */
    private function style_btn_status(string $key_es_status, array $row): array|string
    {
        $key_es_status = trim($key_es_status);
        if($key_es_status === ''){
            return $this->error->error(mensaje: 'Error key_es_status esta vacio',data:  $key_es_status);
        }
        if(count($row) === 0){
            return $this->error->error(mensaje: 'Error row esta vacio',data:  $row);
        }

        $keys = array($key_es_status);
        $valida = $this->validacion->valida_statuses(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  registro',data:  $valida);
        }

        $style = 'danger';
        if($row[$key_es_status] === 'activo'){
            $style = 'success';
        }
        return $style;
    }

    /**
     * @param string $propiedades
     * @return string
     */
    private function style_custom(string $propiedades): string
    {
        $style_custom = '';
        if($propiedades!==''){
            $style_custom = "style='$propiedades'";
        }
        return $style_custom;
    }

    /** Genera el template de telefonos para frontend
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de estilos
     * @return array|stdClass
     * @version 4.44.2
     * @final rev
     *
     */
    protected function telefonos_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $telefonos = new stdClass();

        foreach ($campos_view['telefonos'] as $item){

            $item = trim($item);
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }


            $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->telefonos_template(directivas: $this->directivas,
                params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $telefonos->$item = $date;
        }

        return $telefonos;
    }

    /**
     * @param string $item Key de input
     * @param array $keys_selects Parametros
     * @param stdClass $row_upd Registro en proceso
     * @param stdClass $texts Inputs
     * @return array|stdClass
     * @version 0.291.39
     */
    private function text_item(string $item, array $keys_selects, stdClass $row_upd, stdClass $texts): array|stdClass
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = (new params())->params_select_init(item: $item, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params', data: $params_select);
        }

        $keys = array('name','place_holder');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $input = $this->file_template(params_select: $params_select,row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $input);
        }
        $texts->$item = $input;
        return $texts;
    }

    /**
     * Funcion base altas txt
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio si vacio deja in input vacio
     * @param stdClass $params parametros a integrar
     * @return array|stdClass
     * @version 0.119.33
     *
     */
    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        return new stdClass();
    }

    /**
     * Integra los inputs de tipo text
     * @param modelo $modelo Modelo en ejecucion para uso campos view
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Params de inputs
     * @return array|stdClass
     */
    final protected function texts_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $texts = (new texts())->texts_integra(campos_view: $campos_view, directivas: $this->directivas,
            keys_selects:  $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $texts);
        }

        return $texts;
    }

    final public function valida_boton_data_accion(array $accion_permitida): bool|array
    {
        $keys = array('adm_accion_css','adm_accion_es_status','adm_accion_descripcion','adm_seccion_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $accion_permitida',data:  $valida);
        }
        $valida = $this->validacion->valida_estilo_css(style: $accion_permitida['adm_accion_css']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $valida);
        }

        $keys = array('adm_accion_es_status');
        $valida = $this->validacion->valida_statuses(keys:$keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $accion_permitida',data:  $valida);
        }
        return true;
    }

    /**
     * Valida los datos de un select
     * @param array $keys_selects Keys a verificar
     * @param mixed $modelo Modelo a verificar
     * @param string $item Item o nombre del campo
     * @return array|true
     * @version 10.7.0
     */
    private function valida_data_select(array $keys_selects, mixed $modelo, string $item): bool|array
    {
        $valida = $this->valida_item(item: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida item', data: $valida);
        }

        if (array_key_exists($item, $keys_selects) && !is_object($keys_selects[$item])){
            return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $keys_selects[$item]);
        }

        if(!is_object($modelo)){
            return $this->error->error(mensaje: 'Error modelo no es un objeto valido', data: $modelo);
        }
        return true;
    }

    /**
     * Valida que la entrada de item sea correcta, debe ser un texto con letras
     * @param string $item Valor a verificar
     * @return bool|array
     * @version 10.6.0
     */
    private function valida_item(string $item): bool|array
    {
        $item = trim($item);
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item es un numero', data: $item);
        }
        return true;
    }

    /**
     * valida los parametros de entrada de una propiedad css
     * @param string $propiedad Propiedad a integrar
     * @param string $valor Valor a integrar
     * @return bool|array
     * @version 13.84.2
     */
    private function valida_propiedad(string $propiedad, string $valor): bool|array
    {
        $propiedad = trim($propiedad);
        if($propiedad === ''){
            return $this->error->error(mensaje: 'Error propiedad esta vacio', data: $propiedad);
        }
        $valor = trim($valor);
        if($valor === ''){
            return $this->error->error(mensaje: 'Error valor esta vacio', data: $valor);
        }
        if(is_numeric($propiedad)){
            return $this->error->error(mensaje: 'Error propiedad debe ser texto', data: $propiedad);
        }
        return true;
    }







}
