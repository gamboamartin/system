<?php
namespace gamboamartin\system;

use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class actions{

    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Asigna a registros de lista los links mostrables en la lista para su ejecucion
     * @version 0.25.2
     * @param string $accion Accion a ejecutar en el boton
     * @param int $indice Indice del arreglo para vista
     * @param string $link Liga de ejecucion de tipo a
     * @param array $registros_view Conjunto de registros obtenidos
     * @param stdClass $row Registro a ajustar o generar link
     * @param string $style Estilo css danger info, success etc
     * @return array
     */
    private function asigna_link_row(string $accion, int $indice, string $link, array $registros_view, stdClass $row,
                                     string $style): array
    {
        if($indice<0){
            return $this->error->error(mensaje: 'Error el indice debe ser mayor o igual a 0', data:  $indice);
        }

        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error  $accion esta vacia', data:  $accion);
        }
        $link = trim($link);

        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error  $style esta vacio', data:  $style);
        }

        $name_link = 'link_'.$accion;
        $row->$name_link = $link;

        $style_att = $accion.'_style';

        $row->$style_att = $style;
        $registros_view[$indice] = $row;
        return $registros_view;
    }

    /**
     * Asigna los links necesarios de cada controller para ser usados en las views y header
     * @param string $accion Accion a ejecutar en el boton
     * @param int $indice Indice de row de registros
     * @param PDO $link
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros_view Registros de  salida para view
     * @param stdClass $row Registro en verificacion y asignacion
     * @param string $seccion Seccion en ejecucion
     * @param string $style Estilos para botones
     * @return array
     * @version 0.30.2
     */
    private function asigna_link_rows(string $accion, int $indice, PDO $link, links_menu $obj_link, array $registros_view,
                                      stdClass $row, string $seccion, string $style): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error no $accion esta vacio', data:  $accion);
        }
        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error  $style esta vacio', data:  $style);
        }

        $key_id = $this->key_id(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key id', data:  $key_id);
        }

        $valida = $this->valida_data_link(accion: $accion,key_id: $key_id,row: $row,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $link = $this->link_accion(accion: $accion,key_id:  $key_id, link: $link,
            obj_link: $obj_link,row:  $row,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link', data:  $link);
        }

        $registros_view = $this->asigna_link_row(accion: $accion,indice:  $indice,
            link:  $link,registros_view:  $registros_view,row:  $row, style: $style);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar link', data:  $registros_view);
        }
        return $registros_view;
    }

    /**
     * Inicializa los datos para una accion de tipo alta bd
     * @param string $siguiente_view Siguiente view default
     * @return array|string
     */
    final public function init_alta_bd(string $siguiente_view = 'modifica'): array|string
    {
        $siguiente_view = $this->siguiente_view(siguiente_view: $siguiente_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view);
        }
        $limpia_button = $this->limpia_butons();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar botones', data: $limpia_button);
        }
        return $siguiente_view;
    }

    /**
     * @param string $accion Accion a ejecutar en el boton
     * @param PDO $link Conexion a base de datos
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros Registros en proceso
     * @param array $registros_view Registros de  salida para view
     * @param string $seccion Seccion en ejecucion
     * @param string $style Estilos para botones
     * @param bool $style_status Estilo de botones de tipo status
     * @return array
     * @version 0.281.38
     */
    private function genera_link_row(string $accion, PDO $link, links_menu $obj_link, array $registros, array $registros_view,
                                     string $seccion, string $style, bool $style_status): array
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error no $accion esta vacio', data:  $accion);
        }

        foreach ($registros as $indice=>$row){

            if(!is_object($row)){
                return $this->error->error(mensaje: 'Error row debe ser un objeto', data:  $row);
            }

            if($style_status){
                $style = $this->style(accion: $accion, row: $row, seccion: $seccion);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al asignar style', data:  $style);
                }

            }

            $style = trim($style);
            if($style === ''){
                return $this->error->error(mensaje: 'Error  $style esta vacio', data:  $style);
            }

            $registros_view = $this->asigna_link_rows(accion: $accion,indice:  $indice, link: $link,obj_link:  $obj_link,
                registros_view: $registros_view,row:  $row, seccion: $seccion, style: $style);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar link', data:  $registros_view);
            }
        }
        return $registros_view;
    }

    /**
     * Limpia los valores POST de botones
     * @version 0.6.5 Se ajusta modelo base
     * @return array
     */
    private function limpia_butons(): array
    {
        if(isset($_POST['guarda'])){
            unset($_POST['guarda']);
        }
        if(isset($_POST['guarda_otro'])){
            unset($_POST['guarda_otro']);
        }
        if(isset($_POST['btn_action_next'])){
            unset($_POST['btn_action_next']);
        }
        return $_POST;
    }

    /**
     * Asigna los datos de un link para ser usado en la views
     * @param string $accion Accion a ejecutar en el boton
     * @param string $key_id Key donde se encuentra el id del modelo
     * @param PDO $link
     * @param links_menu $obj_link Objeto para generacion de links
     * @param stdClass $row Registro en verificacion y asignacion
     * @param string $seccion Seccion en ejecucion
     * @param int $registro_id
     * @return array|string
     * @version 0.28.2
     */
    private function link_accion(string $accion, string $key_id, PDO $link, links_menu $obj_link, stdClass $row,
                                 string $seccion, int $registro_id = -1): array|string
    {


        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: $accion, adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data:  $tengo_permiso);
        }


        $links_menu = new stdClass();
        $links_menu->links = new stdClass();
        $links_menu->links->$seccion = new stdClass();
        $links_menu->links->$seccion->$accion = '';

        if($tengo_permiso) {
            $valida = $this->valida_data_link(accion: $accion, key_id: $key_id, row: $row, seccion: $seccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
            }

            if ($registro_id !== -1) {
                $row->$key_id = $registro_id;
            }

            $links_menu = new $obj_link(link: $link, registro_id: $row->$key_id);

            if (!isset($links_menu->links->$seccion)) {
                return $this->error->error(mensaje: "Error no existe links_menu->$seccion", data: $links_menu);
            }
            $existe_accion = isset($links_menu->links->$seccion->$accion);
            $existe_accion_2 = isset($obj_link->links->$seccion->$accion);

            if (!$existe_accion && !$existe_accion_2) {
                return $this->error->error(mensaje: "Error no existe links_menu->$seccion->$accion", data: $links_menu);
            }

            if (!$existe_accion) {
                $links_menu->links->$seccion = $obj_link->links->$seccion;
            }

        }

        return $links_menu->links->$seccion->$accion;
    }

    /**
     * Genera el key de identificar de la tabla
     * @param string $seccion Seccion en ejecucion
     * @return string|array
     * @version 0.21.1
     */
    private function key_id(string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }
        return $seccion.'_id';
    }

    /**
     * @param stdClass $acciones Acciones a integrar
     * @param PDO $link Conexion a la base de datos
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros Registros a mostrar en la lista
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 4.41.2
     */
    public function registros_view_actions(stdClass $acciones, PDO $link, links_menu $obj_link, array $registros,
                                           string $seccion): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }

        $registros_view = array();
        foreach ($acciones as $accion=>$data_accion){
            if(!is_object($data_accion)){
                $data_accion = new stdClass();
            }
            if(!isset($data_accion->style)){
                $data_accion->style = 'info';
            }
            if(!isset($data_accion->style_status)){
                $data_accion->style_status = '';
            }
            $style = $data_accion->style;
            $style_status = $data_accion->style_status;

            $registros_view = $this->genera_link_row(accion: $accion, link: $link, obj_link: $obj_link, registros:  $registros,
                registros_view: $registros_view,seccion:  $seccion, style: $style, style_status:$style_status);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar link', data:  $registros_view);
            }
        }
        return $registros_view;
    }

    /**
     * Genera un link para ser aplicado en header despues de una accion
     * @param int $registro_id Registro identificador del registro a procesar
     * @param string $seccion Seccion en ejecucion
     * @param string $siguiente_view Que accion se ejecutara
     * @param array $params Parametros extra por get
     * @param bool $valida_permiso Si valida permiso retorna error en caso de no tener permiso
     * @return array|string link para header
     * @version 0.22.2
     */
    final public function retorno_alta_bd(PDO $link, int $registro_id, string $seccion, string $siguiente_view,
                                    array $params = array(), bool $valida_permiso = false): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }

        $siguiente_view = trim($siguiente_view);
        if($siguiente_view === ''){
            $siguiente_view = 'modifica';
        }

        $link = (new links_menu(link: $link, registro_id: $registro_id))->link_con_id(
            accion:$siguiente_view, link: $link, registro_id: $registro_id, seccion: $seccion,params: $params,
            valida_permiso: $valida_permiso);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link', data:  $link);
        }


        return $link;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta es la función "siguiente_view" en el archivo actions.php
     *
     * @param string $siguiente_view - Es la próxima vista a mostrar por defecto, que es 'modifica'.
     * Si no se especifica un valor por parte del usuario, se toma este valor por defecto
     *
     * @return string Retorna la próxima vista a mostrar dependiendo de las acciones del usuario.
     * Si el usuario ha hecho clic en el botón 'guarda_otro',
     * el valor devuelto por la función será 'alta'.
     * Si el usuario ha hecho clic en algún botón con nombre 'btn_action_next',
     * la función devolverá el valor que tenga asignado dicho botón.
     * Si no se cumple ninguna de las condiciones anteriores,
     * la función devuelve el valor del parámetro que se le pasó.
     *
     * @version 17.2.0
     */
    final public function siguiente_view(string $siguiente_view = 'modifica'): string
    {

        if(isset($_POST['guarda_otro'])){
            $siguiente_view = 'alta';
        }
        elseif (isset($_POST['btn_action_next'])){
            $siguiente_view = (string)$_POST['btn_action_next'];
        }
        return $siguiente_view;
    }

    /**
     * Genera el estilo de un css
     * @param string $accion Accion a integrar estilo
     * @param stdClass $row Registro en proceso
     * @param string $seccion Seccion
     * @return string|array
     * @version 0.188.35
     */
    private function style(string $accion, stdClass $row, string $seccion): string|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion esta vacia', data:  $accion);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:  $seccion);
        }

        $style = 'danger';
        $key = $seccion.'_'.$accion;

        if(!(isset($row->$key))){
            return $this->error->error(mensaje: 'Error no existe $row->'.$key, data:  $key);
        }

        if($row->$key === 'activo'){
            $style = 'info';
        }
        return $style;
    }

    /**
     * @param string $accion Accion a ejecutar en el boton
     * @param string $key_id Key donde se encuentra el id del modelo
     * @param stdClass $row Registro en verificacion y asignacion
     * @param string $seccion Seccion en ejecucion
     * @return bool|array
     */
    private function valida_data_link(string $accion, string $key_id, stdClass $row, string $seccion): bool|array
    {
        $key_id = trim($key_id);
        if($key_id === ''){
            return $this->error->error(mensaje: 'Error no $key_id esta vacio', data:  $key_id);
        }
        if(!isset($row->$key_id)){
            return $this->error->error(mensaje: "Error no existe row->$key_id", data:  $row);
        }

        if(!is_numeric($row->$key_id)){
            return $this->error->error(mensaje: "Error  row->$key_id debe ser un entero positivo", data:  $row);
        }
        if((int)$row->$key_id<=0){
            return $this->error->error(mensaje: "Error  row->$key_id debe ser mayor a 0", data:  $row);
        }

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error no $seccion esta vacio', data:  $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error no $accion esta vacio', data:  $accion);
        }
        return true;
    }


}
