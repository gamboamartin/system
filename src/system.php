<?php
namespace gamboamartin\system;
use base\controller\controlador_base;
use base\orm\modelo;
use config\generales;
use config\views;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use JsonException;
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

    /**
     * @param html_controler $html Html base
     * @param PDO $link Conexion a la base de datos
     * @param modelo $modelo
     * @param links_menu $obj_link
     * @param stdClass $datatables
     * @param array $filtro_boton_lista
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @param stdClass $paths_conf
     */
    public function __construct(html_controler $html,PDO $link, modelo $modelo, links_menu $obj_link,
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
        $include_breadcrumb = (new views())->ruta_templates."head/$this->accion/title.php";
        if(file_exists("templates/head/$this->tabla/$this->accion/title.php")){
            $include_breadcrumb = "templates/head/$this->tabla/$this->accion/title.php";
        }
        $this->include_breadcrumb = $include_breadcrumb;
        if(!file_exists($include_breadcrumb)){
            $this->include_breadcrumb = '';
        }


        $data_for_datable = (new datatables())->datatable_base_init(
            datatables: $datatables,link: $this->link,rows_lista: $this->rows_lista,seccion: $this->seccion);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al maquetar datos para tables ', data: $data_for_datable);
            var_dump($error);
            die('Error');
        }

        $this->datatable_init(columns: $data_for_datable->columns, filtro: $data_for_datable->filtro);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar columnDefs', data: $this->datatable);
            var_dump($error);
            die('Error');
        }
    }



    /**
     * Funcion que genera los inputs y templates base para un alta
     * @version 0.17.5
     * @param bool $header Si header muestra resultado via http
     * @param bool $ws Muestra resultado via Json
     * @return array|string
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

        $include_inputs_alta = (new generales())->path_base."templates/inputs/$this->seccion/alta.php";
        if(!file_exists($include_inputs_alta)){
            $include_inputs_alta = (new views())->ruta_templates."inputs/base/alta.php";
        }

        $this->include_inputs_alta = $include_inputs_alta;



        return $this->forms_inputs_alta;
    }

    /**
     * @param bool $header Si header mostrara el resultado en el navegador
     * @param bool $ws Mostrara el resultado en forma de json
     * @return array|stdClass
     * @throws JsonException
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
            echo json_encode($r_alta_bd, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_alta_bd->siguiente_view = $siguiente_view;
        return $r_alta_bd;
    }
    
    private function columnas_lista(): array
    {
        $columnas = array();
        foreach ($this->keys_row_lista as $key_row_lista){
            $valida = $this->valida_key_rows_lista(key_row_lista: $key_row_lista);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
            }

            $columnas[] = $key_row_lista->campo;
        }
        return $columnas;
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
        $r_del = parent::elimina_bd(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al eliminar', data: $r_del, header:  $header,
                ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd(siguiente_view: 'lista');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header:  $header, ws: $ws);
        }

        if($header){
            $this->retorno_base(registro_id:-1, result: $r_del,
                siguiente_view: $siguiente_view,ws:  $ws);
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
    
    private function filtros_especiales_datatable(array $filtro_especial, string $str): array
    {
        foreach ($this->datatable["filtro"] as $indice=>$column) {

            $filtro_especial = (new datatables())->filtro_especial_datatable(
                filtro_especial: $filtro_especial,indice:  $indice, column: $column, str: $str);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
            }
        }
        return $filtro_especial;
    }

    /**
     * Genera el conjunto de botones
     * @param array $acciones_permitidas Acciones permitidas
     * @param string $key_id Key de row
     * @param array $rows conjunto de registros
     * @return array
     * @version 0.172.34
     */
    private function genera_buttons_permiso(array $acciones_permitidas, string $key_id, array $rows): array
    {
        foreach ($rows as $indice=>$row){
            $rows = $this->integra_acciones_permitidas(acciones_permitidas: $acciones_permitidas,
                indice:  $indice,key_id:  $key_id, row: $row,rows:  $rows);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }

    private function genera_filtro_especial_datatable(): array
    {
        $filtro_especial = array();
        if(isset($_GET['search']) && $_GET['search']['value'] !== '' ) {
            $str = $_GET['search']['value'];
            $filtro_especial = $this->filtros_especiales_datatable(filtro_especial: $filtro_especial,str:  $str);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
            }
        }
        return $filtro_especial;
    }
    
    public function genera_inputs(array $keys_selects = array()): array|stdClass
    {
        $inputs = $this->html->init_alta2(row_upd: $this->row_upd, modelo: $this->modelo, link: $this->link,
            keys_selects:$keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
        }

        $inputs_asignados = $this->asigna_inputs(inputs: $inputs);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function get_data(bool $header, bool $ws = false){

        /**
         * REFCATORIZAR
         */
        $draw = mt_rand(1,999);
        if (isset ( $_GET['draw'] )) {
            $draw = $_GET['draw'];
        }
        $n_rows_for_page = 10;
        if (isset ( $_GET['length'] )) {
            $n_rows_for_page = $_GET['length'];
        }

        $pagina = 1;
        if (isset ( $_GET['start'] )) {
            $pagina = (int)($_GET['start'] /  $n_rows_for_page) + 1;
        }
        if($pagina <= 0){
            $pagina = 1;
        }

        $filtro  = array();
        if (isset($_GET['data'])){
            $filtro = $_GET['data'];
        }

        $filtro_especial = $this->genera_filtro_especial_datatable();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial,header:  $header, ws: $ws);
        }

        $data_result = $this->modelo->get_data_lista(filtro:$filtro,filtro_especial: $filtro_especial,
            n_rows_for_page: $n_rows_for_page, pagina: $pagina);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener data result', data: $data_result,header:  $header, ws: $ws);
        }

        $acciones_permitidas = (new datatables())->acciones_permitidas(link:$this->link,seccion:  $this->tabla);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener data result', data: $acciones_permitidas,header:  $header, ws: $ws);
        }

        foreach ($data_result['registros'] as $key => $row){

            $links = array();
            foreach ($acciones_permitidas as $indice=>$adm_accion_grupo){
                /**
                 * REFCATORIZAR
                 */
                $accion = $adm_accion_grupo['adm_accion_descripcion'];
                $registro_id = $row[$this->seccion.'_id'];


                $link_con_id = $this->obj_link->link_con_id(accion:$accion, link: $this->link,
                    registro_id: $registro_id,seccion:  $this->seccion);
                if(errores::$error){
                    return $this->retorno_error(mensaje: 'Error al asignar link', data: $link_con_id,header:  $header, ws: $ws);
                }


                /*$link_con_id = $this->html->bu($accion_permitida, $indice, $registro_id, $rows)
                if(errores::$error){
                    return $this->retorno_error(mensaje: 'Error al asignar link', data: $link_con_id,header:  $header, ws: $ws);
                }*/



                $links[$accion] = $link_con_id;
            }


            $data_result['registros'][$key] = array_merge($row,$links);
        }

        $salida = array(
            "draw"         => $draw,
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



    /**
     * Integra las acciones permitidas a un row para lista
     * @param array $acciones_permitidas Conjunto de acciones
     * @param int $indice Indice de la matriz de los registros a mostrar
     * @param string $key_id key de valor para registro id
     * @param array $row registro en proceso
     * @param array $rows conjunto de registros
     * @return array
     * @version 0.167.34
     */
    private function integra_acciones_permitidas(array $acciones_permitidas, int $indice, string $key_id, array $row,
                                                array $rows): array
    {

        if($indice < 0){
            return $this->errores->error(mensaje: 'Error indice debe ser mayor o igual a 0',data:  $indice);
        }
        $key_id = trim($key_id);
        if($key_id ===''){
            return $this->errores->error(mensaje: 'Error key_id esta vacio',data:  $key_id);
        }
        if(is_numeric($key_id)){
            return $this->errores->error(mensaje: 'Error key_id debe ser un campo con texto',data:  $key_id);
        }
        if(!isset($rows[$indice])){
            return $this->errores->error(mensaje: 'Error no existe el registro en proceso',data:  $rows);
        }

        if(!isset($rows[$indice]['acciones'])){
            $rows[$indice]['acciones'] = array();
        }

        foreach ($acciones_permitidas as $accion_permitida){

            $valida = $this->valida_data_btn(accion_permitida: $accion_permitida,key_id:  $key_id, row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
            }

            $rows = $this->html->boton_link_permitido(
                accion_permitida: $accion_permitida,indice:  $indice,registro_id:  $row[$key_id],rows:  $rows);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }

    /**
     * Integra el elemento a modificar en cambio de estatus
     * @param string $key Key a integrar
     * @return array
     * @version 0.182.34
     */
    private function integra_row_upd(string $key): array
    {
        if($this->registro_id<=0){
            return $this->errores->error(mensaje: 'Error this->registro_id debe ser mayor a 0',
                data:  $this->registro_id);
        }
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio', data:  $key);
        }

        $registro = $this->modelo->registro(registro_id: $this->registro_id, columnas_en_bruto: true, retorno_obj: true);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener adm_accion',data:  $registro);
        }



        $row_upd = $this->row_upd_status(key: $key,registro:  $registro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener row upd',data:  $row_upd);
        }
        return $row_upd;
    }
    
    /**
     * Genera la lista mostrable en la accion de cat_sat_tipo_persona / lista
     * @param bool $header if header se ejecuta en html
     * @param bool $ws retorna webservice
     * @return array
     */
    public function lista(bool $header, bool $ws = false): array
    {


        $registros_view = $this->rows_view_lista();
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al generar rows para lista', data:  $registros_view, header: $header, ws: $ws);
        }

        $this->registros = $registros_view;
        $n_registros = count($registros_view);
        $this->n_registros = $n_registros;

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



    public function modifica(bool $header, bool $ws = false, string $breadcrumbs = '',
                             bool $aplica_form = true, bool $muestra_btn = true): array|string
    {

        if($this->registro_id<=0){
            return $this->retorno_error(mensaje: 'Error registro_id debe ser mayor a 0', data: $this->registro_id,
                header:  $header, ws: $ws);
        }

        $r_modifica = parent::modifica(header: false, breadcrumbs: $breadcrumbs,aplica_form:  $aplica_form,
            muestra_btn: $muestra_btn); // TODO: Change the autogenerated stub
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

        $include_inputs_modifica = (new generales())->path_base."templates/inputs/$this->seccion/modifica.php";
        if(!file_exists($include_inputs_modifica)){
            $include_inputs_modifica = (new views())->ruta_templates."inputs/base/modifica.php";
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


    protected function row_upd(string $key): array|stdClass
    {
        $row_upd = $this->integra_row_upd(key: $key);
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
     * Integra el valor a modificar de tipo status
     * @param string $key Key del valor a ajustar
     * @param stdClass $registro Registro en proceso
     * @return array
     * @version 0.181.34
     */
    private function row_upd_status(string $key, stdClass $registro): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->errores->error(mensaje: 'Error key esta vacio', data:  $key);
        }
        $keys = array($key);
        $valida = $this->validacion->valida_statuses(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar registro', data:  $valida);
        }

        $row_upd[$key] = 'inactivo';
        if($registro->$key === 'inactivo'){
            $row_upd[$key] = 'activo';
        }
        return $row_upd;
    }

    private function rows_lista(): array|stdClass
    {
        $columnas = $this->columnas_lista();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar columnas para lista', data:  $columnas);
        }

        $registros = $this->modelo->registros(columnas:$columnas,return_obj: true);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener registros', data:  $registros);
        }
        return $registros;
    }

    private function rows_view_lista(): array
    {
        $registros = $this->rows_lista();
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener registros', data:  $registros);
        }

        $registros_view = (new actions())->registros_view_actions(acciones: $this->acciones, link: $this->link,
            obj_link: $this->obj_link,registros:  $registros, seccion:  $this->seccion);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al asignar link', data:  $registros_view);
        }
        return $registros_view;
    }

    /**
     * Intregra las acciones a los registros
     * @param string $key_id Registro id
     * @param array $rows Conjunto de registros
     * @param string $seccion Seccion a integrar acciones
     * @return array
     * @version 0.173.34
     */
    protected function rows_con_permisos(string $key_id, array $rows, string $seccion): array
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

        $acciones_permitidas = (new datatables())->acciones_permitidas(link: $this->link, seccion: $seccion);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener acciones',data:  $acciones_permitidas);
        }
        $rows = $this->genera_buttons_permiso(acciones_permitidas: $acciones_permitidas,
            key_id:  $key_id,rows:  $rows);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al integrar link',data:  $rows);
        }
        return $rows;
    }



    private function valida_data_btn(mixed $accion_permitida, string $key_id, array|stdClass $row): bool|array
    {
        $keys = array($key_id);
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        if(!is_array($accion_permitida)){
            return $this->errores->error(mensaje: 'Error accion_permitida debe ser array',data:  $accion_permitida);
        }
        $keys = array('adm_accion_descripcion','adm_accion_titulo','adm_seccion_descripcion','adm_accion_css',
            'adm_accion_es_status');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }
        return true;
    }

    /**
     * Valida que los keys rows lista sean validos
     * @param mixed $key_row_lista Key a validar
     * @return bool|array
     * @version 0.125.33
     */
    private function valida_key_rows_lista(mixed $key_row_lista): bool|array
    {
        if(!is_object($key_row_lista)){
            return $this->errores->error(mensaje: 'Error el key_row_lista debe ser un objeto', data:  $key_row_lista);
        }
        $keys = array('campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $key_row_lista,valida_vacio: false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
        }

        return true;
    }
}
