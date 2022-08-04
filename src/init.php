<?php
namespace gamboamartin\system;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\template\html;
use stdClass;

class init{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * @param string $campo_puro Campo puro de la tabla en ejecucion
     * @param string $tabla Tabla o seccion o modelo
     * @return array|stdClass
     */
    private function data_key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $key_value = $this->key_value_campo(campo_puro: $campo_puro, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar key value lista', data: $key_value);
        }

        $name_lista = $this->name_lista(campo_puro: $campo_puro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $name_lista', data: $name_lista);
        }
        $data = new stdClass();
        $data->key_value = $key_value;
        $data->name_lista = $name_lista;
        return $data;
    }

    private function genera_key_row_lista(string $key_value, string $name_lista): stdClass
    {
        $keys_row_lista = new stdClass();
        $keys_row_lista->campo = $key_value;
        $keys_row_lista->name_lista = $name_lista;

        return $keys_row_lista;
    }

    /**
     * @param system $controller Controlador en ejecucion
     * @return stdClass
     */
    private function init_acciones_base(system $controller): stdClass
    {
        $controller->acciones = new stdClass();
        $controller->acciones->modifica = new stdClass();
        $controller->acciones->elimina_bd = new stdClass();

        $controller->acciones->modifica->style = 'info';
        $controller->acciones->modifica->style_status = false;

        $controller->acciones->elimina_bd->style = 'danger';
        $controller->acciones->elimina_bd->style_status = false;

        return $controller->acciones;
    }

    /**
     * @param system $controller Controlador en ejecucion
     * @param html $html
     * @return array|stdClass
     */
    public function init_controller(system $controller, html $html): array|stdClass
    {
        $init_msj = (new mensajeria())->init_mensajes(controler: $controller,html: $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar mensajes', data: $init_msj);
        }

        $init_links = (new links_menu(registro_id: $controller->registro_id))->init_link_controller(
            controler: $controller);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar links', data: $init_links);
        }

        $init_acciones = $this->init_acciones_base(controller:$controller);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar acciones', data: $init_acciones);
        }


        $data = new stdClass();
        $data->msj = $init_msj;
        $data->links = $init_links;
        return $data;
    }

    /**
     * @param string $campo_puro Campo puro de la tabla en ejecucion
     * @param string $tabla Tabla o seccion o modelo
     * @return array|stdClass
     */
    private function key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $data_key_row_lista = $this->data_key_row_lista(campo_puro: $campo_puro, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $data_key_row_lista', data: $data_key_row_lista);
        }

        $key_row_lista = $this->genera_key_row_lista(key_value: $data_key_row_lista->key_value,name_lista:  $data_key_row_lista->name_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $keys_row_lista', data: $key_row_lista);
        }
        return $key_row_lista;
    }

    /**
     * @param system $controler Controlador en ejecucion
     * @return array
     */
    public function keys_row_lista(system $controler): array
    {

        foreach ($controler->rows_lista as $row){

            $key_row_lista = $this->key_row_lista(campo_puro: $row, tabla: $controler->tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar $key_row_lista', data: $key_row_lista);
            }

            $controler->keys_row_lista[] = $key_row_lista;

        }
        return $controler->keys_row_lista;
    }

    /**
     * Limpia los elementos de tipo row previos al alta de un registro
     * @param string $key Key a limpiar
     * @param array $row Registro a aplicar limpieza
     * @version 0.27.30
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-29 11:21
     * @return array
     */
    private function limpia_data_row(string $key, array $row): array
    {
        if(isset($row[$key])){
            unset($row[$key]);
        }
        return $row;
    }

    /**
     * Limpiar los elementos de un registro previo a su insersion
     * @param array $keys Keys a limpiar
     * @param array $row Registro a limpiar
     * @version 0.29.30
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-29 12:08
     * @return array
     */
    public function limpia_rows(array $keys, array $row): array
    {
        foreach ($keys as $key){
            $row = $this->limpia_data_row(key: $key,row:  $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar datos',data:  $row);
            }
        }
        return $row;
    }

    private function name_lista(string $campo_puro): string
    {
        $name_lista = str_replace('_', ' ', $campo_puro);
        return ucwords($name_lista);
    }

    /**
     * Genera un key para un campo
     * @version 0.20.5
     * @param string $campo_puro Campo puro de la tabla en ejecucion
     * @param string $tabla Tabla o seccion o modelo
     * @return string|array
     */
    private function key_value_campo(string $campo_puro, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        $campo_puro = trim($campo_puro);
        if($campo_puro === ''){
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio',data:  $campo_puro);
        }

        return $tabla.'_'.$campo_puro;
    }

    /**
     * Asigna un valor a sun row id para su uso en selects
     * @param stdClass $row Registro verificar
     * @param string $tabla Tabla o modelo
     * @return stdClass
     */
    public function row_value_id(stdClass $row, string $tabla): stdClass
    {
        $key = $tabla.'_id';
        if(!isset($row->$key)){
            $row->$key = -1;
        }
        if((int)$row->$key === -1){
            $row->$key = (new generales())->defaults[$tabla]['id'];
        }

        return $row;
    }


}
