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

class system extends controlador_base{

    private html_controler $html;

    public stdClass $acciones;

    public links_menu $obj_link;
    public array $secciones = array();
    public array $keys_row_lista = array();
    public array $rows_lista = array('id','codigo','codigo_bis','descripcion','descripcion_select','alias');
    public array $inputs_alta = array('codigo','codigo_bis','descripcion','descripcion_select','alias');
    public array $inputs_modifica = array('id','codigo','codigo_bis','descripcion','descripcion_select','alias');
    public string $forms_inputs_alta = '';
    public string $forms_inputs_modifica = '';
    public html $html_base;

    /**
     * @param html_controler $html Html base
     * @param PDO $link
     * @param modelo $modelo
     * @param links_menu $obj_link
     * @param array $filtro_boton_lista
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @param stdClass $paths_conf
     */
    public function __construct(html_controler $html,PDO $link, modelo $modelo, links_menu $obj_link,
                                array $filtro_boton_lista = array(), string $campo_busca = 'registro_id',
                                string $valor_busca_fault = '', stdClass $paths_conf = new stdClass())
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
            $retorno = (new actions())->retorno_alta_bd(links:$this->obj_link, seccion: $this->tabla,
                siguiente_view: $siguiente_view);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al dar de alta registro', data: $r_alta_bd, header:  true,
                    ws: $ws);
            }
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            echo json_encode($r_alta_bd, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_alta_bd->siguiente_view = $siguiente_view;
        return $r_alta_bd;
    }

    public function elimina_bd(bool $header, bool $ws): array
    {
        $r_del = parent::elimina_bd(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al eliminar', data: $r_del, header:  $header,
                ws: $ws);
        }
        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            echo json_encode($r_del, JSON_THROW_ON_ERROR);
            exit;
        }
        return $r_del;
    }

    /**
     * Genera la lista mostrable en la accion de cat_sat_tipo_persona / lista
     * @version 0.5.0
     * @param bool $header if header se ejecuta en html
     * @param bool $ws retorna webservice
     * @return array
     */
    public function lista(bool $header, bool $ws = false): array
    {
        $columnas = array();
        foreach ($this->keys_row_lista as $key_row_lista){
            $columnas[] = $key_row_lista->campo;
        }

        $registros = $this->modelo->registros(columnas:$columnas,return_obj: true);

        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener registros',
                data:  $registros, header: $header, ws: $ws);
        }

        $registros_view = (new actions())->registros_view_actions(acciones: $this->acciones,
            obj_link: $this->obj_link,registros:  $registros, seccion:  $this->seccion);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al asignar link', data:  $registros_view, header: $header,
                    ws: $ws);
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
}
