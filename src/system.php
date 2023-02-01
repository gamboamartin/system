<?php
namespace gamboamartin\system;
use base\controller\controlador_base;
use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use PDO;
use stdClass;
use Throwable;

/**
 * @var int $total_items_sections Corresponde al numero de acciones a mostrar en un menu lateral
 * @var array $actions_number Corresponde a la configuracion de links de un menu lateral
 *          $this->actions_number['contacto']['item'] = 4;
 *          $this->actions_number['contacto']['etiqueta'] = 'Contacto';
 *
 * @var string $menu_lateral Es un html con la forma de un menu lateral con acciones e items definidos
 *
 */
class system extends controlador_base{

    private html_controler $html;

    public stdClass|array $acciones;

    public links_menu $obj_link;
    public array $secciones = array();
    public array $keys_row_lista = array();
    public array $rows_lista = array('id','codigo','codigo_bis','descripcion','descripcion_select','alias');
    public array $columnas_lista_data_table = array();
    public array $columnas_lista_data_table_filter = array();
    public array $datatable = array();
    public array $datatables = array();
    public array $inputs_alta = array('codigo','codigo_bis','descripcion','descripcion_select','alias');
    public array $inputs_modifica = array('id','codigo','codigo_bis','descripcion','descripcion_select','alias');
    public string $forms_inputs_alta = '';
    public string $forms_inputs_modifica = '';
    public html $html_base;
    public array $btns = array();
    public string $include_menu_secciones = '';
    public int $total_items_sections = 0;
    public string $menu_lateral = '';
    public array $actions_number = array();
    public string $include_breadcrumb = '';
    public string $contenido_table = '';
    public bool $lista_get_data = false;
    public array $not_actions = array();

    public stdClass $adm_seccion_ejecucion;


    /**
     * @param html_controler $html Html base
     * @param PDO $link Conexion a la base de datos
     * @param modelo $modelo
     * @param links_menu $obj_link
     * @param array $datatables_custom_cols
     * @param array $datatables_custom_cols_omite
     * @param stdClass $datatables
     * @param array $filtro_boton_lista
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @param stdClass $paths_conf
     */
    public function __construct(html_controler $html,PDO $link, modelo $modelo, links_menu $obj_link,
                                array $datatables_custom_cols = array(), array $datatables_custom_cols_omite = array(),
                                stdClass $datatables = new stdClass(), array $filtro_boton_lista = array(),
                                string $campo_busca = 'registro_id', string $valor_busca_fault = '',
                                stdClass $paths_conf = new stdClass())
    {
        $this->msj_con_html = false;
        parent::__construct(link: $link,modelo:  $modelo,filtro_boton_lista:  $filtro_boton_lista,
            campo_busca:  $campo_busca,valor_busca_fault:  $valor_busca_fault,paths_conf:  $paths_conf);

        $this->html_base = $html->html_base;
        $init = (new init())->init_controller(controller:$this,html: $this->html_base );
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar controller', data: $init);
            print_r($error);
            die('Error');
        }

        $this->secciones = (new generales())->secciones;

        $this->obj_link = $obj_link;
        $this->html = $html;


        $keys_row_lista = (new init())->keys_row_lista(controler:$this);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar $key_row_lista', data: $keys_row_lista);
            var_dump($error);
            die('Error');
        }

        $this->include_menu_secciones = "templates/$this->tabla/$this->accion/secciones.php";


        $include_breadcrumb_rs = (new init())->include_breadcrumb(controler: $this);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar include_breadcrumb_rs', data: $include_breadcrumb_rs);
            var_dump($error);
            die('Error');
        }

        foreach ($datatables_custom_cols_omite as $campo){
            if(isset($datatables->columns[$campo])){
                unset($datatables->columns[$campo]);
            }
        }

        foreach ($datatables_custom_cols_omite as $campo){
            if(isset($datatables->filtro)){

                foreach ($datatables->filtro as $indice=>$campo_filtro){
                    if($campo_filtro === $campo){
                        unset($datatables->filtro[$indice]);
                    }
                }
            }
        }

        foreach ($datatables_custom_cols as $key=>$column){
            $datatables->columns[$key] = $column;
        }

        $data_for_datable = (new datatables())->datatable_base_init(
            datatables: $datatables,link: $this->link,rows_lista: $this->rows_lista,seccion: $this->seccion,
            not_actions: $this->not_actions);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al maquetar datos para tables ', data: $data_for_datable);
            print_r($error);
            die('Error');
        }

        $this->datatable_init(columns: $data_for_datable->columns, filtro: $data_for_datable->filtro);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar columnDefs', data: $this->datatable);
            var_dump($error);
            die('Error');
        }

        $seccion_en_ejecucion = (new adm_seccion(link:  $this->link))->seccion_by_descripcion(descripcion: $this->seccion);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al obtener seccion_en_ejecucion', data: $seccion_en_ejecucion);
            print_r($error);
            die('Error');
        }

        $keys = array('adm_namespace_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $seccion_en_ejecucion);
        if(errores::$error){
            $error = $this->errores->error(mensaje:
                'Error  adm_namespace_descripcion esta vacio en seccion_en_ejecucion', data: $valida);
            print_r($error);
            die('Error');
        }


        $this->path_vendor_views = $seccion_en_ejecucion->adm_namespace_descripcion;


    }



    /**
     * Funcion que genera los inputs y templates base para un alta
     * @version 0.17.5
     * @param bool $header Si header muestra resultado via http
     * @param bool $ws Muestra resultado via Json
     * @return array|string
     * @final rev
     */
    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  array();
        $this->inputs = new stdClass();

        $inputs = $this->html->alta(controler: $this);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar inputs', data: $inputs,
                header:  $header, ws: $ws);
        }

        $form_alta = '';
        foreach($this->inputs_alta as $input_alta){
            $form_alta .= $this->inputs->$input_alta;
        }
        $this->forms_inputs_alta = $form_alta;

        /**
         * REFACTORIZAR
         */
        $include_inputs_alta = (new generales())->path_base."templates/inputs/$this->seccion/alta.php";
        if(!file_exists($include_inputs_alta)){
            $include_inputs_alta = (new views())->ruta_templates."inputs/base/alta.php";

            $path_vendor_base = $this->path_base."vendor/$this->path_vendor_views/templates/inputs/$this->seccion/$this->accion.php";
            if(file_exists($path_vendor_base)){
                $include_inputs_alta = $path_vendor_base;
            }
        }
        $this->include_inputs_alta = $include_inputs_alta;



        return $this->forms_inputs_alta;
    }

    /**
     * @param bool $header Si header mostrara el resultado en el navegador
     * @param bool $ws Mostrara el resultado en forma de json
     * @return array|stdClass
     * @version 0.230.37
     * @final rev
     */
    public function alta_bd(bool $header, bool $ws = false): array|stdClass
    {

        $transaccion_previa = false;
        if($this->link->inTransaction()){
            $transaccion_previa = true;
        }
        if(!$transaccion_previa) {
            $this->link->beginTransaction();
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header:  $header, ws: $ws);
        }
        $seccion_retorno = $this->tabla;
        if(isset($_POST['seccion_retorno'])){
            $seccion_retorno = $_POST['seccion_retorno'];
            unset($_POST['seccion_retorno']);
        }

        $id_retorno = -1;
        if(isset($_POST['id_retorno'])){
            $id_retorno = $_POST['id_retorno'];
            unset($_POST['id_retorno']);
        }

        $valida = $this->validacion->valida_alta_bd(controler: $this);
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al validar datos', data: $valida,header:  $header,ws:  $ws);
        }

        $r_alta_bd = parent::alta_bd(header: false,ws: false);
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al dar de alta registro', data: $r_alta_bd, header:  $header,
                ws: $ws);
        }
        if(!$transaccion_previa) {
            $this->link->commit();
        }

        if($header){
            if($id_retorno === -1) {
                $id_retorno = $r_alta_bd->registro_id;
            }
            $this->retorno_base(registro_id:$id_retorno, result: $r_alta_bd, siguiente_view: $siguiente_view,
                ws:  $ws,seccion_retorno: $seccion_retorno);
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($r_alta_bd, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                $error = (new errores())->error(mensaje: 'Error al maquetar JSON' , data: $e);
                print_r($error);
            }
            exit;
        }
        $r_alta_bd->siguiente_view = $siguiente_view;
        return $r_alta_bd;
    }

    public function data_ajax(bool $header, bool $ws = false, array $not_actions = array()){

        $params = (new datatables())->params(datatable: $this->datatable);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener params', data: $params,header:  $header, ws: $ws);
        }

        if (isset($_GET['filtros'])){
            $filtros = $_GET['filtros'];

            foreach ($filtros as $index => $filtro){
                $keys = array_keys($filtro);

                if (!array_key_exists("key", $filtro)){
                    return $this->retorno_error(mensaje: 'Error no exite la clave key', data: $filtro, header: $header,
                        ws: $ws);
                }

                if (!array_key_exists("valor", $filtro)){
                    return $this->retorno_error(mensaje: 'Error no exite la clave valor', data: $filtro, header: $header,
                        ws: $ws);
                }

                if (!array_key_exists("operador", $filtro)){
                    return $this->retorno_error(mensaje: 'Error no exite la clave operador', data: $filtro, header: $header,
                        ws: $ws);
                }

                if (!array_key_exists("comparacion", $filtro)){
                    return $this->retorno_error(mensaje: 'Error no exite la clave comparacion', data: $filtro,
                        header: $header, ws: $ws);
                }

                if (trim($filtro['valor']) !== ""){
                    $params->filtro_especial[$index][$filtro['valor']]['operador'] = $filtro['operador'];
                    $params->filtro_especial[$index][$filtro['valor']]['valor'] = $filtro['key'];
                    $params->filtro_especial[$index][$filtro['valor']]['comparacion'] = $filtro['comparacion'];
                    $params->filtro_especial[$index][$filtro['valor']]['valor_es_campo'] = true;
                }
            }
        }

        $data_result = $this->modelo->get_data_lista(filtro:$params->filtro,filtro_especial: $params->filtro_especial,
            n_rows_for_page: $params->n_rows_for_page, pagina: $params->pagina);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener data result', data: $data_result,header:  $header, ws: $ws);
        }

        $salida = array(
            "draw"         => $params->draw,
            "recordsTotal"    => intval( $data_result['n_registros']),
            "recordsFiltered" => intval( $data_result['n_registros'] ),
            "data"            => $data_result['registros']);

        if($ws) {
            ob_clean();
            header('Content-Type: application/json');
            try {
                echo json_encode($salida, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $error = $this->errores->error(mensaje: 'Error al obtener registros', data: $e);
                print_r($error);
            }
            exit;
        }

        return $salida;
    }

    private function datatable_columnDefs_init(array $columns, array $columndefs): array
    {
        $index_header = array();

        foreach ($columndefs as $item){

            $keys = array_keys($item);

            $valida = $this->datatable_validate_columnDefs(keys: $keys);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al validar columnDefs', data:  $valida);
            }

            if (array_key_exists("visible",$item) && array_key_exists("targets",$item)){

                /**
                 * REFACTOTRIZAR
                 */
                $column["type"] = "text";
                $column["targets"] = ($item['targets'][0]) - 1;
                $rendered[]["index"] = $columns[$column["targets"]];
                foreach ($item["targets"] as $target){
                    $rendered[]["index"] = $columns[$target];
                    array_push($index_header, $target);
                }
                $column["rendered"] = $rendered;
                $this->datatable["columnDefs"][]= $column;
            }
        }
        return $index_header;
    }

    public function datatable_init(array $columns, array $filtro = array(), string $identificador = ".datatable",
                                   array $data = array()): array
    {

        $datatable = (new datatables())->datatable(columns: $columns, filtro: $filtro,identificador: $identificador,
            data: $data);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar datatables base', data:  $datatable);
        }

        $this->datatable = $datatable;
        $this->datatables[] = $this->datatable;

        return $this->datatable;
    }

    private function datatable_validate_columnDefs(array $keys): array|bool
    {
        $propiedades = array("type","visible","targets","rendered");

        foreach ($keys as $key){
            if (!in_array($key, $propiedades)){
                return $this->errores->error(mensaje: 'Error la propiedad no esta definida', data:  $this->datatable);
            }
        }

        return true;
    }

    public function elimina_bd(bool $header, bool $ws): array|stdClass
    {
        $transaccion_previa = false;
        if($this->link->inTransaction()){
            $transaccion_previa = true;
        }
        if(!$transaccion_previa) {
            $this->link->beginTransaction();
        }

        $r_del = parent::elimina_bd(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            if(!$transaccion_previa) {
                $this->link->rollBack();
            }
            return $this->retorno_error(mensaje: 'Error al eliminar', data: $r_del, header:  $header,
                ws: $ws);
        }
        if(!$transaccion_previa) {
            $this->link->commit();
        }

        $siguiente_view = (new actions())->init_alta_bd(siguiente_view: 'lista');
        if(errores::$error){

            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header:  $header, ws: $ws);
        }

        $header_retorno = $this->header_retorno(accion: $siguiente_view, seccion: $this->tabla, id_retorno: -1);
        if(errores::$error){

            return $this->retorno_error(mensaje: 'Error al maquetar retorno', data: $header_retorno,
                header:  $header, ws: $ws);
        }

        if($header){
            header('Location:' . $header_retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($r_del, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                $error = $this->errores->error(mensaje: 'Error al dar salida json', data: $e);
                print_r($error);
                exit;
            }
            exit;
        }
        $r_del->siguiente_view = $siguiente_view;

        return $r_del;
    }

    public function genera_inputs(array $keys_selects = array()): array|stdClass
    {
        if(!is_object($this->inputs)){
            return $this->errores->error(
                mensaje: 'Error controlador->inputs debe se run objeto',data: $this->inputs);
        }

        $inputs = $this->html->init_alta2(row_upd: $this->row_upd, modelo: $this->modelo, keys_selects:$keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
        }

        $inputs_asignados = $this->asigna_inputs(inputs: $inputs);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function get_data(bool $header, bool $ws = false, array $not_actions = array()){


        $params = (new datatables())->params(datatable: $this->datatable);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener params', data: $params,header:  $header, ws: $ws);
        }

        $data_result = $this->modelo->get_data_lista(filtro:$params->filtro,filtro_especial: $params->filtro_especial,
            n_rows_for_page: $params->n_rows_for_page, pagina: $params->pagina);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener data result', data: $data_result,header:  $header, ws: $ws);
        }

        $acciones_permitidas = (new datatables())->acciones_permitidas(
            link:$this->link,seccion:  $this->tabla, not_actions: $not_actions);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener data result', data: $acciones_permitidas,header:  $header, ws: $ws);
        }

        foreach ($data_result['registros'] as $key => $row){

            $links = array();
            foreach ($acciones_permitidas as $indice=>$adm_accion_grupo){
                /**
                 * REFCATORIZAR
                 */

                $registro_id = $row[$this->seccion.'_id'];

                $data_link = (new datatables())->data_link(adm_accion_grupo: $adm_accion_grupo,
                    data_result: $data_result, html_base: $this->html_base, key: $key,registro_id:  $registro_id);

                if(errores::$error){
                    return $this->retorno_error(mensaje: 'Error al obtener data para link', data: $data_link,
                        header:  $header, ws: $ws);
                }

                $links[$data_link->accion] = $data_link->link_con_id;
            }


            $data_result['registros'][$key] = array_merge($row,$links);
        }

        $salida = array(
            "draw"         => $params->draw,
            "recordsTotal"    => intval( $data_result['n_registros']),
            "recordsFiltered" => intval( $data_result['n_registros'] ),
            "data"            => $data_result['registros']);

        if($ws) {
            ob_clean();
            header('Content-Type: application/json');
            try {
                echo json_encode($salida, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $error = $this->errores->error(mensaje: 'Error al obtener registros', data: $e);
                print_r($error);
            }
            exit;
        }
        return $salida;
    }

    protected function header_retorno(string $accion, string $seccion, int $id_retorno = -1): array|string
    {
        $accion = trim($accion);
        $seccion = trim($seccion);

        if($accion === ''){
            return $this->errores->error(mensaje: 'Error accion esta vacia',data:  $accion);
        }
        if($seccion === ''){
            return $this->errores->error(mensaje: 'Error seccion esta vacia',data:  $seccion);
        }

        $retornos = (new init())->retornos_get(accion: $accion,seccion:  $seccion, id_retorno: $id_retorno);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener retornos data',data:  $retornos);
        }


        $header_retorno = "index.php?seccion=$retornos->next_seccion&accion=$retornos->next_accion&adm_menu_id=$retornos->adm_menu_id";
        $header_retorno .= "&session_id=$this->session_id&registro_id=$retornos->id_retorno";
        return $header_retorno;
    }

    public function inputs(array $keys_selects): array|stdClass
    {
        $keys_selects = $this->key_selects_txt(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }
        $inputs = $this->genera_inputs(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener inputs',data:  $inputs);
        }
        return $inputs;
    }

    /**
     * Debe ser operable y sobreescrito en controller de ejecucion
     * @param array $keys_selects Conjunto de keys para select
     * @return array
     * @version 0.227.37
     * @final rev
     * @por_doc true
     */
    protected function key_selects_txt(array $keys_selects): array
    {

        return $keys_selects;
    }
    
    /**
     * Genera la lista mostrable en la accion de cat_sat_tipo_persona / lista
     * @param bool $header if header se ejecuta en html
     * @param bool $ws retorna webservice
     * @return array
     */
    public function lista(bool $header, bool $ws = false): array
    {

        $this->registros = array();

        if(!$this->lista_get_data) {
            $registros_view = (new lista())->rows_view_lista(controler: $this);
            if (errores::$error) {
                return $this->retorno_error(
                    mensaje: 'Error al generar rows para lista en '.$this->seccion, data: $registros_view,
                    header: $header, ws: $ws);
            }

            $this->registros = $registros_view;
            $n_registros = count($registros_view);
            $this->n_registros = $n_registros;
        }

        $include_lista_row = (new generales())->path_base."templates/listas/$this->seccion/row.php";
        if(!file_exists($include_lista_row)){
            $include_lista_row = (new views())->ruta_templates."listas/row.php";
        }
        $this->include_lista_row = $include_lista_row;

        $include_lista_thead = (new generales())->path_base."templates/listas/$this->seccion/thead.php";
        if(!file_exists($include_lista_thead)){
            $include_lista_thead= (new views())->ruta_templates."listas/thead.php";
        }

        $this->include_lista_thead = $include_lista_thead;

        return $this->registros;
    }

    /**
     * Inicializa datos para vista modifica
     * @param bool $header Si header da salida en html
     * @param bool $ws Si ws da salida json
     * @return array|stdClass
     * @version 0.210.37
     */
    public function modifica(bool $header, bool $ws = false): array|stdClass
    {

        if($this->registro_id<=0){
            return $this->retorno_error(mensaje: 'Error registro_id debe ser mayor a 0', data: $this->registro_id,
                header:  $header, ws: $ws);
        }

        $r_modifica = parent::modifica(header: false, ws: $ws); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener template', data: $r_modifica,
                header:  $header, ws: $ws);
        }

        $inputs = $this->html->modifica(controler: $this);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar inputs', data: $inputs,
                header:  $header, ws: $ws);
        }


        if(!isset($this->row_upd)){
            $this->row_upd = new stdClass();
        }
        if(!isset($this->row_upd->status)){
            $this->row_upd->status = '';
        }

        $button_status = (new directivas(html: $this->html_base))->button_href_status(cols: 12, registro_id:$this->registro_id,
            seccion: $this->seccion,status: $this->row_upd->status);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar boton', data: $button_status,
                header:  $header, ws: $ws);
        }
        $this->inputs->status = $button_status;


        $form_modifica = '';
        foreach($this->inputs_modifica as $input_modifica){
            $form_modifica .= $this->inputs->$input_modifica;
        }
        $this->forms_inputs_modifica = $form_modifica;

        /*$include_inputs_modifica = (new generales())->path_base."templates/inputs/$this->seccion/modifica.php";
        if(!file_exists($include_inputs_modifica)){
            $include_inputs_modifica = (new views())->ruta_templates."inputs/base/modifica.php";
        }

        $this->include_inputs_modifica = $include_inputs_modifica;*/

        /**
         * REFCATROIZAR SIMILAR ALTA
         */

        $include_inputs_modifica = (new generales())->path_base."templates/inputs/$this->seccion/modifica.php";
        if(!file_exists($include_inputs_modifica)){
            $include_inputs_modifica = (new views())->ruta_templates."inputs/base/modifica.php";

            $path_vendor_base = $this->path_base."vendor/$this->path_vendor_views/templates/inputs/$this->seccion/$this->accion.php";
            if(file_exists($path_vendor_base)){
                $include_inputs_modifica = $path_vendor_base;
            }
        }
        $this->include_inputs_modifica = $include_inputs_modifica;


        return $r_modifica;
    }

    public function modifica_bd(bool $header, bool $ws): array|stdClass
    {
        $this->link->beginTransaction();
        if(isset($_POST['guarda'])){
            unset($_POST['guarda']);
        }
        $r_modifica_bd = parent::modifica_bd(false, false); // TODO: Change the autogenerated stub
        if(errores::$error){
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al modificar registro', data: $r_modifica_bd,header:  $header, ws: $ws);
        }
        $this->link->commit();
        $_SESSION[$r_modifica_bd->salida][]['mensaje'] = $r_modifica_bd->mensaje.' del id '.$this->registro_id;
        $this->header_out(result: $r_modifica_bd, header: $header,ws:  $ws);

        return $r_modifica_bd;
    }

    function reemplazar_id_link($str, $start, $end, $replacement) {

        $replacement = $start . $replacement . $end;

        $start = preg_quote($start, '/');
        $end = preg_quote($end, '/');
        $regex = "/({$start})(.*?)({$end})/";

        return preg_replace($regex,$replacement,$str);
    }

    /**
     * Ejecuta el retorno de una transaccion
     * @param int $registro_id Identificador en proceso
     * @param mixed $result Resultado
     * @param string $siguiente_view Vista de retorno
     * @param bool $ws si webservice
     * @param bool $header Si header
     * @param array $params Envia parametros por GET en retorno $_GET['PARAMETRO'] = 1
     * @param string $seccion_retorno Seccion de retorno default this->tabla
     * @return bool|array
     * @version 0.90.32
     */
    protected function retorno_base(int $registro_id, mixed $result, string $siguiente_view, bool $ws,
                                    bool $header = true, array $params = array(),
                                    string $seccion_retorno = ''):bool|array{

        if($seccion_retorno === ''){
            $seccion_retorno = $this->tabla;
        }

        $retorno = (new actions())->retorno_alta_bd(link: $this->link, registro_id: $registro_id,
            seccion: $seccion_retorno, siguiente_view: $siguiente_view, params: $params);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al dar de alta registro', data: $result, header:  $header,
                ws: $ws);
        }
        if($header) {
            header('Location:' . $retorno);
            exit;
        }
        return true;
    }

    public function row_upd(string $key): array|stdClass
    {
        $row_upd = (new row())->integra_row_upd(key: $key, modelo: $this->modelo, registro_id: $this->registro_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener row upd',data:  $row_upd);
        }

        $upd = $this->modelo->modifica_bd(registro: $row_upd, id: $this->registro_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al modificar adm_accion',data:  $upd);
        }
        return $upd;
    }

    /**
     * Intregra las acciones a los registros
     * @param string $key_id Registro id
     * @param array $rows Conjunto de registros
     * @param string $seccion Seccion a integrar acciones
     * @param array $not_actions Acciones para omitir en lista
     * @param array $params Para anexar var get
     * @return array
     * @version 0.173.34
     */
    final protected function rows_con_permisos(
        string $key_id, array $rows, string $seccion, array $not_actions = array(), array $params = array()): array
    {

        if(!isset($_SESSION)){
            return $this->errores->error(mensaje: 'Error no hay SESSION iniciada', data: array());
        }
        $keys = array('grupo_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $_SESSION);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar SESSION', data: $valida);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->errores->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }

        $acciones_permitidas = (new datatables())->acciones_permitidas(link: $this->link, seccion: $seccion,
            not_actions: $not_actions);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener acciones',data:  $acciones_permitidas);
        }
        $rows = (new out_permisos())->genera_buttons_permiso(
            acciones_permitidas: $acciones_permitidas, html: $this->html, key_id:  $key_id,rows:  $rows,
            params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar link',data:  $rows);
        }
        return $rows;
    }


}
