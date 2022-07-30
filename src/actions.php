<?php
namespace gamboamartin\system;

use gamboamartin\errores\errores;
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
        if($link === ''){
            return $this->error->error(mensaje: 'Error  $link esta vacio', data:  $link);
        }
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
     * @version 0.30.2
     * @param string $accion Accion a ejecutar en el boton
     * @param int $indice Indice de row de registros
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros_view Registros de  salida para view
     * @param stdClass $row Registro en verificacion y asignacion
     * @param string $seccion Seccion en ejecucion
     * @param string $style Estilos para botones
     * @return array
     */
    private function asigna_link_rows(string $accion, int $indice, links_menu $obj_link, array $registros_view,
                                      stdClass $row, string $seccion, string $style): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }

        $key_id = $this->key_id(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key id', data:  $key_id);
        }

        $valida = $this->valida_data_link(accion: $accion,key_id: $key_id,row: $row,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $link = $this->link_accion(accion: $accion,key_id:  $key_id, obj_link: $obj_link,row:  $row,seccion:  $seccion);
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
     * @version v0.15.5
     * @return array|string
     */
    public function init_alta_bd(): array|string
    {
        $siguiente_view = $this->siguiente_view();
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
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros
     * @param array $registros_view Registros de  salida para view
     * @param string $seccion Seccion en ejecucion
     * @param string $style Estilos para botones
     * @param bool $style_status
     * @return array
     */
    private function genera_link_row(string $accion, links_menu $obj_link, array $registros, array $registros_view,
                                     string $seccion, string $style, bool $style_status): array
    {

        /**
         * REFACTORIZAR
         */
        foreach ($registros as $indice=>$row){

            if($style_status){

                $style = $this->style(accion: $accion, row: $row, seccion: $seccion);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al asignar style', data:  $style);
                }

            }
            $registros_view = $this->asigna_link_rows(accion: $accion,indice:  $indice,obj_link:  $obj_link,
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
     * @version 0.28.2
     * @param string $accion Accion a ejecutar en el boton
     * @param string $key_id Key donde se encuentra el id del modelo
     * @param links_menu $obj_link Objeto para generacion de links
     * @param stdClass $row Registro en verificacion y asignacion
     * @param string $seccion Seccion en ejecucion
     * @return array|string
     */
    private function link_accion(string $accion, string $key_id , links_menu $obj_link, stdClass $row,
                                 string $seccion): array|string
    {

        $valida = $this->valida_data_link(accion: $accion,key_id: $key_id,row: $row,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $links_menu = new $obj_link(registro_id: $row->$key_id);

        if(!isset($links_menu->links->$seccion)){
            return $this->error->error(mensaje: "Error no existe links_menu->$seccion", data:  $links_menu);
        }
        $existe_accion = isset($links_menu->links->$seccion->$accion);
        if(!$existe_accion){
            return $this->error->error(mensaje: "Error no existe links_menu->$seccion->$accion", data:  $links_menu);
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
     * @param stdClass $acciones
     * @param links_menu $obj_link Objeto para generacion de links
     * @param array $registros
     * @param string $seccion Seccion en ejecucion
     * @return array
     */
    public function registros_view_actions(stdClass $acciones, links_menu $obj_link, array $registros,
                                           string $seccion): array
    {
        $registros_view = array();
        foreach ($acciones as $accion=>$data_accion){
            $style = $data_accion->style;
            $style_status = $data_accion->style_status;

            $registros_view = $this->genera_link_row(accion: $accion, obj_link: $obj_link, registros:  $registros,
                registros_view: $registros_view,seccion:  $seccion, style: $style, style_status:$style_status);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar link', data:  $registros_view);
            }
        }
        return $registros_view;
    }

    /**
     * Genera un link para ser aplicado en header despues de una accion
     * @version 0.22.2
     * @param int $registro_id Identificador del modelo
     * @param string $seccion Seccion en ejecucion
     * @param string $siguiente_view Que accion se ejecutara
     * @return array|string link para header
     */
    public function retorno_alta_bd(int $registro_id, string $seccion, string $siguiente_view): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data:  $seccion);
        }
        $links = new links_menu(registro_id: $registro_id);

        if(!isset($links->links->$seccion)){
            return $this->error->error(mensaje: 'Error la seccion no esta habilitada para links', data:  $seccion);
        }

        $retorno = $links->links->$seccion->modifica;
        if($siguiente_view === 'alta'){
            $retorno = $links->links->$seccion->alta;
        }
        return $retorno;
    }

    /**
     * Determina que funcion se ejecutara despues del alta bd
     * @version 1.16.1
     * @return string
     */
    private function siguiente_view(): string
    {
        $siguiente_view = 'modifica';
        if(isset($_POST['guarda_otro'])){
            $siguiente_view = 'alta';
        }
        return $siguiente_view;
    }

    private function style(string $accion, stdClass $row, string $seccion): string
    {
        $style = 'danger';
        $key = $seccion.'_'.$accion;
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
