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
     * REG
     * Inicializa las acciones base para los botones de un controlador.
     *
     * Esta función configura las acciones comunes de un controlador, específicamente
     * las acciones de modificar y eliminar_bd. Además, asigna estilos por defecto
     * a cada acción, y establece el estado de cada una de ellas.
     *
     * La estructura resultante incluye dos acciones con su respectivo estilo y estado,
     * lo que permite al controlador usar estas configuraciones para generar botones
     * o realizar otras operaciones de manera consistente.
     *
     * @param system $controller Controlador en ejecución, que recibirá las configuraciones
     *                           de las acciones.
     *
     * @return stdClass Devuelve un objeto `stdClass` que contiene las acciones inicializadas.
     *
     * @example
     * ```php
     * $controller = new system();
     * $acciones = $this->init_acciones_base($controller);
     * echo $acciones->modifica->style; // Imprime 'info'
     * echo $acciones->elimina_bd->style; // Imprime 'danger'
     * ```
     *
     * @version 0.92.32
     */
    private function init_acciones_base(system $controller): stdClass
    {
        $controller->acciones = new stdClass();
        $controller->acciones->modifica = new stdClass();
        $controller->acciones->elimina_bd = new stdClass();

        // Acción de modificar
        $controller->acciones->modifica->style = 'info';  // Estilo de color para modificar
        $controller->acciones->modifica->style_status = false;  // Estado de estilo para modificar

        // Acción de eliminar_bd
        $controller->acciones->elimina_bd->style = 'danger';  // Estilo de color para eliminar_bd
        $controller->acciones->elimina_bd->style_status = false;  // Estado de estilo para eliminar_bd

        return $controller->acciones;
    }


    /**
     * REG
     * Inicializa los datos del controlador y configura mensajes, links y acciones.
     *
     * Esta función se encarga de inicializar los datos necesarios para un controlador específico.
     * Configura los mensajes a mostrar, genera los links asociados a las acciones disponibles
     * y configura las acciones base (como modificar y eliminar). Devuelve los datos necesarios
     * para continuar con el flujo de trabajo del controlador.
     *
     * Los parámetros de entrada permiten pasar la instancia del controlador y los datos necesarios
     * para la construcción de la interfaz, mientras que la función devuelve un objeto con los
     * resultados de la configuración de mensajes, links y acciones.
     *
     * @param system $controller Instancia del controlador en ejecución.
     *                           El controlador es responsable de gestionar la lógica de negocio y
     *                           de mantener el estado de las variables de sesión y datos.
     *                           Se espera que el controlador tenga configurada una propiedad `seccion`.
     *
     * @param html $html Instancia de la clase `html` que proporciona las funciones necesarias
     *                   para generar elementos HTML y manejar las plantillas.
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las propiedades `msj` (mensajes) y `links`
     *                        (links generados). Si hay algún error durante la configuración, se devuelve
     *                        un array con el mensaje de error.
     *
     * @throws array Si ocurre un error en algún paso, se devuelve un array con el mensaje de error.
     *
     * @example
     * ```php
     * $controller = new system();
     * $html = new html();
     * $result = $this->init_controller($controller, $html);
     * if (isset($result->msj)) {
     *     echo $result->msj; // Mostrar mensajes inicializados
     * }
     * if (isset($result->links)) {
     *     echo $result->links; // Mostrar links generados
     * }
     * ```
     * En este ejemplo, el controlador y los datos HTML son pasados a la función `init_controller`.
     * Dependiendo de la configuración de la clase `system`, la función generará los mensajes
     * y links adecuados, y devolverá los resultados en el objeto `stdClass`.
     *
     * @version 0.92.32
     */
    final public function init_controller(system $controller, html $html): array|stdClass
    {
        // Sección es extraída del controlador para verificar que no esté vacía
        $seccion = $controller->seccion;

        // Si la sección está vacía, se devuelve un error
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }

        // Inicializa los mensajes usando la clase mensajeria
        $init_msj = (new mensajeria())->init_mensajes(controler: $controller, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar mensajes', data: $init_msj);
        }

        // Inicializa los links mediante la clase links_menu
        $init_links = (new links_menu(
            link: $controller->link, registro_id: $controller->registro_id))->init_link_controller(
            controler: $controller);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar links', data: $init_links);
        }

        // Inicializa las acciones base (como modificar y eliminar)
        $init_acciones = $this->init_acciones_base(controller: $controller);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar acciones', data: $init_acciones);
        }

        // Devuelve los datos inicializados como un objeto stdClass
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
