<?php
namespace gamboamartin\system;

use base\controller\controler;
use base\orm\modelo;
use base\orm\modelo_base;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;


use PDO;
use stdClass;

class html_controler{
    public directivas $directivas;
    protected errores $error;
    public html $html_base;
    protected validacion $validacion;

    public function __construct(html $html){
        $this->directivas = new directivas(html: $html);
        $this->error = new errores();
        $this->html_base = $html;
        $this->validacion = new validacion();
    }

    /**
     * Genera los inputs base de un alta de cualquier controller que herede
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.16.5
     */
    public function alta(system $controler): array|stdClass
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
    public function boton_link_permitido(array $accion_permitida, int $indice, int $registro_id, array $rows,
                                         array $params = array()): array
    {


        $valida = $this->valida_boton_link(
            accion_permitida: $accion_permitida,indice:  $indice,registro_id:  $registro_id,rows:  $rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data:  $valida);
        }

        $style = $this->style_btn(accion_permitida: $accion_permitida, row: $rows[$indice]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }

        $accion = $accion_permitida['adm_accion_descripcion'];
        $etiqueta = $accion_permitida['adm_accion_titulo'];
        $seccion = $accion_permitida['adm_seccion_descripcion'];

        $link = $this->button_href(accion: $accion, etiqueta: $etiqueta, registro_id:  $registro_id, seccion: $seccion,
            style:  $style, params: $params);
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

    /**
     * Genera un boton href
     * @param string $accion Accion a ejecutar
     * @param string $etiqueta Etiqueta de boton
     * @param int $registro_id Registro a integrar
     * @param string $seccion Seccion a ejecutar
     * @param string $style Stilo del boton
     * @param int $cols N columnas css
     * @param array $params extra-params
     * @param string $role
     * @return string|array
     * @version 0.164.34
     */
    public function button_href(string $accion, string $etiqueta, int $registro_id, string $seccion,
                                string $style, int $cols = 12, array $params = array(), string $role = 'button'): string|array
    {

        $valida = $this->html_base->valida_input(accion: $accion,etiqueta:  $etiqueta, seccion: $seccion,style:  $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $session_id = (new generales())->session_id;

        if($session_id === ''){
            return $this->error->error(mensaje: 'Error la $session_id esta vacia', data: $session_id);
        }

        $params_get = '';
        foreach ($params as $key=>$value){
            $params_get .= "&$key=$value";
        }

        $link = "index.php?seccion=$seccion&accion=$accion&registro_id=$registro_id&session_id=$session_id";
        $link .= $params_get;
        return "<a role='$role' href='$link' class='btn btn-$style col-sm-$cols'>$etiqueta</a>";
    }

    /**
     * @refactorizar Refactoriza
     * @param modelo $modelo
     * @param stdClass $row_upd
     * @param array $keys_selects
     * @return array|stdClass
     */
    protected function dates_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $dates = new stdClass();

        foreach ($campos_view['dates'] as $item){

            /**
             * REFACTORIZAR
             */
            $params_select = new stdClass();

            if (array_key_exists($item, $keys_selects) ){
                $params_select = $keys_selects[$item];
            }

            $params_select = $this->params_input2(params: $params_select,name: $item,place_holder: $item);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }

            $date = $this->dates_template(params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $dates->$item = $date;
        }

        return $dates;
    }

    /**
     * Integra los datos para un template
     * @param stdClass $params_select Parametros de select
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 0.233.37
     */
    private function dates_template(stdClass $params_select, stdClass $row_upd): array|string
    {
        $keys = array('cols','disabled','name','place_holder','value_vacio');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = $this->validacion->valida_numerics(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('disabled','value_vacio');
        $valida = $this->validacion->valida_bools(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->fecha_required(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder,  row_upd: $row_upd,
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

    protected function files_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $texts = new stdClass();

        foreach ($campos_view['files'] as $item){
            /**
             * REFCATORIZAR
             */
            $params_select = new stdClass();

            if (array_key_exists($item, $keys_selects) ){
                $params_select = $keys_selects[$item];
            }

            $params_select = $this->params_input2(params: $params_select,name: $item,place_holder: $item);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }

            $input = $this->file_template(params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $input);
            }
            $texts->$item = $input;
        }

        return $texts;
    }

    public function file_template(mixed $params_select, stdClass $row_upd): array|string
    {
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

    /**
     * Asigna los values de un select
     * @refactorizar Refactorizar
     * @param stdClass $keys Keys para asignacion basica
     * @param array $registros Conjunto de registros a integrar
     * @return array
     * @version 0.48.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-02 18:12
     * @author mgamboa
     */
    private function genera_values_selects(stdClass $keys, array $registros): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }
        $values = array();
        foreach ($registros as $registro){
            /**
             * REFACTORIZAR
             */
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error registro debe ser un array',data:  $registro);
            }
            $keys_valida = array($keys->id,$keys->descripcion_select);
            $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
            }

            $values[$registro[$keys->id]] = $registro;
            $values[$registro[$keys->id]]['descripcion_select'] = $registro[$keys->descripcion_select];
        }
        return $values;
    }

    /**
     * Genera un input de tipo hidden
     * @param string $name Nombre del input
     * @param string $value Valor del input
     * @return array|string
     * @version 0.159.34
     */
    public function hidden(string $name, string $value): array|string
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

    public function init_alta2(stdClass $row_upd, modelo $modelo, array $keys_selects = array()): array|stdClass
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

        $fields = array();
        $fields['selects'] = $selects;
        $fields['inputs'] = $texts;
        $fields['files'] = $files;
        $fields['dates'] = $dates;

        return $fields;
    }

    /**
     * Inicializa los datos de un select
     * @refactorizar Refactorizar metodo
     * @param bool $con_registros Si no con registros integra el select vacio para ser llenado posterior con ajax
     * @param modelo $modelo Modelo en ejecucion para la asignacion de datos
     * @param array $extra_params_keys Keys de extra params para ser cargados en un select
     * @param array $filtro Filtro para obtencion de datos para options
     * @param string $key_descripcion_select key del registro para mostrar en un select
     * @param string $key_id key Id de value para option
     * @param string $label Etiqueta a mostrar
     * @param string $name Nombre del input
     * @param array $not_in Omite resultado de options
     * @return array|stdClass
     * @version 0.52.32
     * @version 0.55.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:55
     * @author mgamboa
     */
    private function init_data_select(bool $con_registros, modelo $modelo, array $extra_params_keys = array(),
                                      array $filtro = array(), string $key_descripcion_select= '', string $key_id = '',
                                      string $label = '', string $name = '', array $not_in = array()): array|stdClass
    {

        $keys = $this->keys_base(tabla: $modelo->tabla, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, name: $name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(con_registros: $con_registros, keys: $keys,modelo: $modelo,
            extra_params_keys: $extra_params_keys, filtro: $filtro, not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valores',data:  $values);
        }

        /**
         * REFACTORIZAR
         */
        $label_ =$label;
        if($label_ === '') {
            $label_ = $this->label(tabla: $modelo->tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener label', data: $label_);
            }
        }

        $keys->values = $values;
        $keys->label = $label_;
        return $keys;
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
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.74.32
     */
    public function input_descripcion(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                      string $place_holder = 'Descripcion'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'descripcion',place_holder: $place_holder,
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
     * @param stdClass $cols Objeto con la definicion del numero de columnas a integrar en un input base
     * @version 0.11.5
     * @param system $controler
     * @param bool $value_vacio
     * @return array|stdClass
     */
    protected function inputs_base(stdClass $cols, controler $controler, bool $value_vacio): array|stdClass
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

    public function input_template(mixed $params_select, stdClass $row_upd): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text(disabled: $params_select->disabled, name: $params_select->name,
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

    /**
     * Genera un input text required
     * @param int $cols N columnas css
     * @param bool $disabled attr disable
     * @param string $name Name input
     * @param string $place_holder Tag Input
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio si vacio no valida
     * @return array|string
     * @version 0.130.33
     */
    protected function input_text_required(int $cols, bool $disabled, string $name, string $place_holder,
                                           stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: $name,place_holder: $place_holder,
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

    private function integra_thead(string $ths): string
    {
        return "<thead><tr>$ths</tr></thead>";
    }

    /**
     * Asigna los keys necesarios para un select
     * @param string $tabla Tabla del select
     * @param string $key_descripcion_select base de descripcion
     * @param string $key_id identificador key
     * @param string $name Name del input
     * @return stdClass|array obj->id, obj->descripcion_select
     * @version 0.2.5
     * @verfuncion 0.2.0 Se carga name
     */
    private function keys_base(string $tabla, string $key_descripcion_select = '', string $key_id = '',
                               string $name = ''): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        if($key_id === '') {
            $key_id = $tabla . '_id';
        }
        $name = trim($name);
        if($name === ''){
            $name = $key_id;
        }
        if($key_descripcion_select === '') {
            $key_descripcion_select = $tabla.'_descripcion_select';
        }


        $data = new stdClass();
        $data->id = $key_id;
        $data->descripcion_select = $key_descripcion_select;
        $data->name = $name;

        return $data;
    }

    /**
     * Genera un label valido para se mostrado en front
     * @param string $tabla Tabla o estructura para generar etiqueta
     * @return string|array
     * @version 0.50.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-03 09:22
     * @author mgamboa
     */
    private function label(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $label = str_replace('_', ' ', $tabla);

        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error $label esta vacio', data: $label);
        }


        return ucwords($label);
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
    public function modifica(controler $controler): array|stdClass
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
     * @param array|stdClass $campos_view Campos definidos desde modelo
     * @return array|stdClass
     * @version 0.211.37
     */
    protected function obtener_inputs(array|stdClass $campos_view): array|stdClass
    {
        $selects = array();
        $inputs = array();
        $files = array();
        $dates = array();

        foreach ($campos_view as $item => $campo){
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
                case 'dates':
                    $dates[] = $item;
                    break;
            }

        }
        return ['selects' => $selects,'inputs' => $inputs,'files' => $files,'dates' => $dates];
    }

    /**
     * Obtiene un modelo basado en campo
     * @param array $campo Conjunto de modelos
     * @return array|modelo
     * @version 0.120.33
     */
    protected function obtener_select(array $campo): array|modelo
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
     * @version 0.205.36
     */
    protected function obtener_tipo_input(array|stdClass $campo): string|array
    {
        if (!isset($campo['type'])){
            return $this->error->error(mensaje: 'Error no existe key type', data: $campo);
        }
        if(!is_string($campo['type'])){
            return $this->error->error(mensaje: 'Error type debe ser un string', data: $campo);
        }
        return trim($campo['type']);
    }

    /**
     * Inicializa los parametros para un input
     * @param stdClass $data Data precargado
     * @param string $name Nombre del input
     * @param stdClass $params Parametros
     * @return stdClass
     * @version 0.185.34
     */
    private function params_base(stdClass $data, string $name, stdClass $params): stdClass
    {
        $data->disabled = $params->disabled ?? false;
        $data->con_registros = $params->con_registros ?? true;
        $data->id_selected = $params->id_selected ?? -1;
        $data->required = $params->required ?? true;
        $data->row_upd = $params->row_upd ?? new stdClass();
        $data->value_vacio = $params->value_vacio ?? false;
        $data->filtro = $params->filtro ?? array();
        $data->not_in = $params->not_in ?? array();
        $data->name = $params->name ?? $name;

        $data->extra_params_keys = array();
        if(isset($params->extra_params_keys) ){
            $data->extra_params_keys = $params->extra_params_keys;
        }


        return $data;
    }

    /**
     * Inicializa los parametros para un input
     * @param stdClass $params Parametros precargados
     * @param string $name Name input
     * @param string $place_holder Label del input
     * @return stdClass|array
     * @version 0.228.37
     */
    private function params_input2(stdClass $params, string $name,string $place_holder): stdClass|array
    {
        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->place_holder = $params->place_holder ?? $place_holder;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($place_holder));

        $data = $this->params_base(data: $data, name: $name ,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Inicializa los parametros de un select
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros inicializados
     * @return stdClass|array
     * @version 0.95.32
     */
    private function params_select(string $name_model, stdClass $params): stdClass|array
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }
        $data = new stdClass();

        $data->cols = $params->cols ?? 12;
        $data->place_holder = $params->place_holder ?? $name_model;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($name_model));

        $data = $this->params_base(data: $data, name : $name_model,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Ajusta los parametros
     * @param stdClass $params Parametros precargados
     * @param string $label Etiqueta a mostrar en input
     * @return stdClass|array
     * @version 0.212.37
     */
    private function params_select_col_6(stdClass $params, string $label): stdClass|array
    {
        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error label esta vacio', data: $label);
        }
        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->label = $params->label ?? str_replace('_',' ', $label);

        $data->label = str_replace('  ', ' ', $data->label);

        $data = $this->params_base(data: $data,name:  $label,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }


        return $data;
    }

    /**
     * Retornos hidden
     * @param int $registro_id Registro id a retornar
     * @param string $tabla Tabla a retornar
     * @return array|stdClass
     * @version 0.187.35
     */
    public function retornos(int $registro_id, string $tabla): array|stdClass
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
     * Obtiene los registros para un select
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo del select
     * @param array $extra_params_keys Datos a integrar para extra params
     * @param array $filtro Filtro de datos para filtro and
     * @param array $not_in Omite resultados para options
     * @return array
     * @version 0.47.32
     * @version 0.53.32
     * @verfuncion 0.1.0 UT fin
     * @verfuncion 0.2.0 Se integra param filtro
     * @fecha 2022-08-02 17:32
     * @fecha 2022-08-02 17:32
     * @author mgamboa
     */
    private function rows_select(stdClass $keys, modelo $modelo, array $extra_params_keys = array(),
                                 array $filtro = array(), array $not_in = array()): array
    {
        $keys_val = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_val,registro:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;

        foreach ($extra_params_keys as $key){
            /**
             * REFACTORIZAR
             */
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error el key de extra params esta vacio',data:  $extra_params_keys);
            }
            $columnas[] = $key;
        }

        $filtro[$modelo->tabla.'.status'] = 'activo';
        $registros = $modelo->filtro_and(columnas: $columnas, filtro: $filtro, not_in: $not_in);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }
        return $registros->registros;
    }

    /**
     * Genera un select automatico conforme a params
     * @param PDO $link Conexion a la BD
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros a ejecutar para select
     * @param stdClass $selects Selects precargados
     * @param string $namespace_model
     * @param string $tabla
     * @return array|stdClass
     * @version 0.96.32
     */
    private function select_aut(
        PDO $link, string $name_model, stdClass $params, stdClass $selects,string $namespace_model = '' ,
        string $tabla = ''): array|stdClass
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        $params_select = $this->params_select(name_model: $name_model, params: $params);
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
            id_selected: $params_select->id_selected, modelo: $modelo, disabled: $params_select->disabled,
            filtro: $params_select->filtro, label: $params_select->label, not_in: $params_select->not_in,
            required: $params_select->required);
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
     * @version 0.227.38
     */
    PUBLIC function select_aut2(modelo $modelo, stdClass $params_select): array|stdClass|string
    {
        $keys = array('cols','con_registros','id_selected','disabled','extra_params_keys','filtro','label','not_in',
            'required');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $params_select);
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

        $keys = array('extra_params_keys','filtro','not_in');
        $valida = $this->validacion->valida_arrays(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $select  = $this->select_catalogo(cols: $params_select->cols, con_registros: $params_select->con_registros,
            id_selected: $params_select->id_selected, modelo: $modelo, disabled: $params_select->disabled,
            extra_params_keys: $params_select->extra_params_keys, filtro: $params_select->filtro,
            label: $params_select->label, not_in: $params_select->not_in, required: $params_select->required);
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
     * @param int $id_selected Identificador de un registro y cargado utilizado para modifica, aplica selected
     * @param modelo $modelo Modelo de datos ejecucion
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param array $extra_params_keys Extraparams datos a obtener para integrar en data-extra
     * @param array $filtro Filtro para obtencion de datos
     * @param string $key_descripcion_select Key para mostrar en options
     * @param string $key_id Key para integrar el value
     * @param string $label Etiqueta a mostrar en select
     * @param string $name Nombre del input
     * @param bool $required si required agrega el atributo required a input
     * @param array $not_in Omite los elementos en obtencion de datos
     * @return array|string Un string con options en forma de html
     * @version 0.56.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-03 16:27
     * @author mgamboa
     */
    protected function select_catalogo(int $cols, bool $con_registros, int $id_selected, modelo $modelo,
                                       bool $disabled = false, array $extra_params_keys = array(),
                                       array $filtro=array(), string $key_descripcion_select = '', string $key_id = '',
                                       string $label = '', string $name = '', array $not_in = array(),
                                       bool $required = false): array|string
    {

        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $init = $this->init_data_select(con_registros: $con_registros, modelo: $modelo,
            extra_params_keys: $extra_params_keys, filtro:$filtro, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, label: $label, name: $name, not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $init);
        }

        $select = $this->html_base->select(cols:$cols, id_selected:$id_selected, label: $init->label,name:$init->name,
            values: $init->values, disabled: $disabled, extra_params_key: $extra_params_keys, required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
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

            /**
             * REFCATORIZAR LUNES GET NAME MODEL GET TABLA
             */
            if(!is_object($params)){
                return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $params);
            }
            $tabla = $name_model;

            if(isset($params->name_model)){
                $name_model = $params->name_model;
            }

            $name_model = trim($name_model);
            if($name_model === ''){
                return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
            }
            if(is_numeric($name_model)){
                return $this->error->error(mensaje: 'Error $name_model debe ser el nombre de un modelo valido',
                    data: $name_model);
            }

            $namespace_model = '';
            if(isset($params->namespace_model)){
                $namespace_model = $params->namespace_model;
            }

            $selects  = $this->select_aut(link: $link,name_model:  $name_model,params:  $params, selects: $selects,
                namespace_model: $namespace_model, tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $selects);
            }

        }

        return $selects;

    }

    protected function selects_alta2(modelo $modelo,array $keys_selects = array()): array|stdClass
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
     * @version 0.232.37
     */
    private function selects_integra(array $campos_view, array $keys_selects): array|stdClass
    {
        $selects = new stdClass();

        if(!isset($campos_view['selects'])){
            $campos_view['selects'] = array();
        }

        foreach ($campos_view['selects'] as $item => $modelo){
            $item = trim($item);
            if($item === ''){
                return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
            }
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item es un numero', data: $item);
            }

            if (array_key_exists($item, $keys_selects) && !is_object($keys_selects[$item])){
                return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $keys_selects[$item]);
            }

            if(!is_object($modelo)){
                return $this->error->error(mensaje: 'Error modelo no es un objeto valido', data: $modelo);
            }


            $params_select = new stdClass();

            if (array_key_exists($item, $keys_selects) ){
                $params_select = $keys_selects[$item];
            }

            $params_select = $this->params_select_col_6(params: $params_select,label: $item);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }

            $select = $this->select_aut2(modelo: $modelo,params_select: $params_select);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $select);
            }
            $selects->$item = $select;
        }
        return $selects;
    }

    public function style_btn(array $accion_permitida, array $row){
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

    protected function texts_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $texts = new stdClass();

        foreach ($campos_view['inputs'] as $item){
            /**
             * REFCATORIZAR
             */
            $params_select = new stdClass();

            if (array_key_exists($item, $keys_selects) ){
                $params_select = $keys_selects[$item];
            }

            $params_select = $this->params_input2(params: $params_select,name: $item,place_holder: $item);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }

            $input = $this->input_template(params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $input);
            }
            $texts->$item = $input;
        }

        return $texts;
    }

    private function th(string $name): string
    {
        return "<th>$name</th>";
    }

    public function thead(array $names): array|string
    {
        $ths = $this->ths(names: $names);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra ths', data: $ths);
        }

        $thead = $this->integra_thead(ths:$ths);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra thead', data: $thead);
        }
        return $thead;
    }

    private function ths(array $names): array|string
    {

        $ths = '';
        foreach ($names as $name){
            $th = $this->th(name: $name);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integra th', data: $th);
            }
            $ths.=$th;
        }
        return $ths;

    }

    private function valida_boton_link(array $accion_permitida, int $indice, int $registro_id, array $rows): bool|array
    {
        $keys = array('adm_accion_descripcion','adm_accion_titulo','adm_seccion_descripcion','adm_accion_css',
            'adm_accion_es_status');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }

        $keys = array('adm_accion_es_status');
        $valida = $this->validacion->valida_statuses(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }

        if($indice < 0){
            return $this->error->error(mensaje: 'Error indice debe ser mayor o igual a 0',data:  $indice);
        }

        if($registro_id <= 0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data:  $registro_id);
        }
        if(!isset($rows[$indice])){
            return $this->error->error(mensaje: 'Error no existe el registro en proceso',data:  $rows);
        }
        return true;
    }

    /**
     * Genera los values para ser utilizados en los selects options
     * @param bool $con_registros si con registros muestra todos los registros
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo para asignacion de datos
     * @param array $extra_params_keys Keys para asignacion de extra params para ser utilizado en javascript
     * @param array $filtro Filtro para obtencion de datos del select
     * @param array $not_in Omite resultados para options
     * @return array
     * @version 0.49.31
     * @version 0.54.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:04
     * @fecha 2022-08-03 14:50
     * @author mgamboa
     */
    private function values_selects( bool $con_registros, stdClass $keys, modelo $modelo,
                                     array $extra_params_keys = array(), array $filtro = array(),
                                     array $not_in = array()): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $registros = array();
        if($con_registros) {
            $registros = $this->rows_select(keys: $keys, modelo: $modelo, extra_params_keys: $extra_params_keys,
                filtro:$filtro, not_in: $not_in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
            }
        }

        $values = $this->genera_values_selects(keys: $keys,registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valores',data:  $values);
        }

        return $values;
    }


}
