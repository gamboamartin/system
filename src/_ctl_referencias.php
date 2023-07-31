<?php
namespace gamboamartin\system;

use base\controller\controler;
use base\orm\modelo;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class _ctl_referencias{

    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Asignan valores default para precargar selects
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key del parent 0 al input select
     * @return array
     */
    private function asigna_valor_default(system $controler, string $key_parent_id): array
    {
        $controler->valores_asignados_default[$key_parent_id] = $_GET[$key_parent_id];
        return $controler->valores_asignados_default;
    }

    /**
     * @param system $controler
     * @param modelo $model_parent
     * @return array
     */
    private function asigna_valores_default(system $controler, modelo $model_parent): array
    {
        $valores = array();
        $key_parent_id = $this->key_parent_id(model_parent: $model_parent);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar key parent', data:  $key_parent_id);
        }

        if(isset($_GET[$key_parent_id])){
            $valores = $this->asigna_valor_default(controler: $controler, key_parent_id: $key_parent_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar valor', data:  $valores);
            }
        }
        return $valores;
    }

    /**
     * Genera un boton para is al catalogo children
     * @param array $child
     * @param system $controler
     * @param string $entidad
     * @param array $params
     * @return array|string
     */
    private function boton_children(array $child, system $controler, string $entidad, array $params): array|string
    {
        $params = $this->params_key(controler: $controler, params: $params);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar param',data:  $params);
        }

        $button = $controler->html_base->button_href(accion: 'alta',etiqueta: $child['title'],registro_id: -1,
            seccion: $entidad,style:  'success', params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar button',data:  $button);
        }
        return $button;
    }

    /**
     * Integra los botones permitidos en una vista
     * @param system $controler
     * @param stdClass $params
     * @return array|stdClass
     */
    private function boton_permitido(system $controler, stdClass $params): array|stdClass
    {
        $buttons = new stdClass();

        $buttons = $this->buttons_alta(buttons: $buttons,controler:  $controler,params:  $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar botones', data: $buttons);
        }

        $buttons = $this->buttons_modifica(buttons: $buttons,controler:  $controler,params:  $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar botones', data: $buttons);
        }

        return $buttons;
    }

    /**
     * Genera los botones para ir a los catalogos children
     * @param system $controler
     * @param array $params
     * @return array
     */
    private function botones_children(system $controler, array $params): array
    {
        foreach ($controler->childrens_data as $entidad=>$child){
            $button = $this->boton_children(child: $child, controler: $controler, entidad: $entidad, params: $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar button',data:  $button);
            }
            $controler->buttons_childrens_alta[] = $button;
        }
        return $controler->buttons_childrens_alta;
    }

    /**
     * Integra los botones de alta view base
     * @param stdClass $buttons Botones previos cargados
     * @param system $controler Controlador en ejecucion
     * @param stdClass $params parametros get
     * @return array|stdClass
     * @version 8.57.0
     */
    private function buttons_alta(stdClass $buttons, system $controler, stdClass $params): array|stdClass
    {
        if(!isset($params->model_parent)){
            return $this->error->error(mensaje: 'Error $params->model_parent no existe', data:  $params);
        }
        if(!is_object($params->model_parent)){
            return $this->error->error(mensaje: 'Error $params->model_parent debe ser un objeto', data:  $params);
        }
        if(!isset($params->model_parent->tabla)){
            return $this->error->error(mensaje: 'Error $params->model_parent->tabla no existe', data:  $params);
        }

        $tengo_permiso = (new adm_accion(link: $controler->link))->permiso(accion: 'alta',
            seccion:  $params->model_parent->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso boton', data:  $tengo_permiso);
        }

        if($tengo_permiso) {
            $buttons = $this->genera_botones_parent(
                controler: $controler, etiqueta: $params->etiqueta, model_parent: $params->model_parent);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar botones', data: $buttons);
            }
        }
        return $buttons;
    }

    /**
     * Genera los botones de ir en vistas de modificacion
     * @param stdClass $buttons Botones previamente cargados
     * @param system $controler Controlador en ejecucion
     * @param stdClass $params Parametros GET
     * @return array|stdClass
     */
    private function buttons_modifica(stdClass $buttons, system $controler, stdClass $params): array|stdClass
    {
        $tengo_permiso = (new adm_accion(link: $controler->link))->permiso(accion: 'modifica',
            seccion:  $params->model_parent->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso boton', data:  $tengo_permiso);
        }

        if($tengo_permiso) {

            $key_id = $params->model_parent->key_id;

            if(isset($controler->row_upd->$key_id)) {
                $buttons = $this->genera_botones_parent_ir(
                    controler: $controler, etiqueta: $params->etiqueta_ir, model_parent: $params->model_parent,
                    registro_id: $controler->row_upd->$key_id);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar botones', data: $buttons);
                }
            }
        }
        return $buttons;
    }

    /**
     * Genera botones si hace falta algun parent
     * @param system $controler Controlador en ejecucion
     * @param string $etiqueta Etiqueta de boton parent
     * @param modelo $model_parent Modelo parent
     * @return array|stdClass
     * @version 8.35.0
     */
    private function genera_botones_parent(system $controler, string $etiqueta, modelo $model_parent): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
        }

        $style = $this->style_btn_parent(model_parent: $model_parent, success: 'success');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera style '.$model_parent->tabla, data:  $style);
        }

        $buttons = $this->integra_button_parent(controler: $controler, etiqueta: $etiqueta,
            model_parent: $model_parent, style: $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar botones', data:  $buttons);
        }
        return $buttons;
    }

    /**
     * Genera lso botones de ir a la entidad relacionada
     * @param system $controler Controlador en ejecucion
     * @param string $etiqueta Etiqueta de boton
     * @param modelo $model_parent Modelo a ir
     * @param int $registro_id registro en proceso
     * @return array|stdClass
     * @version 8.48.0
     */
    private function genera_botones_parent_ir(
        system $controler, string $etiqueta, modelo $model_parent, int $registro_id): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
        }
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id);
        }

        $style = $this->style_btn_parent(model_parent: $model_parent, success: 'info');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera style '.$model_parent->tabla, data:  $style);
        }

        $buttons = $this->integra_button_parent_ir(controler: $controler, etiqueta: $etiqueta,
            model_parent: $model_parent, registro_id: $registro_id, style: $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar botones', data:  $buttons);
        }
        return $buttons;
    }

    /**
     * Genera los keys para buttons parent en alta
     * @param system $controler Controlador en ejecucion
     * @param array|modelo $parent Modelos a integrar
     * @return array|stdClass
     * @version 7.104.3
     */
    private function genera_keys_parents(system $controler, array|modelo $parent): array|stdClass
    {
        $model_parent = $this->model_parent(parent: $parent);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar model parent', data: $model_parent);
        }

        $key_parent_id = $this->key_parent_id(model_parent: $model_parent);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar key parent', data: $key_parent_id);
        }
        if(isset($_GET[$key_parent_id])){

            $keys_selects = $this->integra_key_parent_get(controler: $controler, key_parent_id: $key_parent_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $keys_selects);
            }

        }
        return $controler->keys_selects;
    }

    /**
     * Integra los inputs de tipo parent
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key a integrar para select
     * @return array|stdClass
     * @version 7.90.3
     */
    private function input_parent(system $controler, string $key_parent_id): array|stdClass
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error(mensaje: 'Error key_parent_id esta vacio', data: $key_parent_id);
        }
        if(!isset($controler->keys_selects[$key_parent_id])){
            $controler->keys_selects[$key_parent_id] = new stdClass();
        }
        if(!isset($_GET[$key_parent_id])){
            $_GET[$key_parent_id] = '';
        }
        $controler->keys_selects[$key_parent_id]->con_registros = true;
        $controler->keys_selects[$key_parent_id]->value = $_GET[$key_parent_id];

        return $controler->keys_selects;
    }

    /**
     * Integra los botones de parents para alta
     * @param system $controler Controlador en proceso
     * @return array|stdClass
     * @version 8.11.0
     */
    private function inputs_parent(system $controler): array|stdClass
    {
        foreach ($controler->parents_verifica as $parent){

            $keys_selects = $this->genera_keys_parents(controler: $controler,parent: $parent);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $keys_selects);
            }
        }
        return $controler->keys_selects;
    }

    /**
     * Integra un boton para ejecucion parent
     * @param system $controler
     * @param string $etiqueta Etiqueta de boton
     * @param modelo $model_parent Modelo parent
     * @param string $style Stilo css
     * @return array|stdClass
     * @version 7.67.3
     */
    private function integra_button_parent(system $controler, string $etiqueta, modelo $model_parent, string $style): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
        }

        $button = $controler->html->button_href(accion: 'alta', etiqueta: $etiqueta,
            registro_id:  -1, seccion: $model_parent->tabla,style: $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton', data:  $button);
        }
        $object_button = $model_parent->tabla;
        $controler->buttons_parents_alta->$object_button = $button;
        return $controler->buttons_parents_alta;
    }

    /**
     * Integra los botones parent de controler
     * @param system $controler Controlador en ejecucion
     * @param string $etiqueta Etiqueta de button
     * @param modelo $model_parent Modelo
     * @param int $registro_id Registro en proceso
     * @param string $style Estilo de button
     * @return array|stdClass
     * @version 8.40.0
     */
    private function integra_button_parent_ir(system $controler, string $etiqueta, modelo $model_parent,
                                              int $registro_id, string $style): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
        }
        $valida = $controler->html_base->valida_input(accion: 'modifica',etiqueta:  $etiqueta,
            seccion: $model_parent->tabla,style:  $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $button = $controler->html->button_href(accion: 'modifica', etiqueta: $etiqueta,
            registro_id:  $registro_id, seccion: $model_parent->tabla,style: $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton', data:  $button);
        }


        $object_button = $model_parent->tabla;
        $controler->buttons_parents_ir->$object_button = $button;
        return $controler->buttons_parents_ir;
    }

    /**
     * Integra los botones para una view con childrens
     * @param system $controler
     * @return array
     */
    final public function integra_buttons_children(system $controler): array
    {
        $params_btn_children = $this->params_btn(controler: $controler);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar value children', data: $params_btn_children);
        }

        $botones = $this->botones_children(controler: $controler, params: $params_btn_children);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar buttons',data:  $botones);

        }
        return $botones;
    }

    /**
     * Integra el key para un parent button para alta
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key de valor
     * @return array|stdClass
     * @version 7.95.3
     */
    private function integra_key_parent(system $controler, string $key_parent_id): array|stdClass
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error(mensaje: 'Error key_parent_id esta vacio', data: $key_parent_id);
        }
        if(isset($controler->keys_selects[$key_parent_id])){
            $keys_selects = $this->input_parent(controler: $controler, key_parent_id: $key_parent_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $keys_selects);
            }
        }
        return $controler->keys_selects;
    }

    /**
     * Integra un elemento para buttons parent para alta
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key del campo a obtener info
     * @return array|stdClass
     * @version 7.102.3
     */
    private function integra_key_parent_get(system $controler, string $key_parent_id): array|stdClass
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error(mensaje: 'Error key_parent_id esta vacio', data: $key_parent_id);
        }

        if(isset($_GET[$key_parent_id])){
            $keys_selects = $this->integra_key_parent(controler: $controler,key_parent_id: $key_parent_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $keys_selects);
            }
        }
        return $controler->keys_selects;
    }

    /**
     * Integra los parametros de un boton de tipo hijo
     * @param system $controler Controlador en ejecucion
     * @param modelo $model_parent Modelo parent a integrar
     * @param array $params_btn_children parametros previamente cargados
     * @return array
     * @version 8.84.1
     */
    private function integra_params_btn(system $controler, modelo $model_parent, array $params_btn_children): array
    {
        $key_parent_id = $this->key_parent_id(model_parent: $model_parent);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key parent',data:  $key_parent_id);

        }

        $params_btn_children = $this->param_btn_children(controler: $controler, key_parent_id: $key_parent_id,
            params_btn_children: $params_btn_children);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar value children', data: $params_btn_children);
        }
        return $params_btn_children;
    }

    /**
     * Genera el name id de la tabla a relacionar
     * @param modelo $model_parent Modelo parent
     * @return string|array
     * @version 7.81.3
     */
    private function key_parent_id(modelo $model_parent): string|array
    {
        $tabla = trim($model_parent->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error la tabla del modelo esta vacia',data:  $tabla);
        }
        return $tabla.'_id';
    }

    /**
     * Verifica la estructura del parent y retorna el modelo
     * @param array|modelo $parent Dato a verificar
     * @return modelo|array
     * @version 7.79.3
     */
    private function model_parent(array|modelo $parent): modelo|array
    {
        $model_parent = $parent;
        if(is_array($parent) && isset($parent['model_parent'])){

            if(!is_object($parent['model_parent'])){
                return $this->error->error(mensaje: 'Error el model_parent no es de tipo modelo',data:  $parent);
            }

            $model_parent = $parent['model_parent'];
        }

        if(!is_object($model_parent)){
            return $this->error->error(mensaje: 'Error el resultado no es de tipo modelo',data:  $model_parent);
        }

        return $model_parent;
    }

    /**
     * Integra los parametros de un boton de tipo dependencia de un modelo
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key del controlador en ejecucion para envio de registro_id
     * @param array $params_btn_children Parametros previamente cargados
     * @return array
     * @version 8.73.1
     */
    private function param_btn_children(system $controler, string $key_parent_id, array $params_btn_children): array
    {
        if(isset($this->row_upd->$key_parent_id)){

            $params_btn_children = $this->value_param_children(controler: $controler, key_parent_id: $key_parent_id,
                params_btn_children:  $params_btn_children);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar value',data:  $params_btn_children);
            }
        }
        else {
            if($controler->registro_id <= 0){
                return $this->error->error(mensaje: 'Error controler->registro_id debe ser mayor a 0',
                    data:  $controler->registro_id);
            }
            $params_btn_children = $this->param_children(controler: $controler, key_parent_id: $key_parent_id,
                params_btn_children: $params_btn_children);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar value children', data: $params_btn_children);
            }
        }
        return $params_btn_children;
    }

    /**
     * Integra los parametros de un boton
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key para registro id de retorno
     * @param array $params_btn_children Parametros previamente cargados
     * @return array
     * @version 8.72.1
     */
    private function param_children(system $controler, string $key_parent_id, array $params_btn_children): array
    {
        if($controler->registro_id <= 0){
            return $this->error->error(mensaje: 'Error controler->registro_id debe ser mayor a 0',
                data:  $controler->registro_id);
        }
        $row_in_proceso = $controler->modelo->registro(registro_id: $controler->registro_id,retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro en proceso',data:  $row_in_proceso);
        }
        if(isset($row_in_proceso->$key_parent_id)){
            $params_btn_children = $this->value_row_children_proceso(key_parent_id: $key_parent_id,
                params_btn_children:  $params_btn_children,row_in_proceso:  $row_in_proceso);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar value',data:  $params_btn_children);
            }
        }
        return $params_btn_children;
    }

    /**
     * Parametros de botones para envio get
     * @param system $controler Controlador en ejecucion
     * @return array
     */
    private function params_btn(system $controler): array
    {
        $params_btn_children = array();
        foreach ($controler->parents_verifica as $parent){

            $model_parent = $parent;
            if(is_array($parent) && isset($parent['model_parent'])){
                $model_parent = $parent['model_parent'];
            }

            $params_btn_children = $this->integra_params_btn(controler: $controler, model_parent: $model_parent,
                params_btn_children:  $params_btn_children);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar value children', data: $params_btn_children);
            }

        }
        return $params_btn_children;
    }

    /**
     * Obtiene los parametros para ejecucion de referencias
     * @param modelo|array $parent Data de integracion
     * @return stdClass|array
     * @version 7.92.3
     */
    private function params_btn_parent(modelo|array $parent): stdClass|array
    {
        if(is_array($parent) && isset($parent['model_parent'])) {
            $keys = array('model_parent','etiqueta');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro: $parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar parent', data: $valida);
            }

            $model_parent = $parent['model_parent'];
            $etiqueta = $parent['etiqueta'];
            $etiqueta_ir = ' Ir a '.$model_parent->etiqueta;
            if(isset($parent['etiqueta_ir'])) {
                $etiqueta_ir = $parent['etiqueta_ir'];
            }
        }
        else{
            $keys = array('etiqueta');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro: $parent);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar parent', data: $valida);
            }

            $model_parent = $parent;
            $etiqueta = 'Alta '.$model_parent->etiqueta;
            $etiqueta_ir = ' Ir a '.$model_parent->etiqueta;
        }

        $data = new stdClass();
        $data->model_parent = $model_parent;
        $data->etiqueta = $etiqueta;
        $data->etiqueta_ir = $etiqueta_ir;
        return $data;
    }

    /**
     * Integra un parametro para envio get
     * @param system $controler Controlador en ejecucion
     * @param array $params Parametros previamente cargados
     * @return array
     */
    private function params_key(system $controler, array $params): array
    {
        $params[$controler->seccion.'_id'] = $controler->registro_id;
        return $params;
    }

    /**
     * @param system $controler
     * @return array|bool
     */
    private function parents_alta(system $controler): array|bool
    {
        /**
         * @var modelo $model_parent;
         */
        if($controler->verifica_parents_alta){

            foreach ($controler->parents_verifica as $parent) {

                $params = $this->params_btn_parent(parent: $parent);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar params', data: $params);
                }

                $buttons = $this->boton_permitido(controler: $controler,params: $params);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar botones', data: $buttons);
                }

                $valores = $this->asigna_valores_default(controler: $controler, model_parent: $params->model_parent);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al asignar valor', data: $valores);
                }
            }
        }
        return $controler->verifica_parents_alta;
    }

    /**
     * @param system $controler
     * @return array|stdClass
     */
    final public function referencias_alta(system $controler): array|stdClass
    {
        $keys_selects = $this->inputs_parent(controler: $controler);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $keys_selects);
        }


        $valores = $this->parents_alta(controler: $controler);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar valores parent', data: $valores);
        }
        return $keys_selects;
    }

    /**
     * Obtiene el style de un boton
     * @param string $success Success default
     * @param bool $tiene_rows Si tiene registros o no
     * @return string|array
     * @version 8.26.0
     */
    private function style_btn(string $success, bool $tiene_rows): string|array
    {
        $success = trim($success);
        if($success === ''){
            return $this->error->error(mensaje: 'Error success no puede venir vacio', data: $success);
        }
        $style = 'warning';
        if($tiene_rows){
            $style = $success;
        }
        return $style;
    }

    /**
     * Genera el estilo de un boton parent para alta y modifica
     * @param modelo $model_parent Modelo
     * @param string $success Estilo default
     * @return array|string
     * @version 8.34.0
     *
     */
    private function style_btn_parent(modelo $model_parent, string $success): array|string
    {
        $success = trim($success);
        if($success === ''){
            return $this->error->error(mensaje: 'Error success no puede venir vacio', data: $success);
        }

        $tiene_rows = $model_parent->tiene_registros();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $model_parent '.$model_parent->tabla, data:  $tiene_rows);
        }

        $style = $this->style_btn(success: $success, tiene_rows: $tiene_rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera style '.$model_parent->tabla, data:  $style);
        }
        return $style;
    }

    /**
     * Integra un value a un input de tipo children
     * @param system $controler Controlador en ejecucion
     * @param string $key_parent_id Key id parent
     * @param array $params_btn_children Parametros de botones
     * @return array
     * @version 8.56.0
     */
    private function value_param_children(system $controler, string $key_parent_id, array $params_btn_children): array
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error(mensaje: 'Error key_parent_id no puede venir vacio', data: $key_parent_id);
        }
        if(!isset($controler->row_upd->$key_parent_id)){
           return $this->error->error(mensaje: 'Error no existe atributo en row upd '.$key_parent_id,
               data:  $controler);
        }

        $params_btn_children[$key_parent_id] = $controler->row_upd->$key_parent_id;
        return $params_btn_children;
    }

    /**
     * Integra el valor de un parametro de boton
     * @param string $key_parent_id Key de integracion id para envio de accion
     * @param array $params_btn_children Parametros previamente cargados
     * @param stdClass $row_in_proceso Registro en proceso
     * @return array
     * @version 11.14.0
     *
     */
    private function value_row_children_proceso(string $key_parent_id, array $params_btn_children,
                                                stdClass $row_in_proceso): array
    {
        $key_parent_id = trim($key_parent_id);
        if($key_parent_id === ''){
            return $this->error->error(mensaje: 'Error key_parent_id esta vacio', data:  $key_parent_id);
        }

        $keys = array($key_parent_id);
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row_in_proceso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_in_proceso', data:  $valida);
        }

        $params_btn_children[$key_parent_id] = $row_in_proceso->$key_parent_id;
        return $params_btn_children;
    }
}

