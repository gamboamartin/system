<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\system;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;
use Throwable;


class _ctl_base extends system{

    protected string $key_id_filter = '';
    protected string $key_id_row = '';
    public array $childrens;

    /**
     * Integra los campos view de una vista para alta y modifica Metodo para sobreescribir
     * @return array
     * @version 0.262.38
     */
    protected function campos_view(): array
    {
        return array();
    }

    protected function campos_view_base(array $init_data, stdClass $keys): array
    {
        $selects = (new \base\controller\init())->select_key_input($init_data, selects: $keys->selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar select',data:  $selects);
        }
        $keys->selects = $selects;

        $campos_view = (new \base\controller\init())->model_init_campos_template(
            campos_view: array(),keys:  $keys, link: $this->link);

        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }
        return $campos_view;
    }

    /**
     * Integra los elementos base de una view
     * @return array|$this
     * @version 0.263.38
     */
    private function base(): array|static
    {

        $campos_view = $this->campos_view();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar campos_view',data:  $campos_view);
        }

        $this->modelo->campos_view = $campos_view;



        $this->inputs = new stdClass();
        $this->inputs->select = new stdClass();


        return $this;
    }

    protected function contenido_children(stdClass $data_view, string $next_accion): array|string
    {

        $params = array();
        $params['next_seccion'] = $this->tabla;
        $params['next_accion'] = $next_accion;
        $params['id_retorno'] = $this->registro_id;

        $childrens = $this->children_data(
            namespace_model: $data_view->namespace_model, name_model_children: $data_view->name_model_children, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar inputs',data:  $childrens);
        }

        $class_css_table = array('table','table-striped');
        $id_css_table = array($data_view->name_model_children);

        $contenido_table = (new table())->table(childrens: $childrens, cols_actions: 4, data_view: $data_view,
            class_css_table: $class_css_table, id_css_table:$id_css_table );
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener tbody',data:  $contenido_table);
        }
        $this->contenido_table = $contenido_table;
        return $contenido_table;
    }

    protected function children_data(string $namespace_model, string $name_model_children, array $params): array
    {
        $inputs = $this->children_base();
        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al generar inputs',data:  $inputs);
        }

        $childrens = $this->childrens(namespace_model: $namespace_model,
            name_model_children: $name_model_children, params: $params, registro_id: $this->registro_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar links',data:  $childrens);
        }

        $this->childrens = $childrens;
        return $this->childrens;
    }

    protected function children_base(): array|stdClass
    {
        $registro = $this->init_data_children();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar registro',data:  $registro);
        }

        $inputs = $this->inputs_children(registro: $registro);
        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al generar inputs',data:  $inputs);
        }

        $retornos = $this->input_retornos();
        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener retornos',data:  $retornos);
        }

        return $inputs;
    }

    protected function childrens(string $namespace_model, string $name_model_children, array $params, int $registro_id): array
    {
        $this->key_id_filter = $this->tabla.'.id';
        $filtro = array();
        $filtro[$this->key_id_filter] = $registro_id;

        $model_children = $this->modelo->genera_modelo(modelo: $name_model_children,namespace_model: $namespace_model);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar modelo',data:  $model_children);
        }

        $r_children = $model_children->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener r_children',data:  $r_children);
        }
        $childrens = $r_children->registros;

        $key_id = $name_model_children.'_id';
        $childrens = $this->rows_con_permisos(key_id:  $key_id, rows:  $childrens,seccion: $name_model_children, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar link',data:  $childrens);
        }

        return $childrens;
    }

    protected function base_upd(array $keys_selects, array $not_actions, array $params, array $params_ajustados): array|stdClass
    {

        if(count($params) === 0){
            $params = (new \gamboamartin\system\_ctl_base\init())->params(controler: $this,params:  $params);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al asignar params', data: $params);
            }
        }

        if(count($params_ajustados) === 0) {
            $params_ajustados['elimina_bd']['next_seccion'] = $this->tabla;
            $params_ajustados['elimina_bd']['next_accion'] = 'lista';
        }

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener inputs',data:  $inputs);
        }

        $this->buttons = array();
        $buttons = (new out_permisos())->buttons_view(controler:$this, not_actions: $not_actions, params: $params, params_ajustados: $params_ajustados);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar botones',data:  $buttons);
        }

        $data = new stdClass();
        $data->buttons = $buttons;
        $data->inputs = $inputs;
        $this->buttons = $buttons;
        return $data;
    }

    private function data_retorno(): array|stdClass
    {
        $seccion_retorno = $this->seccion_retorno();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener seccion retorno', data: $seccion_retorno);
        }

        $id_retorno = $this->id_retorno();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener id retorno', data: $id_retorno);
        }
        $data = new stdClass();
        $data->seccion_retorno = $seccion_retorno;
        $data->id_retorno = $id_retorno;

        return $data;
    }

    protected function data_retorno_base(): array|stdClass
    {
        $siguiente_view = (new actions())->init_alta_bd();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view);
        }

        $data_retorno = $this->data_retorno();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener datos de retorno', data: $data_retorno);
        }
        $data_retorno->siguiente_view = $siguiente_view;
        return $data_retorno;
    }

    /**
     * Inicializa loe elementos para un alta
     * @return array|stdClass|string
     * @version 0.269.38
     */
    protected function init_alta(): array|stdClass|string
    {

        $r_template = parent::alta(header:false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener template',data:  $r_template);
        }

        $base = $this->base();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera base',data:  $base);
        }

        return $r_template;
    }

    private function id_retorno(){
        $id_retorno = -1;
        if(isset($_POST['id_retorno'])){
            $id_retorno = $_POST['id_retorno'];
            unset($_POST['id_retorno']);
        }
        return $id_retorno;
    }

    /**
     * Inicializa los elementos de datos de un children para una view
     * @return array|stdClass
     * @version 0.264.38
     */
    protected function init_data_children(): array|stdClass
    {
        if($this->registro_id<=0){
            return $this->errores->error(mensaje: 'Error this->registro_id debe ser mayor a 0',
                data:  $this->registro_id);
        }

        $registro = $this->modelo->registro(registro_id: $this->registro_id, retorno_obj: true);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener registro',data:  $registro);
        }

        $this->key_id_row = $this->tabla.'_id';

        return $registro;
    }

    /**
     * Inicializa upd base view
     * @return array|stdClass|string
     * @version 0.267.38
     */
    protected function init_modifica(): array|stdClass|string
    {
        if($this->registro_id<=0){
            return $this->errores->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $this->registro_id);
        }

        $r_template = parent::modifica(header: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener template',data:  $r_template);
        }

        $base = $this->base();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera base',data:  $base);
        }
        return $r_template;
    }

    /**
     * Debe star sobreescrito en el controlador integrando todos los selects necesarios
     * @param stdClass $registro
     * @return stdClass|array
     * @version 0.265.38
     */
    protected function inputs_children(stdClass $registro): stdClass|array
    {

        return new stdClass();
    }

    /**
     * Genera los input para retornos despues de transaccion
     * @return array|stdClass
     * @version 0.259.38
     */
    private function input_retornos(): array|stdClass
    {
        $retornos = (new html_controler(html: $this->html_base))->retornos(registro_id: $this->registro_id,tabla:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener retornos',data:  $retornos);
        }

        $hidden_input_id = (new html_controler(html: $this->html_base))->hidden(name: $this->key_id_row, value: $this->registro_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener hidden_input_id',data:  $hidden_input_id);
        }

        $this->inputs->hidden_row_id = $hidden_input_id;
        $this->inputs->hidden_seccion_retorno = $retornos->hidden_seccion_retorno;
        $this->inputs->hidden_id_retorno = $retornos->hidden_id_retorno;
        return $this->inputs;
    }

    /**
     * Integra un elemento para select de template
     * @param string $key Campo a integrar
     * @param string $key_val Key para asignar value
     * @param array $keys_selects Conjunto de parametros
     * @param string|bool|array|null $value Valor
     * @return array
     * @version 0.275.38
     */
    private function integra_key_to_select(string $key, string $key_val, array $keys_selects, string|bool|array|null $value ): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $key_val = trim($key_val);
        if($key_val === ''){
            return $this->errores->error(mensaje: 'Error key_val esta vacio',data:  $key_val);
        }
        if(!isset($keys_selects[$key])){
            $keys_selects[$key] = new stdClass();
        }
        $keys_selects[$key]->$key_val = $value;
        return $keys_selects;
    }

    /**
     * Integra los parametros de un key para select
     * @param int $cols N cols css
     * @param bool $con_registros integra rows en opciones si es true
     * @param array $filtro Filtro para result
     * @param string $key Name input
     * @param array $keys_selects keys precargados
     * @param int|null $id_selected Identificador para selected
     * @param string $label Etiqueta a mostrar
     * @return array
     */
    protected function key_select(int $cols, bool $con_registros, array $filtro,string $key, array $keys_selects,
                                  int|null $id_selected, string $label): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $valida = (new validacion())->valida_cols_css(cols: $cols);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar cols',data:  $valida);
        }

        $label = $this->label_init(key: $key,label:  $label);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar label',data:  $label);
        }


        $keys_selects[$key] = new stdClass();

        $keys_selects = $this->integra_key_to_select(key: $key,key_val:  'cols',keys_selects:  $keys_selects,value:  $cols);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar keys',data:  $keys_selects);
        }

        $keys_selects = $this->integra_key_to_select(key: $key,key_val:  'con_registros',keys_selects:  $keys_selects,value:  $con_registros);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar keys',data:  $keys_selects);
        }

        $keys_selects = $this->integra_key_to_select(key: $key,key_val:  'label',keys_selects:  $keys_selects,value:  $label);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar keys',data:  $keys_selects);
        }

        $keys_selects = $this->integra_key_to_select(key: $key,key_val:  'id_selected',keys_selects:  $keys_selects,value:  $id_selected);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar keys',data:  $keys_selects);
        }

        $keys_selects = $this->integra_key_to_select(key: $key,key_val:  'filtro',keys_selects:  $keys_selects,value:  $filtro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar keys',data:  $keys_selects);
        }


        return $keys_selects;
    }

    /**
     * Genera un label para input
     * @param string $key Key del campo
     * @return string|array
     * @version 0.270.38
     */
    private function label(string $key): string |array
    {
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $label = trim($key);
        $label = str_replace('_', ' ', $label);
        return ucwords($label);
    }

    /**
     * Inicializa un elemento label
     * @param string $key Key de input
     * @param string $label Etiqueta de input
     * @return array|string
     * @version 0.272.38
     */
    private function label_init(string $key, string $label): array|string
    {
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio',data:  $key);
        }
        $label = trim($label);
        if($label === ''){
            $label = $this->label(key: $key);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al generar label',data:  $label);
            }
        }
        return $label;
    }

    protected function retorno(
        stdClass $data_retorno, bool $header, int $registro_id, mixed $result, bool $ws){
        if($header){
            if($data_retorno->id_retorno === -1) {
                $data_retorno->id_retorno = $registro_id;
            }

            $this->retorno_base(registro_id:$data_retorno->id_retorno, result: $result, siguiente_view: $data_retorno->siguiente_view,
                ws:  $ws,seccion_retorno: $data_retorno->seccion_retorno);

        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                $error = $this->errores->error(mensaje: 'Error al dar salida json', data: $e);
                print_r($error);
            }
            exit;
        }
        return $result;
    }

    /**
     * Obtiene la seccion de retorno
     * @return string
     * @version 0.282.38
     */
    private function seccion_retorno():string{
        $seccion_retorno = $this->tabla;
        if(isset($_POST['seccion_retorno'])){
            $seccion_retorno = $_POST['seccion_retorno'];
            unset($_POST['seccion_retorno']);
        }
        return $seccion_retorno;
    }

}
