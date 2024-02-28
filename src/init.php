<?php
namespace gamboamartin\system;
use config\generales;
use config\views;
use gamboamartin\errores\errores;
use gamboamartin\template\html;
use stdClass;

class init{
    protected errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera los datos para una lista
     * @param string $campo_puro Campo puro de la tabla en ejecucion
     * @param string $tabla Tabla o seccion o modelo
     * @return array|stdClass
     * @version 0.163.34
     */
    private function data_key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        $campo_puro = trim($campo_puro);
        if($campo_puro === ''){
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio',data:  $campo_puro);
        }

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

    /**
     * Genera los keys para un row de lista
     * @param string $key_value Key a integrar
     * @param string $name_lista Nombre a mostrar
     * @return stdClass|array
     * @version 0.166.34
     */
    private function genera_key_row_lista(string $key_value, string $name_lista): stdClass|array
    {
        $key_value = trim($key_value);
        if($key_value === ''){
            return $this->error->error(mensaje: 'Error key_value esta vacio',data:  $key_value);
        }
        $name_lista = trim($name_lista);
        if($name_lista === ''){
            return $this->error->error(mensaje: 'Error name_lista esta vacio',data:  $name_lista);
        }

        $keys_row_lista = new stdClass();
        $keys_row_lista->campo = $key_value;
        $keys_row_lista->name_lista = $name_lista;

        return $keys_row_lista;
    }

    /**
     * Integra los bread de listas
     * @param system $controler Controlador en ejecucion
     * @return array|string
     * @version 7.56.3
     *
     */
    final public function include_breadcrumb(system $controler): array|string
    {
        $include_breadcrumb = (new views())->ruta_templates."head/$controler->accion/title.php";
        if(file_exists("templates/head/$controler->tabla/$controler->accion/title.php")){
            $include_breadcrumb = "templates/head/$controler->tabla/$controler->accion/title.php";
        }

        $include_breadcrumb_rs = $this->include_breadcrumbs(controlador: $controler, include_breadcrumb: $include_breadcrumb);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al inicializar include_breadcrumb_rs', data: $include_breadcrumb_rs);
        }
        return $include_breadcrumb_rs;
    }

    /**
     * Integra los breadcrumbs de navegacion
     * @param system $controlador Controlador en ejecucion
     * @param string $include_breadcrumb include file
     * @return string
     * @version 7.54.3
     */
    private function include_breadcrumbs(system $controlador, string $include_breadcrumb): string
    {
        $controlador->include_breadcrumb = $include_breadcrumb;
        if(!file_exists($include_breadcrumb)){
            $controlador->include_breadcrumb = '';
        }
        return $controlador->include_breadcrumb;
    }

    /**
     * Inicializa las acciones basicas para botones
     * @param system $controller Controlador en ejecucion
     * @return stdClass
     * @version 0.92.32
     * @por_doc true
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
     * Inicializa los datos de un controller
     * @param system $controller Controlador en ejecucion
     * @param html $html Html de template
     * @return array|stdClass
     * @version 0.163.34
     */
    final public function init_controller(system $controller, html $html): array|stdClass
    {
        $seccion = $controller->seccion;

        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $init_msj = (new mensajeria())->init_mensajes(controler: $controller,html: $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar mensajes', data: $init_msj);
        }

        $init_links = (new links_menu(
            link: $controller->link, registro_id: $controller->registro_id))->init_link_controller(
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
     * Obtiene un row para una lista
     * @param string $campo_puro Campo puro de la tabla en ejecucion
     * @param string $tabla Tabla o seccion o modelo
     * @return array|stdClass
     * @version 0.168.34
     */
    private function key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        $campo_puro = trim($campo_puro);
        if($campo_puro === ''){
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio',data:  $campo_puro);
        }

        $data_key_row_lista = $this->data_key_row_lista(campo_puro: $campo_puro, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $data_key_row_lista', data: $data_key_row_lista);
        }

        $key_row_lista = $this->genera_key_row_lista(
            key_value: $data_key_row_lista->key_value,name_lista:  $data_key_row_lista->name_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar $keys_row_lista', data: $key_row_lista);
        }
        return $key_row_lista;
    }

    /**
     * Genera los keys para una lista
     * @param system $controler Controlador en ejecucion
     * @return array
     * @version 0.169.34
     */
    final public function keys_row_lista(system $controler): array
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
     * POR DOCUMENTAR EN WIKI
     * Limpia una fila de datos especificada por una clave.
     *
     * Esta función toma una clave y una matriz de datos, y la función busca la clave en la matriz.
     * Si la clave existe en la matriz, la función la elimina.
     * Una vez realizada la operación, la matriz modificada se devuelve como resultado.
     *
     * @param string $key La clave que se debe buscar en la matriz de datos para eliminar.
     * @param array $row La matriz de datos donde se debe buscar la clave.
     *
     * @return array Una matriz de datos modificada después de eliminar la clave especificada.
     * @version 17.4.0
     */
    private function limpia_data_row(string $key, array $row): array
    {
        if(isset($row[$key])){
            unset($row[$key]);
        }
        return $row;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Limpia los datos de las filas proporcionadas.
     *
     * @final
     * @param array $keys Las claves de los datos a limpiar.
     * @param array $row Los datos de la fila a limpiar.
     * @return array $row Los datos de la fila después de la limpieza.
     *
     * La función `limpia_rows` recorre cada clave proporcionada en el parámetro `$keys`.
     * Para cada clave, intenta limpiar los datos en la fila correspondiente usando la función `limpia_data_row`.
     * Si se produce un error durante este proceso, la función registra el error y devuelve un mensaje de error utilizando la función `error`.
     * Si todas las claves se procesan con éxito, la función devuelve la fila con los datos limpios.
     *
     * @throws errores Si ocurre un error al limpiar los datos, se lanza una excepción.
     * @version 18.9.0
     */
    final public function limpia_rows(array $keys, array $row): array
    {
        foreach ($keys as $key){
            $row = $this->limpia_data_row(key: $key,row:  $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar datos',data:  $row);
            }
        }
        return $row;
    }

    /**
     * Genera el hd de una lista
     * @param string $campo_puro Nombre del campo
     * @return string
     * @version 0.62.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-05 16:46
     * @author mgamboa
     */
    private function name_lista(string $campo_puro): string
    {
        $campo_puro = trim($campo_puro);

        $name_lista = str_replace('_', ' ', $campo_puro);
        return ucwords($name_lista);
    }

    /**
     * Inicializa los datos de retorno
     * @param string $accion Accion a integrar
     * @param string $seccion Seccion a integrar
     * @param int $registro_id Id en proceso
     * @return stdClass|array
     * @version 0.257.38
     */
    private function retornos(string $accion, string $seccion, int $registro_id = -1): stdClass|array
    {
        $accion = trim($accion);
        $seccion = trim($seccion);

        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion esta vacia',data:  $accion);
        }
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data:  $seccion);
        }

        $next_seccion = $seccion;
        $next_accion = $accion;
        $id_retorno = $registro_id;

        $data = new stdClass();
        $data->next_seccion = $next_seccion;
        $data->next_accion = $next_accion;
        $data->id_retorno = $id_retorno;
        return $data;

    }

    /**
     * Inicializa las variables de retorno por GET despues de una transaccion
     * @param string $accion Accion a retornar
     * @param string $seccion Seccion a retornar
     * @param int $id_retorno Id a retornar
     * @return array|stdClass
     *
     */
    public function retornos_get(string $accion, string $seccion, int $id_retorno = -1): array|stdClass
    {
        $accion = trim($accion);
        $seccion = trim($seccion);

        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion esta vacia',data:  $accion);
        }
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data:  $seccion);
        }

        $retornos_init = $this->retornos(accion: $accion, seccion: $seccion, registro_id: $id_retorno);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar retornos',data:  $retornos_init);
        }

        if(isset($_GET['next_seccion'])){
            $retornos_init->next_seccion = $_GET['next_seccion'];
        }
        if(isset($_GET['next_accion'])){
            $retornos_init->next_accion = $_GET['next_accion'];
        }
        if(isset($_GET['id_retorno'])){
            $retornos_init->id_retorno = $_GET['id_retorno'];
        }
        $retornos_init->adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $retornos_init->adm_menu_id = $_GET['adm_menu_id'];
        }

        return $retornos_init;

    }

    /**
     * Asigna un valor a sun row id para su uso en selects
     * @param stdClass|array $row Registro verificar
     * @param string $tabla Tabla o modelo
     * @param string $key Key del campo  a inicializar
     * @return stdClass|array
     * @version 0.60.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-05 09:43
     * @author mgamboa
     */
    public function row_value_id(stdClass|array $row, string $tabla, string $key = ''): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error la tabla esta vacia',data:  $tabla);
        }
        $row_ = $row;
        if(is_array($row_)){
            $row_ = (object)$row_;
        }

        $key = trim($key);
        if($key==='') {
            $key = $tabla . '_id';
        }

        if(!isset($row_->$key)){
            $row_->$key = -1;
        }
        if((int)$row_->$key === -1){
            $generales = new generales();
            if(isset($generales->defaults[$tabla]['id'])) {
                $row_->$key = $generales->defaults[$tabla]['id'];
            }
        }
        if(is_array($row)){
            $row_ = (array)$row_;
        }

        return $row_;
    }


}
