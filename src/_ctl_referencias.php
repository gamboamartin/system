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

    private function boton_permitido(system $controler, stdClass $params){
        $buttons = new stdClass();
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
     * Genera botones si hace falta algun parent
     * @param string $etiqueta Etiqueta de boton parent
     * @param modelo $model_parent Modelo parent
     * @return array|stdClass
     * 7.69.3
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

    private function genera_botones_parent_ir(
        system $controler, string $etiqueta, modelo $model_parent, int $registro_id): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
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

    private function integra_button_parent_ir(system $controler, string $etiqueta, modelo $model_parent,
                                              int $registro_id, string $style): array|stdClass
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la $etiqueta esta vacia', data: $etiqueta);
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
            $params_btn_children = $this->param_children(controler: $controler, key_parent_id: $key_parent_id,
                params_btn_children: $params_btn_children);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar value children', data: $params_btn_children);
            }
        }
        return $params_btn_children;
    }

    private function param_children(system $controler, string $key_parent_id, array $params_btn_children): array
    {
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

    private function params_key(system $controler, array $params): array
    {
        $params[$controler->seccion.'_id'] = $controler->registro_id;
        return $params;
    }

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

    final public function referencias_alta(system $controler){
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
     * @return string
     */
    private function style_btn(string $success, bool $tiene_rows): string
    {
        $style = 'warning';
        if($tiene_rows){
            $style = $success;
        }
        return $style;
    }

    private function style_btn_parent(modelo $model_parent, string $success): array|string
    {
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
     * @param system $controler
     * @param string $key_parent_id
     * @param array $params_btn_children
     * @return array
     */
    private function value_param_children(system $controler, string $key_parent_id, array $params_btn_children): array
    {
        $params_btn_children[$key_parent_id] = $controler->row_upd->$key_parent_id;
        return $params_btn_children;
    }

    private function value_row_children_proceso(string $key_parent_id, array $params_btn_children, stdClass $row_in_proceso): array
    {
        $params_btn_children[$key_parent_id] = $row_in_proceso->$key_parent_id;
        return $params_btn_children;
    }
}

