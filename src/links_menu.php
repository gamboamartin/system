<?php
namespace gamboamartin\system;
use base\controller\controler;
use config\generales;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class links_menu{
    public stdClass $links;
    protected string $session_id;
    protected errores $error;
    private array $secciones;

    /**
     * @param int $registro_id Registro a integrar en el link href
     */
    public function __construct(PDO $link, int $registro_id){
        $this->error = new errores();
        $this->links = new stdClass();
        $this->session_id = (new generales())->session_id;

        $secciones = (new adm_seccion_pertenece(link: $link))->secciones_paquete();
        if(errores::$error){
            $error = $this->error->error(mensaje: 'Error obtener secciones del paquete', data: $secciones);
            print_r($error);
            die('Error');
        }

        $this->secciones = $secciones;


        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            $error = $this->error->error(mensaje: 'Error session_id esta vacio', data: $this->session_id);
            print_r($error);
            die('Error');
        }

        $links = $this->links(link: $link, registro_id: $registro_id);
        if(errores::$error){
            $error = $this->error->error(mensaje: 'Error al generar links', data: $links);
            print_r($error);
            die('Error');
        }

    }

    /**
     * Obtiene el menu id para se utilizado por GET en los links
     * @return int
     * @version 7.88.3
     */
    private function adm_menu_id(): int
    {
        $adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $adm_menu_id = (int)$_GET['adm_menu_id'];
        }
        return $adm_menu_id;
    }

    /**
     * Genera un link de alta
     * @param PDO $link
     * @param string $seccion Seccion en ejecucion
     * @return string|array
     * @version 0.14.0
     */
    private function alta(PDO $link,string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'alta',adm_seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $link_alta = '';
        if($tengo_permiso){
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $link_alta = "./index.php?seccion=$seccion&accion=alta&adm_menu_id=$adm_menu_id";
        }


        return $link_alta;
    }

    /**
     * Precarga un link alta bd
     * @param PDO $link
     * @param string $seccion Seccion a ejecutar
     * @return string|array
     * @version 0.158.34
     */
    private function alta_bd(PDO $link, string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'alta_bd', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $liga = '';
        if($tengo_permiso){
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $liga = "./index.php?seccion=$seccion&accion=alta_bd&adm_menu_id=$adm_menu_id";
        }
        return $liga;
    }

    private function altas(PDO $link): array|stdClass
    {

        $links = $this->links_sin_id(accion: 'alta', link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $links);
        }


        return $this->links;
    }

    private function altas_bd(PDO $link): array|stdClass
    {
        $links = $this->links_sin_id(accion: 'alta_bd', link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $links);
        }

        return $this->links;
    }

    /**
     * Asigna la seccion a controler via tabla
     * @param controler $controler Controlador en ejecucion
     * @return array|string
     * @version 8.4.0
     */
    private function asigna_seccion(controler $controler): array|string
    {
        $tabla = trim($controler->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        $tabla = $this->init_tabla(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar tabla',data:  $tabla);
        }

        $controler->seccion = $tabla;
        return $controler->seccion;
    }

    /**
     * @param string $accion Accion a asignar o generar link
     * @param PDO $link
     * @param int $registro_id Registro a aplicar identificador
     * @param string $seccion
     * @return array|stdClass
     */
    private function con_id(string $accion, PDO $link, int $registro_id, string $seccion): array|stdClass
    {
        $function = 'link_'.$accion;
        $link = $this->$function(registro_id: $registro_id, link: $link, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link de elimina bd', data: $link);
        }

        $init = $this->init_action(accion: $accion,link: $link,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
        }
        return $init;
    }

    private function data_link(string $accion, PDO $link, array $params, string $seccion)
    {
        $vars_get = $this->var_gets(params_get: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params get', data: $vars_get);
        }

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: $accion, adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $data = new stdClass();
        $data->vars_get = $vars_get;
        $data->tengo_permiso = $tengo_permiso;
        return $data;


    }

    private function elimina_bd(PDO $link, int $registro_id, string $seccion): string
    {

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'elimina_bd', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $liga = '';
        if($tengo_permiso){
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $liga = "./index.php?seccion=$seccion&accion=elimina_bd&registro_id=$registro_id&adm_menu_id=$adm_menu_id";
        }

        return $liga;
    }

    /**
     * @param PDO $link
     * @param int $registro_id Registro a integrar en el link href
     * @return array|stdClass
     */
    private function eliminas_bd(PDO $link, int $registro_id): array|stdClass
    {
        $init = $this->links_con_id(accion: 'elimina_bd', link: $link,registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
        }

        return $this->links;
    }

    private function genera_link_ancla(string $accion, int $registro_id, string $seccion, bool $tengo_permiso, string $vars_get)
    {
        $link_ancla = '';
        if($tengo_permiso) {
            $link_ancla = $this->link_ancla(accion: $accion,registro_id:  $registro_id,seccion:  $seccion,vars_get:  $vars_get);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
            }
        }
        return $link_ancla;

    }

    final public function genera_links(controler $controler): array|stdClass
    {
        $filtro['adm_seccion.descripcion']  = $controler->modelo->tabla;
        $acciones = (new adm_accion($controler->link))->filtro_and(columnas: array("adm_accion_descripcion"),
            filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones de la seccion',data:  $acciones);
        }

        $inits = $this->integra_links(acciones: $acciones,controler:  $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar links', data: $inits);
        }

        return $this->links;
    }

    private function get_datos_ancla(string $accion, PDO $link, array $params, string $seccion, bool $valida_permiso)
    {
        $data_link = $this->data_link(accion: $accion,link:  $link,params:  $params,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar data_link', data: $data_link);
        }
        $valida = $this->valida_permiso(accion: $accion,data_link:  $data_link,seccion:  $seccion,
            valida_permiso:  $valida_permiso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso', data: $valida);
        }

        return $data_link;

    }

    /**
     * Obtiene el link a ejecutar
     * @param string $seccion Seccion a ejecutar
     * @param string $accion Accion a integrar
     * @param bool $valida_error
     * @return array|string
     */
    final public function get_link(string $seccion, string $accion, bool $valida_error = false): array|string
    {
        if($valida_error) {
            if (!property_exists($this->links, $seccion)) {
                return $this->error->error(mensaje: 'Error no existe la seccion ' . $seccion, data: $seccion);
            }

            if (!property_exists($this->links->$seccion, $accion)) {
                return $this->error->error(mensaje: 'Error no existe la accion ' . $accion, data: $accion);
            }
        }
        else{
            if (!property_exists($this->links, $seccion)) {
                $this->links->$seccion = new stdClass();
            }

            if (!property_exists($this->links->$seccion, $accion)) {
                $this->links->$seccion->$accion = '';
            }
        }

        return $this->links->$seccion->$accion;
    }

    /**
     * Inicializa un link para generar una accion
     * @param string $accion Accion a asignar o generar link
     * @param string $link Link href con ruta
     * @param string $seccion Seccion a asignar link
     * @return stdClass|array
     * @version 0.10.5
     */
    private function init_action(string $accion, string $link, string $seccion): stdClass|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la accion esta vacia', data:$accion);
        }
        $link = trim($link);

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error $seccion esta vacia', data:$seccion);
        }
        if(!isset($this->links->$seccion)){
            $this->links->$seccion = new stdClass();
        }
        $this->links->$seccion->$accion =  $link;
        return $this->links;
    }

    /**
     * @param controler $controler
     * @param array $registro
     * @return array|stdClass
     */
    private function init_data_link(controler $controler, array $registro): array|stdClass
    {
        $seccion_rs = $this->seccion(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar seccion',data:  $seccion_rs);
        }
        $accion = $registro['adm_accion_descripcion'];
        $init = $this->link_init(link: $controler->link, seccion: $controler->seccion, accion: $accion,
            registro_id: $controler->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar links', data: $init);
        }
        return $init;
    }

    /**
     * REG
     * Inicializa los enlaces para el controlador y los asigna a las propiedades correspondientes.
     *
     * Esta función verifica la existencia de los enlaces asociados a una sección en el controlador y los inicializa si no están presentes.
     * Asigna los enlaces correspondientes (alta, alta_bd, elimina_bd, lista, descarga_excel, modifica, modifica_bd)
     * a las propiedades del controlador. Si la sección está vacía, devuelve un mensaje de error.
     *
     * @param system $controler El controlador en ejecución que contiene la sección actual y los enlaces a asignar.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con los enlaces inicializados para la sección,
     *                         o un array con el mensaje de error en caso de que la sección esté vacía o haya un problema.
     *
     * @example
     * ```php
     * $controler = new system();
     * $controler->seccion = 'usuario';
     * $links = $links_menu->init_link_controller($controler);
     *
     * // Ejemplo de salida esperada:
     * if (isset($links->usuario->alta)) {
     *     echo "El enlace de alta es: " . $links->usuario->alta;
     * }
     * ```
     *
     * En este ejemplo, si la sección del controlador es "usuario", los enlaces se asignan a las propiedades del controlador,
     * y podrás acceder a ellos como `$controler->link_alta`, `$controler->link_lista`, etc.
     *
     * @throws array Si la sección está vacía o si hay un error al asignar los enlaces.
     */
    final public function init_link_controller(system $controler): stdClass|array
    {
        // Se obtiene la sección del controlador
        $seccion = $controler->seccion;

        // Verifica si la sección está vacía
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        // Inicializa los enlaces de la sección si no existen
        if(!isset($this->links->$seccion)){
            $this->links->$seccion = new stdClass();
        }

        // Asigna valores predeterminados para los enlaces si no están definidos
        if(!isset($this->links->$seccion->alta)){
            $this->links->$seccion->alta = '';
        }
        if(!isset($this->links->$seccion->alta_bd)){
            $this->links->$seccion->alta_bd = '';
        }
        if(!isset($this->links->$seccion->elimina_bd)){
            $this->links->$seccion->elimina_bd = '';
        }
        if(!isset($this->links->$seccion->lista)){
            $this->links->$seccion->lista = '';
        }
        if(!isset($this->links->$seccion->descarga_excel)){
            $this->links->$seccion->descarga_excel = '';
        }
        if(!isset($this->links->$seccion->modifica)){
            $this->links->$seccion->modifica = '';
        }
        if(!isset($this->links->$seccion->modifica_bd)){
            $this->links->$seccion->modifica_bd = '';
        }

        // Asigna los enlaces inicializados a las propiedades del controlador
        $controler->link_alta = $this->links->$seccion->alta;
        $controler->link_alta_bd = $this->links->$seccion->alta_bd;
        $controler->link_elimina_bd = $this->links->$seccion->elimina_bd;
        $controler->link_lista = $this->links->$seccion->lista;
        $controler->link_descarga_excel = $this->links->$seccion->descarga_excel;
        $controler->link_modifica = $this->links->$seccion->modifica;
        $controler->link_modifica_bd = $this->links->$seccion->modifica_bd;

        // Retorna los enlaces inicializados
        return $this->links;
    }


    private function init_links(stdClass $acciones, controler $controler){
        $inits = array();
        foreach ($acciones->registros as $registro){
            $init = $this->init_data_link(controler: $controler,registro:  $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar links', data: $init);
            }
            $inits[] = $init;
        }
        return $inits;
    }

    /**
     * Inicializa el nombre de la tabla integrada en el constructor
     * @param controler $controler Controlador en ejecucion
     * @return string|array
     * @version 8.3.0
     */
    private function init_tabla(controler $controler): string|array
    {
        $tabla = trim($controler->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $tabla = $controler->tabla;
        return trim($tabla);
    }

    private function integra_link_ancla(string $accion, PDO $link, array $params, int $registro_id, string $seccion, bool $valida_permiso)
    {
        $data_link = $this->get_datos_ancla(accion: $accion,link:  $link,params:  $params,
            seccion:  $seccion,valida_permiso:  $valida_permiso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar data_link', data: $data_link);
        }
        $link_ancla = $this->genera_link_ancla(accion: $accion,registro_id:  $registro_id,seccion:  $seccion,
            tengo_permiso:  $data_link->tengo_permiso,vars_get:  $data_link->vars_get);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
        }
        return $link_ancla;

    }

    private function integra_links(stdClass $acciones, controler $controler){
        $inits = array();
        if ($acciones->n_registros > 0){
            $inits = $this->init_links(acciones: $acciones,controler:  $controler);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar links', data: $inits);
            }
        }
        return $inits;
    }

    private function liga(string $accion, int $registro_id, string $seccion, bool $tengo_permiso){
        $liga = '';
        if($tengo_permiso){
            $seccion = trim($seccion);
            if($seccion === ''){
                return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
            }
            $accion = trim($accion);
            if($accion === ''){
                return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion);
            }
            $liga = $this->liga_con_permiso(accion: $accion,registro_id:  $registro_id,seccion:  $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar liga', data: $liga);
            }
        }
        return $liga;
    }

    /**
     * Genera el link para uso de anclas
     * @param string $accion accion a llamar
     * @param int $adm_menu_id Menu a llamar
     * @param int $registro_id Registro a ejecutar transaccion
     * @param string $seccion Seccion a ejecutar
     * @return string|array
     * @version 8.6.0
     */
    private function liga_completa(string $accion, int $adm_menu_id, int $registro_id, string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion);
        }
        return "./index.php?seccion=$seccion&accion=$accion&registro_id=$registro_id&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
    }

    private function liga_con_permiso(string $accion, int $registro_id, string $seccion){
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion);
        }

        $adm_menu_id = $this->adm_menu_id();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_menu_id', data: $adm_menu_id);
        }

        $liga = $this->liga_completa(accion: $accion,adm_menu_id:  $adm_menu_id,registro_id:  $registro_id, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar liga', data: $liga);
        }
        return $liga;
    }

    /**
     * Genera un link basado en datos de controler
     * @param string $accion Accion a ejecutar
     * @param PDO $link Conexion a base de datos
     * @param int $registro_id Registro en proceso
     * @param string $seccion Seccion a ejecutar
     * @return string|array
     *
     */
    private function link(string $accion, PDO $link, int $registro_id, string $seccion): string|array
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: $accion, adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $liga = $this->liga(accion: $accion,registro_id:  $registro_id,seccion:  $seccion,tengo_permiso:  $tengo_permiso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar liga', data: $liga);
        }

        return $liga;
    }

    /**
     * Genera un link de tipo alta
     * @param PDO $link
     * @param string $seccion Seccion a inicializar el link
     * @return array|string
     * @version 0.18.1
     */
    public function link_alta(PDO $link, string $seccion): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }
        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }


        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'alta', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $alta = '';
        if($tengo_permiso){
            $alta = $this->alta( link: $link, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener link de alta', data: $alta);
            }
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $alta.="&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        }

        return $alta;
    }

    /**
     * Genera un link de tipo alta bd
     * @param PDO $link Conexion a la base de datos
     * @param string $seccion Seccion en ejecucion
     * @return array|string
     * @version 0.189.35
     */
    final public function link_alta_bd(PDO $link, string $seccion): array|string
    {
        $alta_bd = '';
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'alta_bd', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        if($tengo_permiso) {
            $alta_bd = $this->alta_bd(link: $link, seccion: $seccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener link de alta_bd', data: $alta_bd);
            }
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $alta_bd .= "&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        }
        return $alta_bd;
    }

    private function link_ancla(string $accion, int $registro_id, string $seccion, string $vars_get): string
    {
        $adm_menu_id = $this->adm_menu_id();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener menu id', data: $adm_menu_id);
        }

        $param_registro_id = '';
        if($registro_id > 0){
            $param_registro_id = "&registro_id=$registro_id";
        }
        $link_ancla = "./index.php?seccion=$seccion&accion=$accion$param_registro_id&adm_menu_id=$adm_menu_id";
        $link_ancla.="&session_id=$this->session_id$vars_get";
        return $link_ancla;

    }

    /**
     * Funcion que genera un link con un id definido para la ejecucion de una accion
     * @param string $accion Accion a ejecutar
     * @param PDO $link Conexion a la base de datos
     * @param int $registro_id Registro identificador
     * @param string $seccion Seccion de envio
     * @param array $params Parametros para integrar en GET
     * @param bool $valida_permiso Si valida retorna error si no tiene permiso
     * @return array|string
     * @version 0.81.32
     */
    final public function link_con_id(string $accion, PDO $link, int $registro_id, string $seccion,
                                array $params = array(), bool $valida_permiso = false): array|string
    {
        $accion = trim($accion);
        $seccion = trim($seccion);

        $valida = $this->valida_link(accion: $accion,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada de datos', data: $valida);
        }

        $link_ancla = $this->integra_link_ancla(accion: $accion,link:  $link,params:  $params,
            registro_id:  $registro_id,seccion:  $seccion,valida_permiso:  $valida_permiso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
        }


        return $link_ancla;
    }

    private function link_elimina_bd(PDO $link, int $registro_id, string $seccion): array|string
    {

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'alta_bd', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $elimina = '';
        if($tengo_permiso) {
            $elimina = $this->elimina_bd(link: $link, registro_id: $registro_id, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener link de elimina', data: $elimina);
            }
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $elimina.="&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        }


        return $elimina;
    }

    /**
     * Inicializa un link para uso general
     * @param PDO $link Conexion a base de datos
     * @param string $seccion Seccion en ejecucion
     * @param string $accion Accion en ejecucion
     * @param int $registro_id Registro a integrar link
     * @return array|stdClass
     */
    private function link_init(PDO $link, string $seccion, string $accion,int $registro_id): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){

            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $link = $this->link(accion: $accion, link: $link, registro_id: $registro_id, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        $init = $this->init_action(accion: $accion,link: $link,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar link', data: $init);
        }

        return $init;
    }

    private function link_lista(PDO $link, string $seccion): array|string
    {
        $lista_cstp = $this->lista(link: $link, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link de lista', data: $lista_cstp);
        }

        $adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $adm_menu_id = $_GET['adm_menu_id'];
        }

        $lista_cstp.="&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        return $lista_cstp;
    }

    private function link_descarga_excel(PDO $link, string $seccion): array|string
    {
        $lista_cstp = $this->descarga_excel(link: $link, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link de lista', data: $lista_cstp);
        }

        $adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $adm_menu_id = $_GET['adm_menu_id'];
        }

        $lista_cstp.="&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        return $lista_cstp;
    }


    private function link_modifica(PDO $link, int $registro_id, string $seccion): array|string
    {


        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'modifica', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }


        $modifica = '';
        if($tengo_permiso){
            $modifica = $this->modifica(link: $link, registro_id: $registro_id, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener link de modifica', data: $modifica);
            }
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $modifica.="&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        }


        return $modifica;
    }

    private function link_modifica_bd(PDO $link, int $registro_id, string $seccion): array|string
    {

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'modifica_bd', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $modifica = '';
        if($tengo_permiso) {
            $modifica = $this->modifica_bd(link: $link, registro_id: $registro_id, seccion: $seccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener link de modifica_bd', data: $modifica);
            }
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $modifica .= "&session_id=$this->session_id&adm_menu_id=$adm_menu_id";
        }
        return $modifica;
    }

    final public function link_sin_id(string $accion, PDO $link, string $seccion, array $params = array(), bool $valida_permiso = false): array|string
    {
        $accion = trim($accion);
        $seccion = trim($seccion);

        $valida = $this->valida_link(accion: $accion,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada de datos', data: $valida);
        }


        $link_ancla = $this->integra_link_ancla(accion: $accion,link:  $link,params:  $params,registro_id:  -1,seccion:  $seccion,valida_permiso:  $valida_permiso);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
        }


        return $link_ancla;
    }

    /**
     * @param PDO $link
     * @param int $registro_id Registro a integrar en el link href
     * @return stdClass|array
     */
    protected function links(PDO $link, int $registro_id): stdClass|array
    {
        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }

        $listas  = $this->listas(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar listas', data: $listas);
        }

        $descarga_excel  = $this->descargas_excel(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar descarga excel', data: $descarga_excel);
        }

        $modificas  = $this->modificas(link: $link, registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modificas', data: $modificas);
        }
        $altas  = $this->altas(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar altas', data: $altas);
        }

        $altas_bd  = $this->altas_bd(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar altas bd', data: $altas_bd);
        }

        $modificas_bd  = $this->modificas_bd(link: $link, registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modificas bd', data: $modificas_bd);
        }

        $eliminas_bd  = $this->eliminas_bd(link: $link, registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar eliminas bd', data: $eliminas_bd);
        }

        $adm_menu_id = -1;
        if(isset($_GET['adm_menu_id'])){
            $adm_menu_id = $_GET['adm_menu_id'];
        }
        $this->links->adm_session = new stdClass();
        $this->links->adm_session->inicio = "./index.php?seccion=adm_session&accion=inicio&adm_menu_id=$adm_menu_id";
        $this->links->adm_session->inicio.="&session_id=$this->session_id";

        $this->links->adm_session->logout = "./index.php?seccion=adm_session&accion=logout";
        $this->links->adm_session->logout.="&session_id=$this->session_id";

        return $this->links;
    }

    /**
     * @param string $accion Accion a asignar o generar link
     * @param PDO $link
     * @param int $registro_id Registro a aplicar identificador
     * @return array|stdClass
     */
    private function links_con_id(string $accion, PDO $link, int $registro_id): array|stdClass
    {
        foreach ($this->secciones as $seccion){

            $init = $this->con_id(accion: $accion, link: $link,registro_id: $registro_id,seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
            }

        }
        return $this->links;
    }

    /**
     * Genera los links sin ID
     * @param string $accion Accion a integrar
     * @param PDO $link
     * @return array|stdClass
     * @version 0.157.33
     */
    private function links_sin_id(string $accion, PDO $link): array|stdClass
    {

        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }
        foreach ($this->secciones as $seccion){

            $init = $this->sin_id(accion: $accion, link: $link, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
            }
        }
        return $this->links;
    }

    /**
     * Genera un link de tipo lista validando el permiso de acceso
     * @param PDO $link Conexion a la base de datos
     * @param string $seccion Seccion del link
     * @return string|array
     * @version 3.3.1
     */
    private function lista(pdo $link, string $seccion): string|array
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'lista', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $lista = '';
        if($tengo_permiso) {
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $lista = "./index.php?seccion=$seccion&accion=lista&adm_menu_id=$adm_menu_id";
        }

        return $lista;
    }

    private function descarga_excel(pdo $link, string $seccion): string|array
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'descarga_excel', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $lista = '';
        if($tengo_permiso) {
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $lista = "./index.php?seccion=$seccion&accion=descarga_excel&adm_menu_id=$adm_menu_id";
        }

        return $lista;
    }

    /** Genera los links de una lista sin id
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     */
    private function listas(PDO $link): array|stdClass
    {

        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }

        $links = $this->links_sin_id(accion: 'lista', link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $links);
        }

        return $this->links;

    }

    private function descargas_excel(PDO $link): array|stdClass
    {

        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }

        $links = $this->links_sin_id(accion: 'descarga_excel', link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $links);
        }

        return $this->links;

    }
    private function modifica(PDO $link, int $registro_id, string $seccion): string|array
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data:$seccion);
        }

        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'modifica', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        $liga = '';
        if($tengo_permiso){
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $liga = "./index.php?seccion=$seccion&accion=modifica&registro_id=$registro_id&adm_menu_id=$adm_menu_id";
        }

        return $liga;
    }

    private function modifica_bd(PDO $link, int $registro_id, string $seccion): string
    {
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: 'lista', adm_seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }
        $modifica_bd = '';
        if($tengo_permiso) {
            $adm_menu_id = -1;
            if(isset($_GET['adm_menu_id'])){
                $adm_menu_id = $_GET['adm_menu_id'];
            }
            $modifica_bd = "./index.php?seccion=$seccion&accion=modifica_bd&registro_id=$registro_id&adm_menu_id=$adm_menu_id";
        }

        return $modifica_bd;
    }

    private function modificas(PDO $link, int $registro_id): array|stdClass
    {

        $init = $this->links_con_id(accion: 'modifica', link: $link,registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
        }
        return $this->links;
    }

    private function modificas_bd(PDO $link, int $registro_id): array|stdClass
    {

        $init = $this->links_con_id(accion: 'modifica_bd', link: $link,registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
        }
        return $this->links;
    }

    /**
     * Inicializa la seccion de una entidad controller
     * @param controler $controler Controlador en ejecucion
     * @return array|string
     * @version 8.5.0
     */
    private function seccion(controler $controler): array|string
    {
        $seccion = trim($controler->seccion);
        if($seccion === ''){
            $seccion_rs = $this->asigna_seccion(controler: $controler);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar seccion',data:  $seccion_rs);
            }
        }
        return $controler->seccion;
    }

    /**
     * Genera los parametros de in link sin registro_id
     * @param string $seccion Seccion en ejecucion o llamada
     * @param string $accion Accion a generar link
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.25.5
     */
    private function sin_id(string $accion, PDO $link, string $seccion,): array|stdClass
    {

        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error la $accion esta vacia', data: $accion);
        }

        $this->session_id = trim($this->session_id);
        if($this->session_id === ''){
            return $this->error->error(mensaje: 'Error links_menu->session_id esta vacio', data: $this->session_id);
        }

        $function_link = 'link_'.$accion;


        $link_accion = $this->$function_link(seccion: $seccion, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener link de '.$accion, data: $link_accion);
        }

        $init = $this->init_action(accion: $accion, link: $link_accion, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializa link', data: $init);
        }

        return $init;

    }

    /**
     * TOTAL
     * Valida si la acción y la sección no están vacías.
     *
     * Esta función verifica que los valores de acción y sección no estén vacíos.
     * Si ambos valores están presentes, devuelve verdadero; de lo contrario,
     * devuelve un mensaje de error indicando qué valor está vacío.
     *
     * @param string $accion La acción a validar.
     * @param string $seccion La sección a validar.
     *
     * @return true|array Retorna true si tanto la acción como la sección no están vacías.
     *                     Retorna un array con un mensaje de error si uno de los valores está vacío.
     *
     * @example
     * ```php
     * $validador = new links_menu();
     * $accion = "alta";
     * $seccion = "contrato";
     * $resultado = $validador->valida_link($accion, $seccion);
     * if ($resultado === true) {
     *     echo "Los valores de acción y sección son válidos.";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     * @url https://github.com/gamboamartin/system/wiki/src.links_menu.valida_link.22.5.0
     */
    private function valida_link(string $accion, string $seccion): true|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error al accion esta vacia', data: $accion, es_final: true);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error al $seccion esta vacia', data: $seccion, es_final: true);
        }
        return true;

    }

    private function valida_permiso(string $accion,stdClass $data_link, string $seccion, bool $valida_permiso): true|array
    {
        if($valida_permiso){
            if(!$data_link->tengo_permiso){
                return $this->error->error(mensaje: 'Error permiso denegado '.$seccion.' '.$accion,
                    data: $data_link->tengo_permiso);
            }
        }
        return true;

    }

    /**
     * TOTAL
     * Construye una cadena de consulta GET a partir de un array de parámetros GET.
     *
     * @param array $params_get Un array asociativo donde las claves son los nombres de las variables GET y los valores
     * son los valores asociados.
     *
     * @return string|array Retorna una cadena de consulta GET si todos los parámetros son válidos. Si hay algún error,
     * retorna un array con un mensaje de error indicando el problema encontrado.
     * @url https://github.com/gamboamartin/system/wiki/src.links_menu.var_gets.22.5.0
     */
    private function var_gets(array $params_get): string|array
    {
        $vars_get = '';
        foreach ($params_get as $var=>$value){
            $var = trim($var);
            if($var === ''){
                return $this->error->error(mensaje: 'Error var esta vacio', data: $params_get, es_final: true);
            }
            $value = trim($value);
            if($value === ''){
                return $this->error->error(mensaje: 'Error value esta vacio', data: $params_get, es_final: true);
            }
            $vars_get.="&$var=$value";
        }
        return $vars_get;

    }
}
