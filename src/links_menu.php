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
     * REG
     * Obtiene el ID del menú de administración desde los parámetros GET.
     *
     * Este método busca el parámetro `adm_menu_id` en la URL (`$_GET`).
     * Si el parámetro existe, lo convierte a un entero y lo devuelve.
     * Si no existe, devuelve `-1` como valor por defecto.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $menu_id = $links_menu->adm_menu_id();
     * echo "ID del menú: " . $menu_id;
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: `adm_menu_id` presente en la URL**
     * ```php
     * $_GET['adm_menu_id'] = "5";
     * ```
     * **Salida esperada:**
     * ```php
     * 5
     * ```
     *
     * **Caso 2: `adm_menu_id` no está en la URL**
     * ```php
     * unset($_GET['adm_menu_id']);
     * ```
     * **Salida esperada:**
     * ```php
     * -1
     * ```
     *
     * **Caso 3: `adm_menu_id` con un valor no numérico**
     * ```php
     * $_GET['adm_menu_id'] = "abc";
     * ```
     * **Salida esperada:**
     * ```php
     * -1
     * ```
     *
     * @return int Retorna el ID del menú (`adm_menu_id`) si está definido en la URL.
     *             Si no está presente, retorna `-1` como valor por defecto.
     */
    private function adm_menu_id(): int
    {
        $adm_menu_id = -1;
        if (isset($_GET['adm_menu_id'])) {
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
     * REG
     * Genera un enlace para la acción `alta_bd` en una sección si el usuario tiene permiso.
     *
     * Este método verifica si el usuario tiene permiso para realizar la acción `alta_bd` en la sección especificada.
     * Si el usuario tiene permiso, genera una URL con los parámetros necesarios para realizar la acción.
     *
     * Validaciones:
     * - Si la sección está vacía, devuelve un error.
     * - Verifica si el usuario tiene permiso mediante la clase `adm_usuario`.
     * - Si ocurre un error en la validación de permisos, devuelve un error.
     * - Si el usuario tiene permiso, genera la URL con los parámetros adecuados.
     * - Si `adm_menu_id` está presente en `$_GET`, se incluye en la URL generada.
     *
     * @param PDO    $link    Conexión activa a la base de datos mediante PDO.
     * @param string $seccion Nombre de la sección donde se ejecutará la acción `alta_bd`.
     *
     * @return string|array Si el usuario tiene permiso, devuelve la URL generada.
     *                      Si hay errores, devuelve un array con el mensaje de error.
     *
     * @example Uso correcto con permiso:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'productos';
     * $_GET['adm_menu_id'] = 5;
     * echo $this->alta_bd($pdo, $seccion);
     * // Salida esperada: "./index.php?seccion=productos&accion=alta_bd&adm_menu_id=5"
     * ```
     *
     * @example Uso correcto sin `adm_menu_id`:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'usuarios';
     * echo $this->alta_bd($pdo, $seccion);
     * // Salida esperada: "./index.php?seccion=usuarios&accion=alta_bd&adm_menu_id=-1"
     * ```
     *
     * @example Error: Sección vacía:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * print_r($this->alta_bd($pdo, ''));
     * // Salida esperada:
     * // Array (
     * //     [error] => Error seccion esta vacia
     * // )
     * ```
     *
     * @example Error en validación de permisos:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'facturas';
     * print_r($this->alta_bd($pdo, $seccion));
     * // Salida esperada si el usuario no tiene permiso:
     * // Array (
     * //     [error] => Error al validar si tengo permiso
     * // )
     * ```
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
     * REG
     * Asigna la sección a un controlador a partir de su tabla.
     *
     * Esta función toma el nombre de la tabla asociada al controlador y lo asigna como su sección.
     * Primero, valida que el nombre de la tabla no esté vacío. Luego, inicializa la tabla usando `init_tabla`
     * y finalmente asigna el valor al atributo `seccion` del controlador.
     *
     * Si la tabla está vacía o se produce un error en la inicialización, la función devuelve un mensaje de error.
     * De lo contrario, retorna el nombre de la sección correctamente asignada.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controlador = new controler();
     * $controlador->tabla = "productos";
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->asigna_seccion($controlador);
     *
     * if (is_array($resultado)) {
     *     echo "Error: " . $resultado['mensaje']; // Manejo de error
     * } else {
     *     echo "Sección asignada: " . $resultado;
     * }
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada válida:**
     * ```php
     * $controler->tabla = "usuarios";
     * ```
     * **Salida esperada:**
     * ```php
     * "usuarios"
     * ```
     *
     * **Entrada con espacios:**
     * ```php
     * $controler->tabla = "  clientes  ";
     * ```
     * **Salida esperada:**
     * ```php
     * "clientes"
     * ```
     *
     * **Entrada con tabla vacía (Error):**
     * ```php
     * $controler->tabla = "";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Error en la inicialización de la tabla:**
     * ```php
     * $controler->tabla = "ordenes";
     * // Supongamos que `init_tabla` falla por alguna razón.
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al inicializar tabla',
     *     'data' => 'ordenes'
     * ]
     * ```
     *
     * @param controler $controler Instancia del controlador en ejecución, que contiene la tabla a asignar como sección.
     *
     * @return array|string Retorna el nombre de la sección asignada si es válido.
     *                      Retorna un array con un mensaje de error si la tabla está vacía o si falla la inicialización.
     *
     * @throws array Si la tabla está vacía o hay un error en la inicialización, devuelve un array con el mensaje de error.
     */
    private function asigna_seccion(controler $controler): array|string
    {
        $tabla = trim($controler->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        $tabla = $this->init_tabla(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar tabla', data: $tabla);
        }

        $controler->seccion = $tabla;
        return $controler->seccion;
    }


    /**
     * REG
     * Genera un enlace de acción basado en un ID de registro y lo asigna a la estructura de enlaces.
     *
     * Este método genera un enlace dinámico para una acción específica dentro de una sección,
     * utilizando un identificador de registro. Valida que los parámetros sean correctos,
     * obtiene el enlace llamando a la función correspondiente (`link_{accion}`) y lo inicializa en la estructura `links`.
     *
     * ### Comportamiento:
     * - Valida que la acción y la sección no estén vacías.
     * - Construye el enlace utilizando un método dinámico basado en el nombre de la acción.
     * - Inicializa el enlace en la estructura `links` del objeto.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->con_id("modifica", $pdo, 5, "productos");
     * print_r($resultado);
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Acción y sección válidas**
     * ```php
     * $accion = "modifica";
     * $link = $pdo;
     * $registro_id = 5;
     * $seccion = "productos";
     * ```
     * **Salida esperada:**
     * ```php
     * stdClass Object
     * (
     *     [productos] => stdClass Object
     *         (
     *             [modifica] => index.php?seccion=productos&accion=modifica&registro_id=5&session_id=xyz
     *         )
     * )
     * ```
     *
     * **Caso 2: Acción vacía**
     * ```php
     * $accion = "";
     * $registro_id = 5;
     * $seccion = "usuarios";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: Sección vacía**
     * ```php
     * $accion = "elimina";
     * $registro_id = 10;
     * $seccion = "";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @param string $accion La acción a ejecutar en el enlace.
     * @param PDO $link Conexión a la base de datos.
     * @param int $registro_id Identificador del registro al que se aplicará la acción.
     * @param string $seccion La sección en la que se ejecutará la acción.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con el enlace generado si los parámetros son válidos.
     *                        Retorna un array con un mensaje de error si la acción o la sección están vacías.
     */
    private function con_id(string $accion, PDO $link, int $registro_id, string $seccion): array|stdClass
    {
        // Elimina espacios en blanco de la acción y valida que no esté vacía
        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion, es_final: true);
        }

        // Elimina espacios en blanco de la sección y valida que no esté vacía
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion, es_final: true);
        }

        // Construye dinámicamente el nombre del método que genera el enlace de la acción
        $function = 'link_' . $accion;

        // Llama al método generado dinámicamente para obtener el enlace de la acción
        $link = $this->$function(registro_id: $registro_id, link: $link, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener link de ' . $accion, data: $link);
        }

        // Inicializa la acción en la estructura de enlaces
        $init = $this->init_action(accion: $accion, link: $link, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar link', data: $init);
        }

        return $init;
    }


    /**
     * REG
     * Genera y valida los datos necesarios para construir un enlace.
     *
     * Esta función realiza las siguientes acciones:
     * 1. Genera los parámetros GET en base al array `$params` usando el método `var_gets()`.
     * 2. Verifica si el usuario tiene permiso para ejecutar una acción en una sección determinada
     *    utilizando la clase `adm_usuario`.
     * 3. Retorna un objeto `stdClass` con los parámetros GET generados y el resultado de la validación de permisos.
     *
     * Si ocurre algún error en la generación de los parámetros GET o en la validación de permisos,
     * la función retorna un array con los detalles del error.
     *
     * @param string $accion  La acción a validar. Debe ser una cadena no vacía.
     * @param PDO    $link    Conexión a la base de datos para ejecutar validaciones.
     * @param array  $params  Parámetros a incluir en la URL, estructurados en un array asociativo.
     * @param string $seccion La sección del sistema donde se ejecutará la acción.
     *
     * @return stdClass|array Retorna un objeto con dos propiedades:
     *                        - `vars_get`: los parámetros GET generados.
     *                        - `tengo_permiso`: el resultado de la validación de permisos.
     *                        En caso de error, retorna un array con los detalles del error.
     *
     * @example
     * ```php
     * $params = ['id' => 123, 'modulo' => 'ventas'];
     * $resultado = $this->data_link('crear', $pdo, $params, 'usuarios');
     * // Salida esperada (si tiene permisos y no hay errores):
     * // stdClass Object (
     * //     [vars_get] => Array (
     * //         [id] => 123
     * //         [modulo] => 'ventas'
     * //     )
     * //     [tengo_permiso] => true
     * // )
     *
     * $params = ['id' => 456];
     * $resultado = $this->data_link('eliminar', $pdo, $params, 'facturas');
     * // Salida esperada (si el usuario no tiene permiso):
     * // Array (
     * //     [error] => true
     * //     [mensaje] => 'Error al validar si tengo permiso'
     * //     [data] => false
     * // )
     *
     * $params = ['id' => null];
     * $resultado = $this->data_link('actualizar', $pdo, $params, 'productos');
     * // Salida esperada (si ocurre un error en la generación de parámetros GET):
     * // Array (
     * //     [error] => true
     * //     [mensaje] => 'Error al generar params get'
     * //     [data] => null
     * // )
     * ```
     */
    private function data_link(string $accion, PDO $link, array $params, string $seccion): array|stdClass
    {
        // Genera los parámetros GET
        $vars_get = $this->var_gets(params_get: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar params get', data: $vars_get);
        }

        // Verifica si el usuario tiene permiso para la acción y sección indicadas
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: $accion, adm_seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        // Crea y retorna el objeto con los datos generados
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

    /**
     * Genera un enlace (URL) solo si el usuario tiene permisos para la acción y la sección especificadas.
     *
     * Esta función evalúa si el usuario tiene permiso para ejecutar una acción en una determinada
     * sección y, en caso afirmativo, genera un enlace utilizando `link_ancla()`. Si el usuario no
     * tiene permisos, retorna una cadena vacía.
     *
     * **Flujo de la función:**
     * 1. Inicializa la variable `$link_ancla` como una cadena vacía.
     * 2. Verifica si el usuario tiene permisos (`$tengo_permiso`).
     * 3. Si tiene permisos, llama a `link_ancla()` para construir el enlace.
     * 4. Si hay un error en `link_ancla()`, retorna un mensaje de error con los detalles.
     * 5. Retorna la URL generada o una cadena vacía si no hay permisos.
     *
     * @param string $accion La acción a ejecutar en la URL (ejemplo: 'editar', 'eliminar', 'ver').
     * @param int $registro_id ID del registro sobre el cual se realizará la acción (0 si no aplica).
     * @param string $seccion La sección del sistema donde se ejecutará la acción (ejemplo: 'clientes', 'facturas').
     * @param bool $tengo_permiso Indica si el usuario tiene permiso para ejecutar la acción.
     * @param string $vars_get Variables adicionales para la URL en formato de `query string` (ejemplo: '&page=1&sort=desc').
     *
     * @return string|array Retorna la URL generada si el usuario tiene permiso; de lo contrario, retorna una cadena vacía.
     *
     * @example
     * ```php
     * // Usuario con permisos para editar el usuario con ID 15
     * $link = $this->genera_link_ancla('editar', 15, 'usuarios', true, '&pagina=2');
     *
     * // Salida esperada:
     * // "./index.php?seccion=usuarios&accion=editar&registro_id=15&adm_menu_id=3&session_id=xyz123&pagina=2"
     *
     * // Usuario sin permisos para eliminar el usuario con ID 20
     * $link = $this->genera_link_ancla('eliminar', 20, 'usuarios', false, '');
     *
     * // Salida esperada:
     * // ""
     *
     * // Usuario con permisos para ver reportes sin ID de registro
     * $link = $this->genera_link_ancla('ver', 0, 'reportes', true, '');
     *
     * // Salida esperada:
     * // "./index.php?seccion=reportes&accion=ver&adm_menu_id=3&session_id=xyz123"
     * ```
     */
    private function genera_link_ancla(
        string $accion,
        int $registro_id,
        string $seccion,
        bool $tengo_permiso,
        string $vars_get
    ): string|array
    {
        // Inicializa el enlace vacío
        $link_ancla = '';

        // Solo genera el enlace si el usuario tiene permisos
        if ($tengo_permiso) {
            $link_ancla = $this->link_ancla(
                accion: $accion,
                registro_id: $registro_id,
                seccion: $seccion,
                vars_get: $vars_get
            );

            // Si ocurre un error en link_ancla(), retorna un mensaje de error
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
            }
        }

        // Retorna la URL generada o una cadena vacía si no hay permisos
        return $link_ancla;
    }


    /**
     * REG
     * Genera los enlaces de acciones disponibles para una sección específica del controlador.
     *
     * Este método obtiene todas las acciones permitidas para la sección correspondiente al controlador,
     * y luego integra los enlaces utilizando `integra_links()`. Si hay un error en la obtención de acciones
     * o en la integración de enlaces, devuelve un mensaje de error.
     *
     * ---
     *
     * ### Pasos del método:
     * 1. **Obtener las acciones**: Se filtran las acciones asociadas a la sección actual del controlador.
     * 2. **Validar errores**: Si ocurre un error al obtener las acciones, se retorna un mensaje de error.
     * 3. **Integrar los enlaces**: Se llama a `integra_links()` para generar los enlaces a partir de las acciones.
     * 4. **Validar errores en la integración**: Si hay un problema en `integra_links()`, se retorna un error.
     * 5. **Devolver los enlaces generados**: Se retorna el objeto `$this->links` con los enlaces de la sección.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controler = new controler();
     * $controler->seccion = "usuarios";
     * $controler->modelo = new modelo();
     * $controler->modelo->tabla = "usuarios";
     * $controler->link = $pdo;
     *
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->genera_links($controler);
     * print_r($resultado);
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación exitosa de enlaces**
     * ```php
     * $controler->modelo->tabla = "usuarios";
     * ```
     * **Salida esperada (ejemplo de estructura de enlaces generados):**
     * ```php
     * stdClass Object
     * (
     *     [usuarios] => stdClass Object
     *         (
     *             [alta] => "./index.php?seccion=usuarios&accion=alta&registro_id=10&session_id=xyz"
     *             [modifica] => "./index.php?seccion=usuarios&accion=modifica&registro_id=10&session_id=xyz"
     *             [elimina_bd] => "./index.php?seccion=usuarios&accion=elimina_bd&registro_id=10&session_id=xyz"
     *         )
     * )
     * ```
     *
     * ---
     *
     * **Caso 2: No hay acciones registradas**
     * ```php
     * $controler->modelo->tabla = "clientes";
     * ```
     * **Salida esperada (enlaces vacíos):**
     * ```php
     * stdClass Object ()
     * ```
     *
     * ---
     *
     * **Caso 3: Error al obtener las acciones**
     * ```php
     * // Simulación de error en la obtención de acciones
     * $controler->modelo->tabla = "ordenes";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al obtener acciones de la seccion',
     *     'data' => [...]
     * ]
     * ```
     *
     * ---
     *
     * @param controler $controler Instancia del controlador que contiene la sección y el modelo con la tabla.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con los enlaces generados si es exitoso.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la obtención de acciones o en la integración de enlaces.
     */
    final public function genera_links(controler $controler): array|stdClass
    {
        // Se filtran las acciones asociadas a la sección del controlador
        $filtro['adm_seccion.descripcion'] = $controler->modelo->tabla;

        // Obtiene las acciones disponibles en la base de datos
        $acciones = (new adm_accion($controler->link))->filtro_and(
            columnas: array("adm_accion_descripcion"),
            filtro: $filtro
        );

        // Si hay un error al obtener las acciones, retorna un mensaje de error
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener acciones de la seccion',
                data: $acciones
            );
        }

        // Integra los enlaces basados en las acciones obtenidas
        $inits = $this->integra_links(acciones: $acciones, controler: $controler);

        // Si hay un error en la integración de enlaces, retorna un mensaje de error
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar links',
                data: $inits
            );
        }

        // Retorna los enlaces generados
        return $this->links;
    }


    /**
     * REG
     * Obtiene y valida los datos de anclaje para la generación de enlaces con permisos.
     *
     * Esta función combina la obtención de los datos necesarios para un enlace (`data_link`)
     * con la validación de permisos (`valida_permiso`).
     *
     * **Flujo de la función:**
     * 1. Llama a `data_link()` para obtener la información de la acción y su contexto.
     * 2. Si hay un error al generar `data_link`, retorna un error.
     * 3. Llama a `valida_permiso()` para verificar si el usuario tiene permiso para ejecutar la acción.
     * 4. Si hay un error en la validación del permiso, retorna un error.
     * 5. Si todo es válido, retorna el objeto `data_link` con la información necesaria.
     *
     * @param string   $accion         La acción que se desea validar (ejemplo: 'crear', 'editar', 'eliminar').
     * @param PDO      $link           Conexión activa a la base de datos.
     * @param array    $params         Parámetros adicionales para la generación del enlace.
     * @param string   $seccion        La sección del sistema en la que se ejecutará la acción (ejemplo: 'usuarios', 'facturas').
     * @param bool     $valida_permiso Indica si se debe validar el permiso (`true`) o no (`false`).
     *
     * @return stdClass|array Retorna un objeto `stdClass` con los datos del enlace si todo es válido.
     *                        En caso de error, retorna un array con detalles del error.
     *
     * @example
     * ```php
     * $link = new PDO('mysql:host=localhost;dbname=sistema', 'usuario', 'contraseña');
     * $params = ['id' => 10, 'token' => 'abc123'];
     *
     * // Ejemplo con validación de permisos activada
     * $data_ancla = $this->get_datos_ancla('editar', $link, $params, 'usuarios', true);
     *
     * // Salida esperada si el usuario tiene permisos:
     * // stdClass Object (
     * //     [vars_get] => Array (
     * //         [id] => 10
     * //         [token] => 'abc123'
     * //     )
     * //     [tengo_permiso] => true
     * // )
     *
     * // Ejemplo sin permisos
     * $data_ancla = $this->get_datos_ancla('eliminar', $link, $params, 'facturas', true);
     * // Salida esperada si no tiene permisos:
     * // Array (
     * //     [error] => true
     * //     [mensaje] => 'Error permiso denegado facturas eliminar'
     * //     [data] => false
     * //     [es_final] => true
     * // )
     *
     * // Ejemplo sin validación de permisos
     * $data_ancla = $this->get_datos_ancla('ver', $link, $params, 'reportes', false);
     * // Salida esperada:
     * // stdClass Object (
     * //     [vars_get] => Array (
     * //         [id] => 10
     * //         [token] => 'abc123'
     * //     )
     * //     [tengo_permiso] => true // No se valida, se asigna por defecto
     * // )
     * ```
     */
    private function get_datos_ancla(
        string $accion,
        PDO $link,
        array $params,
        string $seccion,
        bool $valida_permiso
    ): array|stdClass
    {
        // Obtiene los datos del enlace
        $data_link = $this->data_link(accion: $accion, link: $link, params: $params, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar data_link', data: $data_link);
        }

        // Valida los permisos del usuario si es necesario
        $valida = $this->valida_permiso(
            accion: $accion,
            data_link: $data_link,
            seccion: $seccion,
            valida_permiso: $valida_permiso
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar permiso', data: $valida);
        }

        // Retorna la información del enlace
        return $data_link;
    }


    /**
     * REG
     * Obtiene el enlace almacenado en `$links` para una sección y acción específica.
     *
     * Este método se encarga de recuperar la URL asociada a una acción dentro de una sección. Si el enlace no existe,
     * puede generar un error o inicializarlo con un valor vacío dependiendo del valor de `$valida_error`.
     *
     * Comportamiento según `$valida_error`:
     * - Si es `true`, valida que la sección y la acción existan en `$links`. Si no existen, devuelve un error.
     * - Si es `false`, crea la sección y la acción si no existen, asignando un valor vacío (`''`).
     *
     * @param string $seccion Nombre de la sección en `$links`.
     * @param string $accion Nombre de la acción dentro de la sección.
     * @param bool $valida_error Indica si se debe validar la existencia de la sección y la acción.
     *
     * @return array|string Devuelve el enlace almacenado en `$links->$seccion->$accion`.
     *                      En caso de error, devuelve un array con detalles del error.
     *
     * @example Uso básico sin validación de errores:
     * ```php
     * $links_menu = new links_menu();
     * $links_menu->links = new stdClass();
     * $links_menu->links->usuarios = new stdClass();
     * $links_menu->links->usuarios->crear = 'https://miapp.com/usuarios/crear';
     *
     * // Obtener un enlace existente sin validar errores
     * echo $links_menu->get_link('usuarios', 'crear'); // Salida: https://miapp.com/usuarios/crear
     *
     * // Obtener un enlace inexistente sin validar errores (se inicializa vacío)
     * echo $links_menu->get_link('productos', 'editar'); // Salida: ''
     * ```
     *
     * @example Uso con validación de errores:
     * ```php
     * $links_menu = new links_menu();
     * $links_menu->links = new stdClass();
     *
     * // Obtener un enlace inexistente con validación de errores
     * print_r($links_menu->get_link('productos', 'eliminar', true));
     * // Salida:
     * // Array (
     * //     [error] => Error no existe la sección productos
     * // )
     * ```
     *
     * @example Agregar enlaces y luego obtenerlos:
     * ```php
     * $links_menu = new links_menu();
     * $links_menu->links = new stdClass();
     *
     * // Se agregan enlaces manualmente
     * $links_menu->links->productos = new stdClass();
     * $links_menu->links->productos->ver = 'https://miapp.com/productos/ver';
     *
     * echo $links_menu->get_link('productos', 'ver'); // Salida: https://miapp.com/productos/ver
     * ```
     */
    final public function get_link(string $seccion, string $accion, bool $valida_error = false): array|string
    {
        if ($valida_error) {
            if (!property_exists($this->links, $seccion)) {
                return $this->error->error(mensaje: 'Error no existe la seccion ' . $seccion, data: $seccion);
            }

            if (!property_exists($this->links->$seccion, $accion)) {
                return $this->error->error(mensaje: 'Error no existe la accion ' . $accion, data: $accion);
            }
        } else {
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
     * REG
     * Inicializa un enlace de acción dentro de una sección específica.
     *
     * Este método asigna un enlace (`URL`) a una acción dentro de una sección en la propiedad `links`.
     * Si la sección aún no ha sido definida, se inicializa como un objeto `stdClass`.
     *
     * ### Comportamiento:
     * - Se valida que la acción y la sección no estén vacías.
     * - Se asigna el enlace a la acción correspondiente dentro de la sección.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->init_action("modifica", "index.php?seccion=productos&accion=modifica", "productos");
     * print_r($resultado);
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Acción y sección válidas**
     * ```php
     * $accion = "modifica";
     * $link = "index.php?seccion=productos&accion=modifica";
     * $seccion = "productos";
     * ```
     * **Salida esperada:**
     * ```php
     * stdClass Object
     * (
     *     [productos] => stdClass Object
     *         (
     *             [modifica] => index.php?seccion=productos&accion=modifica
     *         )
     * )
     * ```
     *
     * **Caso 2: Acción vacía**
     * ```php
     * $accion = "";
     * $link = "index.php?seccion=usuarios&accion=modifica";
     * $seccion = "usuarios";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error la accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: Sección vacía**
     * ```php
     * $accion = "elimina";
     * $link = "index.php?seccion=usuarios&accion=elimina";
     * $seccion = "";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @param string $accion La acción que se ejecutará en la URL.
     * @param string $link La URL de la acción.
     * @param string $seccion La sección en la que se ejecutará la acción.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con la estructura de enlaces si los parámetros son válidos.
     *                        Retorna un array con un mensaje de error si la acción o la sección están vacías.
     */
    private function init_action(string $accion, string $link, string $seccion): stdClass|array
    {
        // Elimina espacios en blanco de la acción
        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error la accion esta vacia', data: $accion);
        }

        // Elimina espacios en blanco del enlace
        $link = trim($link);

        // Elimina espacios en blanco de la sección
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }

        // Si la sección aún no está definida en links, la inicializa como un objeto vacío
        if (!isset($this->links->$seccion)) {
            $this->links->$seccion = new stdClass();
        }

        // Asigna el enlace a la acción dentro de la sección
        $this->links->$seccion->$accion = $link;

        return $this->links;
    }


    /**
     * REG
     * Inicializa los enlaces para una acción específica basada en un registro de permisos.
     *
     * Este método toma un registro con información de permisos (`adm_accion_descripcion`),
     * valida su existencia y lo usa para generar un enlace dentro de la sección actual del controlador.
     *
     * ### Pasos del método:
     * 1. Verifica que el campo `adm_accion_descripcion` esté presente en el registro.
     * 2. Asegura que `adm_accion_descripcion` no esté vacío.
     * 3. Obtiene y valida la sección del controlador mediante `seccion()`.
     * 4. Genera el enlace correspondiente usando `link_init()`.
     * 5. Retorna un `stdClass` con el enlace generado o un array con un error si algo falla.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controler = new controler();
     * $controler->seccion = "productos";
     * $controler->registro_id = 5;
     * $registro = ["adm_accion_descripcion" => "modifica"];
     *
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->init_data_link($controler, $registro);
     * print_r($resultado);
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación exitosa de enlace**
     * ```php
     * $registro = ["adm_accion_descripcion" => "modifica"];
     * ```
     * **Salida esperada (`$this->links` con el enlace generado):**
     * ```php
     * stdClass Object
     * (
     *     [productos] => stdClass Object
     *         (
     *             [modifica] => "./index.php?seccion=productos&accion=modifica&registro_id=5&session_id=xyz"
     *         )
     * )
     * ```
     *
     * ---
     *
     * **Caso 2: Error - Falta el campo `adm_accion_descripcion`**
     * ```php
     * $registro = [];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => '$registro[adm_accion_descripcion] no existe',
     *     'data' => []
     * ]
     * ```
     *
     * ---
     *
     * **Caso 3: Error - `adm_accion_descripcion` vacío**
     * ```php
     * $registro = ["adm_accion_descripcion" => ""];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => '$registro[adm_accion_descripcion] esta vacia',
     *     'data' => ["adm_accion_descripcion" => ""]
     * ]
     * ```
     *
     * ---
     *
     * **Caso 4: Error - Problema al inicializar la sección**
     * ```php
     * // Supongamos que `seccion()` devuelve un error.
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al inicializar seccion',
     *     'data' => [detalle del error]
     * ]
     * ```
     *
     * ---
     *
     * **Caso 5: Error - Problema al generar el enlace**
     * ```php
     * // Supongamos que `link_init()` devuelve un error.
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al inicializar links',
     *     'data' => [detalle del error]
     * ]
     * ```
     *
     * ---
     *
     * @param controler $controler Instancia del controlador con los datos de la sección y el registro.
     * @param array $registro Registro que contiene `adm_accion_descripcion`, la acción a inicializar.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con el enlace generado si es exitoso.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la validación o generación del enlace.
     */
    private function init_data_link(controler $controler, array $registro): array|stdClass
    {
        // Verifica que el campo 'adm_accion_descripcion' exista en el registro
        if (!isset($registro['adm_accion_descripcion'])) {
            return $this->error->error(
                mensaje: '$registro[adm_accion_descripcion] no existe',
                data: $registro,
                es_final: true
            );
        }

        // Verifica que el campo 'adm_accion_descripcion' no esté vacío
        if (trim($registro['adm_accion_descripcion']) === '') {
            return $this->error->error(
                mensaje: '$registro[adm_accion_descripcion] esta vacia',
                data: $registro,
                es_final: true
            );
        }

        // Obtiene y valida la sección del controlador
        $seccion_rs = $this->seccion(controler: $controler);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar seccion',
                data: $seccion_rs
            );
        }

        // Genera el enlace usando el valor de 'adm_accion_descripcion'
        $accion = $registro['adm_accion_descripcion'];
        $init = $this->link_init(
            link: $controler->link,
            seccion: $controler->seccion,
            accion: $accion,
            registro_id: $controler->registro_id
        );

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializar links',
                data: $init
            );
        }

        // Retorna el objeto `$this->links` con el enlace generado
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


    /**
     * REG
     * Inicializa los enlaces de múltiples acciones para una sección del controlador.
     *
     * Este método recorre la lista de registros de acciones contenida en `$acciones->registros`
     * y genera un enlace para cada una de ellas usando `init_data_link()`. Si ocurre un error
     * durante la inicialización de cualquier enlace, devuelve un array con un mensaje de error.
     *
     * ---
     *
     * ### Pasos del método:
     * 1. Inicializa un array `$inits` para almacenar los enlaces generados.
     * 2. Itera sobre la lista de registros (`$acciones->registros`).
     * 3. Para cada registro, llama a `init_data_link()` para inicializar el enlace.
     * 4. Si ocurre un error, devuelve un array con el mensaje de error.
     * 5. Retorna un array con todos los enlaces generados exitosamente.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controler = new controler();
     * $controler->seccion = "productos";
     * $controler->registro_id = 5;
     *
     * $acciones = new stdClass();
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => "modifica"],
     *     ["adm_accion_descripcion" => "elimina_bd"]
     * ];
     *
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->init_links($acciones, $controler);
     * print_r($resultado);
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación exitosa de enlaces**
     * ```php
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => "modifica"],
     *     ["adm_accion_descripcion" => "elimina_bd"]
     * ];
     * ```
     * **Salida esperada (array de enlaces generados):**
     * ```php
     * [
     *     stdClass Object
     *     (
     *         [productos] => stdClass Object
     *             (
     *                 [modifica] => "./index.php?seccion=productos&accion=modifica&registro_id=5&session_id=xyz"
     *             )
     *     ),
     *     stdClass Object
     *     (
     *         [productos] => stdClass Object
     *             (
     *                 [elimina_bd] => "./index.php?seccion=productos&accion=elimina_bd&registro_id=5&session_id=xyz"
     *             )
     *     )
     * ]
     * ```
     *
     * ---
     *
     * **Caso 2: Error - `adm_accion_descripcion` falta en un registro**
     * ```php
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => "modifica"],
     *     [] // Falta el campo requerido
     * ];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => '$registro[adm_accion_descripcion] no existe',
     *     'data' => []
     * ]
     * ```
     *
     * ---
     *
     * **Caso 3: Error - `adm_accion_descripcion` vacío**
     * ```php
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => ""]
     * ];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => '$registro[adm_accion_descripcion] esta vacia',
     *     'data' => ["adm_accion_descripcion" => ""]
     * ]
     * ```
     *
     * ---
     *
     * @param stdClass $acciones Objeto que contiene la lista de registros con acciones a inicializar.
     * @param controler $controler Instancia del controlador con los datos de la sección y el registro.
     *
     * @return array|stdClass Retorna un array con los enlaces generados si es exitoso.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la inicialización.
     */
    private function init_links(stdClass $acciones, controler $controler): array|stdClass
    {
        $inits = array();

        // Itera sobre los registros de acciones
        foreach ($acciones->registros as $registro) {
            // Inicializa el enlace para cada acción
            $init = $this->init_data_link(controler: $controler, registro: $registro);

            // Verifica si ocurrió un error
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al inicializar links',
                    data: $init
                );
            }

            // Agrega el enlace generado a la lista
            $inits[] = $init;
        }

        return $inits;
    }


    /**
     * REG
     * Inicializa y valida el nombre de la tabla integrada en el controlador.
     *
     * Esta función obtiene y valida el nombre de la tabla asociada a un controlador (`controler`).
     * Si la tabla está vacía, devuelve un mensaje de error.
     * En caso contrario, retorna el nombre de la tabla después de eliminar los espacios en blanco adicionales.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controlador = new controler();
     * $controlador->tabla = "usuarios";
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->init_tabla($controlador);
     *
     * if (is_array($resultado)) {
     *     echo "Error: " . $resultado['mensaje']; // Manejo de error
     * } else {
     *     echo "Nombre de la tabla: " . $resultado;
     * }
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada:**
     * ```php
     * $controler->tabla = "productos";
     * ```
     * **Salida esperada:**
     * ```php
     * "productos"
     * ```
     *
     * **Entrada:**
     * ```php
     * $controler->tabla = "   clientes   ";
     * ```
     * **Salida esperada:**
     * ```php
     * "clientes"
     * ```
     *
     * **Entrada (Error - tabla vacía):**
     * ```php
     * $controler->tabla = "";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @param controler $controler Controlador en ejecución que contiene la tabla a validar.
     *
     * @return string|array Retorna el nombre de la tabla si es válido.
     *                      Retorna un array con un mensaje de error si la tabla está vacía.
     *
     * @throws array Si la tabla está vacía, devuelve un array con un mensaje de error detallado.
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


    /**
     * REG
     * Genera un enlace (URL) basado en los permisos del usuario y los datos obtenidos para la acción y sección especificadas.
     *
     * Esta función integra la obtención de datos de ancla (`get_datos_ancla`) y la generación del enlace (`genera_link_ancla`).
     * Se encarga de validar si el usuario tiene permiso para ejecutar la acción en la sección correspondiente.
     * Si hay algún error en el proceso, retorna un mensaje de error con los detalles.
     *
     * **Flujo de la función:**
     * 1. Llama a `get_datos_ancla()` para obtener datos como permisos y parámetros GET.
     * 2. Si ocurre un error en `get_datos_ancla()`, retorna un mensaje de error.
     * 3. Llama a `genera_link_ancla()` para construir el enlace con los datos obtenidos.
     * 4. Si `genera_link_ancla()` falla, retorna un mensaje de error.
     * 5. Retorna el enlace generado correctamente.
     *
     * @param string  $accion         La acción a ejecutar en la URL (ejemplo: 'editar', 'eliminar', 'ver').
     * @param PDO     $link           Conexión a la base de datos utilizada para validar permisos.
     * @param array   $params         Parámetros adicionales para la URL en formato clave-valor.
     * @param int     $registro_id    ID del registro sobre el cual se realizará la acción (0 si no aplica).
     * @param string  $seccion        La sección del sistema donde se ejecutará la acción (ejemplo: 'clientes', 'facturas').
     * @param bool    $valida_permiso Indica si se debe validar el permiso del usuario antes de generar el enlace.
     *
     * @return string|array Retorna la URL generada si el usuario tiene permiso; de lo contrario, retorna un array con el error.
     *
     * @example
     * ```php
     * // Generar un enlace para editar un cliente con ID 10, validando permisos
     * $link = $this->integra_link_ancla('editar', $link, [], 10, 'clientes', true);
     *
     * // Salida esperada si el usuario tiene permiso:
     * // "./index.php?seccion=clientes&accion=editar&registro_id=10&adm_menu_id=3&session_id=xyz123"
     *
     * // Salida esperada si el usuario no tiene permiso:
     * // [ 'error' => true, 'mensaje' => 'Error permiso denegado clientes editar', 'data' => false ]
     *
     * // Generar un enlace sin validación de permisos
     * $link = $this->integra_link_ancla('ver', $link, ['page' => 2], 0, 'reportes', false);
     *
     * // Salida esperada:
     * // "./index.php?seccion=reportes&accion=ver&adm_menu_id=3&session_id=xyz123&page=2"
     * ```
     */
    private function integra_link_ancla(
        string $accion,
        PDO $link,
        array $params,
        int $registro_id,
        string $seccion,
        bool $valida_permiso
    ): array|string
    {
        // Obtiene los datos necesarios para el enlace, como permisos y parámetros GET
        $data_link = $this->get_datos_ancla(
            accion: $accion,
            link: $link,
            params: $params,
            seccion: $seccion,
            valida_permiso: $valida_permiso
        );

        // Si hay un error en la obtención de datos, retorna un mensaje de error
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar data_link', data: $data_link);
        }

        // Genera el enlace con los datos obtenidos
        $link_ancla = $this->genera_link_ancla(
            accion: $accion,
            registro_id: $registro_id,
            seccion: $seccion,
            tengo_permiso: $data_link->tengo_permiso,
            vars_get: $data_link->vars_get
        );

        // Si hay un error al generar el enlace, retorna un mensaje de error
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
        }

        // Retorna la URL generada correctamente
        return $link_ancla;
    }


    /**
     * REG
     * Integra y genera los enlaces de acciones de un controlador si existen registros.
     *
     * Este método valida si existen registros (`n_registros > 0`) en el objeto `$acciones` y, en caso afirmativo,
     * procede a inicializar los enlaces llamando a `init_links()`. Si no hay registros, retorna un array vacío.
     * Si ocurre un error en la generación de los enlaces, devuelve un mensaje de error con los detalles del problema.
     *
     * ---
     *
     * ### Pasos del método:
     * 1. Inicializa un array `$inits` vacío.
     * 2. Verifica si hay registros (`$acciones->n_registros > 0`).
     * 3. Si hay registros, llama a `init_links()` para inicializar los enlaces.
     * 4. Si ocurre un error, devuelve un mensaje de error.
     * 5. Retorna el array con los enlaces generados o un array vacío si no hay registros.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controler = new controler();
     * $controler->seccion = "usuarios";
     * $controler->registro_id = 10;
     *
     * $acciones = new stdClass();
     * $acciones->n_registros = 2;
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => "modifica"],
     *     ["adm_accion_descripcion" => "elimina_bd"]
     * ];
     *
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->integra_links($acciones, $controler);
     * print_r($resultado);
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación exitosa de enlaces**
     * ```php
     * $acciones->n_registros = 2;
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => "modifica"],
     *     ["adm_accion_descripcion" => "elimina_bd"]
     * ];
     * ```
     * **Salida esperada (array de enlaces generados):**
     * ```php
     * [
     *     stdClass Object
     *     (
     *         [usuarios] => stdClass Object
     *             (
     *                 [modifica] => "./index.php?seccion=usuarios&accion=modifica&registro_id=10&session_id=xyz"
     *             )
     *     ),
     *     stdClass Object
     *     (
     *         [usuarios] => stdClass Object
     *             (
     *                 [elimina_bd] => "./index.php?seccion=usuarios&accion=elimina_bd&registro_id=10&session_id=xyz"
     *             )
     *     )
     * ]
     * ```
     *
     * ---
     *
     * **Caso 2: No hay registros (`n_registros = 0`)**
     * ```php
     * $acciones->n_registros = 0;
     * ```
     * **Salida esperada:**
     * ```php
     * []
     * ```
     *
     * ---
     *
     * **Caso 3: Error en la inicialización de enlaces**
     * ```php
     * $acciones->n_registros = 2;
     * $acciones->registros = [
     *     ["adm_accion_descripcion" => ""]
     * ];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al inicializar links',
     *     'data' => [...]
     * ]
     * ```
     *
     * ---
     *
     * @param stdClass $acciones Objeto que contiene la cantidad de registros (`n_registros`) y la lista de acciones.
     * @param controler $controler Instancia del controlador con la sección y el registro a procesar.
     *
     * @return array|stdClass Retorna un array con los enlaces generados si es exitoso.
     *                        Retorna un array vacío si no hay registros.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la inicialización.
     */
    private function integra_links(stdClass $acciones, controler $controler): array|stdClass
    {
        $inits = array();

        // Verifica si hay registros de acciones para procesar
        if ($acciones->n_registros > 0) {
            // Llama a init_links para generar los enlaces
            $inits = $this->init_links(acciones: $acciones, controler: $controler);

            // Verifica si ocurrió un error en la generación de enlaces
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al inicializar links',
                    data: $inits
                );
            }
        }

        return $inits;
    }


    /**
     * REG
     * Genera un enlace validando los permisos y los parámetros requeridos.
     *
     * Este método construye un enlace basado en la acción, el ID del registro y la sección.
     * Antes de generar la URL, valida que los valores de `seccion` y `accion` no estén vacíos.
     * Si el usuario tiene permiso (`$tengo_permiso`), se genera la URL utilizando `liga_con_permiso()`.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $liga = $links_menu->liga("modifica", 15, "productos", true);
     * echo $liga;
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Parámetros válidos y permiso concedido**
     * ```php
     * $accion = "modifica";
     * $registro_id = 15;
     * $seccion = "productos";
     * $tengo_permiso = true;
     * ```
     * **Salida esperada:**
     * ```php
     * "./index.php?seccion=productos&accion=modifica&registro_id=15&session_id=xyz123&adm_menu_id=3"
     * ```
     *
     * **Caso 2: `$tengo_permiso` es falso**
     * ```php
     * $accion = "modifica";
     * $registro_id = 20;
     * $seccion = "usuarios";
     * $tengo_permiso = false;
     * ```
     * **Salida esperada:**
     * ```php
     * ""
     * ```
     *
     * **Caso 3: `seccion` vacía**
     * ```php
     * $accion = "alta";
     * $registro_id = 10;
     * $seccion = "";
     * $tengo_permiso = true;
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 4: `accion` vacía**
     * ```php
     * $accion = "";
     * $registro_id = 5;
     * $seccion = "clientes";
     * $tengo_permiso = true;
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @param string $accion Acción a ejecutar en la URL.
     * @param int $registro_id ID del registro en la base de datos.
     * @param string $seccion Sección a la que pertenece la acción.
     * @param bool $tengo_permiso Indica si el usuario tiene permisos para acceder a la acción.
     *
     * @return string|array Retorna la URL generada si los parámetros son válidos y el usuario tiene permisos.
     *                      Retorna un array con un mensaje de error si `seccion` o `accion` están vacíos.
     *                      Retorna una cadena vacía si `$tengo_permiso` es `false`.
     */
    private function liga(string $accion, int $registro_id, string $seccion, bool $tengo_permiso): array|string
    {
        $liga = '';

        // Solo se genera la URL si el usuario tiene permisos
        if ($tengo_permiso) {
            $seccion = trim($seccion);
            if ($seccion === '') {
                return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
            }

            $accion = trim($accion);
            if ($accion === '') {
                return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion);
            }

            $liga = $this->liga_con_permiso(accion: $accion, registro_id: $registro_id, seccion: $seccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar liga', data: $liga);
            }
        }

        return $liga;
    }


    /**
     * REG
     * Genera una URL completa con los parámetros necesarios para ejecutar una acción en una sección específica.
     *
     * Este método construye un enlace con los parámetros esenciales (`seccion`, `accion`, `registro_id`, `session_id`,
     * `adm_menu_id`) para navegar dentro del sistema. Valida que los parámetros `seccion` y `accion` no estén vacíos.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $liga = $links_menu->liga_completa("modifica", 3, 25, "usuarios");
     * echo $liga;
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Parámetros válidos**
     * ```php
     * $accion = "modifica";
     * $adm_menu_id = 3;
     * $registro_id = 25;
     * $seccion = "usuarios";
     * ```
     * **Salida esperada:**
     * ```php
     * "./index.php?seccion=usuarios&accion=modifica&registro_id=25&session_id=xyz123&adm_menu_id=3"
     * ```
     *
     * **Caso 2: `seccion` vacía**
     * ```php
     * $accion = "alta";
     * $adm_menu_id = 1;
     * $registro_id = 10;
     * $seccion = "";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: `accion` vacía**
     * ```php
     * $accion = "";
     * $adm_menu_id = 2;
     * $registro_id = 5;
     * $seccion = "productos";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * @param string $accion Acción que se ejecutará en la URL.
     * @param int $adm_menu_id ID del menú de administración.
     * @param int $registro_id ID del registro en la base de datos.
     * @param string $seccion Sección a la que pertenece la acción.
     *
     * @return string|array Retorna la URL completa si los parámetros son válidos.
     *                      Retorna un array con un mensaje de error si `seccion` o `accion` están vacíos.
     */
    private function liga_completa(string $accion, int $adm_menu_id, int $registro_id, string $seccion): string|array
    {
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }

        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion);
        }

        $seccion_g = "seccion=$seccion";
        $accion_g = "accion=$accion";
        $registro_id_g = "registro_id=$registro_id";
        $session_id_g = "session_id=$this->session_id";
        $menu_id_g = "adm_menu_id=$adm_menu_id";

        return "./index.php?$seccion_g&$accion_g&$registro_id_g&$session_id_g&$menu_id_g";
    }


    /**
     * REG
     * Genera un enlace validando los permisos de acceso y los parámetros requeridos.
     *
     * Este método construye una URL con los parámetros `seccion`, `accion`, `registro_id`, `session_id` y `adm_menu_id`.
     * Antes de generar la URL, valida que los valores de `seccion` y `accion` no estén vacíos.
     * También obtiene el `adm_menu_id` mediante el método `adm_menu_id()`. Si hay errores en el proceso, retorna
     * un mensaje de error con los detalles correspondientes.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $liga = $links_menu->liga_con_permiso("modifica", 12, "usuarios");
     * echo $liga;
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Parámetros válidos**
     * ```php
     * $accion = "modifica";
     * $registro_id = 12;
     * $seccion = "usuarios";
     * ```
     * **Salida esperada:**
     * ```php
     * "./index.php?seccion=usuarios&accion=modifica&registro_id=12&session_id=xyz123&adm_menu_id=3"
     * ```
     *
     * **Caso 2: `seccion` vacía**
     * ```php
     * $accion = "alta";
     * $registro_id = 5;
     * $seccion = "";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: `accion` vacía**
     * ```php
     * $accion = "";
     * $registro_id = 8;
     * $seccion = "productos";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 4: Error al obtener `adm_menu_id`**
     * ```php
     * $accion = "ver";
     * $registro_id = 3;
     * $seccion = "pedidos";
     * // Supongamos que `adm_menu_id()` genera un error.
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error al obtener adm_menu_id',
     *     'data' => -1
     * ]
     * ```
     *
     * @param string $accion Acción que se ejecutará en la URL.
     * @param int $registro_id ID del registro en la base de datos.
     * @param string $seccion Sección a la que pertenece la acción.
     *
     * @return string|array Retorna la URL completa si los parámetros son válidos.
     *                      Retorna un array con un mensaje de error si `seccion`, `accion` están vacíos o si `adm_menu_id` es inválido.
     */
    private function liga_con_permiso(string $accion, int $registro_id, string $seccion): array|string
    {
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion, es_final: true);
        }

        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error accion esta vacia', data: $accion, es_final: true);
        }

        $adm_menu_id = $this->adm_menu_id();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener adm_menu_id', data: $adm_menu_id);
        }

        $liga = $this->liga_completa(
            accion: $accion,
            adm_menu_id: $adm_menu_id,
            registro_id: $registro_id,
            seccion: $seccion
        );

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar liga', data: $liga);
        }

        return $liga;
    }


    /**
     * REG
     * Genera un enlace para ejecutar una acción en una sección específica, validando permisos.
     *
     * Este método construye un enlace (`URL`) para una acción dentro de una sección específica.
     * Antes de generar la URL, se validan las siguientes condiciones:
     * - Que la sección no esté vacía.
     * - Que el usuario tenga permisos para ejecutar la acción en la sección indicada.
     *
     * Si se cumplen los requisitos, se genera el enlace con la función `liga()`.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $enlace = $links_menu->link("modifica", $pdo, 15, "productos");
     * echo $enlace;
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Acción válida con permisos**
     * ```php
     * $accion = "modifica";
     * $registro_id = 15;
     * $seccion = "productos";
     * ```
     * **Salida esperada:**
     * ```php
     * "./index.php?seccion=productos&accion=modifica&registro_id=15&session_id=xyz123&adm_menu_id=3"
     * ```
     *
     * **Caso 2: Sección vacía**
     * ```php
     * $accion = "alta";
     * $registro_id = 10;
     * $seccion = "";
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: Usuario sin permisos**
     * ```php
     * $accion = "elimina_bd";
     * $registro_id = 20;
     * $seccion = "usuarios";
     * // El usuario no tiene permisos para eliminar registros en la sección "usuarios"
     * ```
     * **Salida esperada (error):**
     * ```php
     * [
     *     'mensaje' => 'Error al validar si tengo permiso',
     *     'data' => false
     * ]
     * ```
     *
     * @param string $accion La acción que se ejecutará en la URL.
     * @param PDO $link Conexión a la base de datos.
     * @param int $registro_id ID del registro al que se aplicará la acción.
     * @param string $seccion Sección en la que se ejecutará la acción.
     *
     * @return string|array Retorna la URL generada si los parámetros son válidos y el usuario tiene permisos.
     *                      Retorna un array con un mensaje de error si la sección está vacía o si el usuario no tiene permisos.
     */
    private function link(string $accion, PDO $link, int $registro_id, string $seccion): string|array
    {
        // Elimina espacios en blanco de la sección
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }
        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error $accion esta vacia', data: $accion);
        }

        // Verifica si el usuario tiene permisos para ejecutar la acción en la sección
        $tengo_permiso = (new adm_usuario(link: $link))->tengo_permiso(adm_accion: $accion, adm_seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si tengo permiso', data: $tengo_permiso);
        }

        // Genera la liga si el usuario tiene permisos
        $liga = $this->liga(accion: $accion, registro_id: $registro_id, seccion: $seccion,
            tengo_permiso: $tengo_permiso);
        if (errores::$error) {
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
     * REG
     * Genera un enlace para la acción `alta_bd` en una sección si el usuario tiene permiso.
     *
     * Este método verifica si el usuario tiene permiso para realizar la acción `alta_bd` en la sección especificada.
     * Si el usuario tiene permiso, genera una URL con los parámetros necesarios (`session_id`, `adm_menu_id`)
     * para ejecutar la acción correctamente.
     *
     * Validaciones:
     * - Verifica si el usuario tiene permiso mediante la clase `adm_usuario`.
     * - Si ocurre un error en la validación de permisos, devuelve un error.
     * - Si el usuario tiene permiso, obtiene la URL base usando `alta_bd()`.
     * - Si hay un error al obtener el enlace, devuelve un error.
     * - Añade los parámetros `session_id` y `adm_menu_id` a la URL generada.
     *
     * @param PDO    $link    Conexión activa a la base de datos mediante PDO.
     * @param string $seccion Nombre de la sección donde se ejecutará la acción `alta_bd`.
     *
     * @return string|array Si el usuario tiene permiso, devuelve la URL generada.
     *                      Si hay errores, devuelve un array con el mensaje de error.
     *
     * @example Uso correcto con permiso:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'productos';
     * $_GET['adm_menu_id'] = 5;
     * echo $this->link_alta_bd($pdo, $seccion);
     * // Salida esperada: "./index.php?seccion=productos&accion=alta_bd&adm_menu_id=5&session_id=123abc"
     * ```
     *
     * @example Uso correcto sin `adm_menu_id` en `$_GET`:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'usuarios';
     * echo $this->link_alta_bd($pdo, $seccion);
     * // Salida esperada: "./index.php?seccion=usuarios&accion=alta_bd&adm_menu_id=-1&session_id=123abc"
     * ```
     *
     * @example Error en validación de permisos:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'facturas';
     * print_r($this->link_alta_bd($pdo, $seccion));
     * // Salida esperada si el usuario no tiene permiso:
     * // Array (
     * //     [error] => Error al validar si tengo permiso
     * // )
     * ```
     *
     * @example Error al obtener la URL base:
     * ```php
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'usuario', 'password');
     * $seccion = 'ordenes';
     * print_r($this->link_alta_bd($pdo, $seccion));
     * // Salida esperada si hay error en `alta_bd`:
     * // Array (
     * //     [error] => Error al obtener link de alta_bd
     * // )
     * ```
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

    /**
     * REG
     * Genera un enlace (URL) con los parámetros adecuados para la navegación dentro del sistema.
     *
     * La función construye un link con la estructura necesaria para acceder a una acción
     * específica en una sección determinada, incluyendo el ID de registro, ID de menú y sesión.
     *
     * **Flujo de la función:**
     * 1. Obtiene el ID del menú activo llamando a `adm_menu_id()`.
     * 2. Si el `registro_id` es mayor a 0, lo agrega como parámetro en la URL.
     * 3. Valida y asigna los parámetros de `seccion`, `accion` y `adm_menu_id`.
     * 4. Construye la URL con los valores obtenidos, incluyendo el `session_id` y otros `vars_get`.
     * 5. Retorna la URL generada.
     *
     * @param string $accion La acción a ejecutar en la URL (ejemplo: 'editar', 'eliminar', 'ver').
     * @param int $registro_id ID del registro sobre el cual se realizará la acción (0 si no aplica).
     * @param string $seccion La sección del sistema donde se ejecutará la acción (ejemplo: 'clientes', 'facturas').
     * @param string $vars_get Variables adicionales para la URL en formato de `query string` (ejemplo: '&page=1&sort=desc').
     *
     * @return string|array Retorna la URL generada con los parámetros adecuados.
     *
     * @example
     * ```php
     * // Generar un enlace para editar un usuario con ID 15
     * $link = $this->link_ancla('editar', 15, 'usuarios', '&pagina=2');
     *
     * // Salida esperada:
     * // "./index.php?seccion=usuarios&accion=editar&registro_id=15&adm_menu_id=3&session_id=xyz123&pagina=2"
     *
     * // Generar un enlace para ver reportes sin ID de registro
     * $link = $this->link_ancla('ver', 0, 'reportes', '');
     *
     * // Salida esperada:
     * // "./index.php?seccion=reportes&accion=ver&adm_menu_id=3&session_id=xyz123"
     *
     * // Generar un enlace sin acción ni registro
     * $link = $this->link_ancla('', 0, '', '');
     *
     * // Salida esperada:
     * // "./index.php?adm_menu_id=3&session_id=xyz123"
     * ```
     */
    private function link_ancla(string $accion, int $registro_id, string $seccion, string $vars_get): string|array
    {
        // Obtiene el ID del menú activo
        $adm_menu_id = $this->adm_menu_id();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener menu id', data: $adm_menu_id);
        }

        // Parámetro del ID de registro si aplica
        $param_registro_id = '';
        if ($registro_id > 0) {
            $param_registro_id = "&registro_id=$registro_id";
        }

        // Parámetros de sección y acción (si existen)
        $seccion = trim($seccion);
        $seccion_p = $seccion !== '' ? "seccion=$seccion" : '';

        $accion = trim($accion);
        $accion_p = $accion !== '' ? "accion=$accion" : '';

        // Parámetro del ID de menú
        $adm_menu_id_p = "adm_menu_id=$adm_menu_id";

        // Construcción del enlace con los parámetros
        $link_ancla = "./index.php?$seccion_p&$accion_p$param_registro_id&$adm_menu_id_p";
        $link_ancla .= "&session_id=$this->session_id$vars_get";

        return $link_ancla;
    }


    /**
     * REG
     * Genera un enlace (URL) con un ID de registro, validando permisos opcionalmente.
     *
     * Esta función se encarga de:
     * 1. Validar la acción y la sección utilizando `valida_link()`.
     * 2. Si la validación falla, retorna un mensaje de error con los detalles.
     * 3. Integrar los datos necesarios para la URL mediante `integra_link_ancla()`.
     * 4. Si ocurre un error en la integración del enlace, retorna un mensaje de error.
     * 5. Devolver el enlace generado en caso de éxito.
     *
     * **Uso típico:** Se utiliza para construir enlaces dentro del sistema asegurando que la acción,
     * la sección y los permisos sean correctos antes de generar el enlace definitivo.
     *
     * @param string  $accion         La acción a ejecutar en la URL (ejemplo: 'editar', 'eliminar', 'ver').
     * @param PDO     $link           Conexión a la base de datos utilizada para validar permisos.
     * @param int     $registro_id    ID del registro sobre el cual se realizará la acción (0 si no aplica).
     * @param string  $seccion        La sección del sistema donde se ejecutará la acción (ejemplo: 'clientes', 'facturas').
     * @param array   $params         Parámetros adicionales para la URL en formato clave-valor (opcional, por defecto vacío).
     * @param bool    $valida_permiso Indica si se debe validar el permiso del usuario antes de generar el enlace (por defecto `false`).
     *
     * @return array|string Retorna la URL generada si todo es correcto; en caso de error, retorna un array con el detalle del error.
     *
     * @example
     * ```php
     * // Generar un enlace para editar un cliente con ID 10, validando permisos
     * $link = $this->link_con_id('editar', $link, 10, 'clientes', [], true);
     *
     * // Salida esperada si el usuario tiene permiso:
     * // "./index.php?seccion=clientes&accion=editar&registro_id=10&adm_menu_id=3&session_id=xyz123"
     *
     * // Salida esperada si el usuario no tiene permiso:
     * // [ 'error' => true, 'mensaje' => 'Error permiso denegado clientes editar', 'data' => false ]
     *
     * // Generar un enlace sin validación de permisos
     * $link = $this->link_con_id('ver', $link, 0, 'reportes', ['page' => 2], false);
     *
     * // Salida esperada:
     * // "./index.php?seccion=reportes&accion=ver&adm_menu_id=3&session_id=xyz123&page=2"
     * ```
     */
    final public function link_con_id(
        string $accion,
        PDO $link,
        int $registro_id,
        string $seccion,
        array $params = array(),
        bool $valida_permiso = false
    ): array|string {
        // Elimina espacios en blanco en los parámetros de entrada
        $accion = trim($accion);
        $seccion = trim($seccion);

        // Valida que la acción y la sección sean válidas
        $valida = $this->valida_link(accion: $accion, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar entrada de datos', data: $valida);
        }

        // Genera el enlace con la validación de permisos correspondiente
        $link_ancla = $this->integra_link_ancla(
            accion: $accion,
            link: $link,
            params: $params,
            registro_id: $registro_id,
            seccion: $seccion,
            valida_permiso: $valida_permiso
        );

        // Si hay un error al generar el enlace, retorna un mensaje de error
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener link', data: $link_ancla);
        }

        // Retorna el enlace generado correctamente
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
     * REG
     * Inicializa un enlace para una acción específica dentro de una sección.
     *
     * Este método genera un enlace para una acción determinada dentro de una sección específica,
     * validando que los parámetros sean correctos y asegurando que el enlace se cree adecuadamente.
     *
     * ### Pasos del método:
     * 1. Valida que la sección y la acción no estén vacías.
     * 2. Genera el enlace llamando al método `link()`, el cual verifica permisos y construye la URL.
     * 3. Asigna el enlace al objeto `$this->links` llamando a `init_action()`.
     * 4. Retorna el objeto `$this->links` con el enlace generado o un error en caso de fallo.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->link_init($pdo, "productos", "modifica", 10);
     * print_r($resultado);
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación exitosa de enlace**
     * ```php
     * $seccion = "productos";
     * $accion = "modifica";
     * $registro_id = 10;
     * ```
     * **Salida esperada (`$this->links` con el enlace generado):**
     * ```php
     * stdClass Object
     * (
     *     [productos] => stdClass Object
     *         (
     *             [modifica] => "./index.php?seccion=productos&accion=modifica&registro_id=10&session_id=xyz"
     *         )
     * )
     * ```
     *
     * **Caso 2: Error - Sección vacía**
     * ```php
     * $seccion = "";
     * $accion = "modifica";
     * $registro_id = 10;
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error seccion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: Error - Acción vacía**
     * ```php
     * $seccion = "productos";
     * $accion = "";
     * $registro_id = 10;
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error $accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 4: Error - Problema al generar el enlace**
     * ```php
     * // Si la función `link()` devuelve un error:
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al generar link',
     *     'data' => [detalles del error]
     * ]
     * ```
     *
     * @param PDO $link Conexión a la base de datos.
     * @param string $seccion Sección en la que se generará el enlace.
     * @param string $accion Acción específica dentro de la sección.
     * @param int $registro_id Identificador del registro al que se aplicará la acción.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con el enlace generado para la acción.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la generación del enlace.
     */
    private function link_init(PDO $link, string $seccion, string $accion, int $registro_id): array|stdClass
    {
        // Valida que la sección no esté vacía
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }

        // Valida que la acción no esté vacía
        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error $accion esta vacia', data: $accion);
        }

        // Genera el enlace validando permisos y construyendo la URL
        $link = $this->link(accion: $accion, link: $link, registro_id: $registro_id, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        // Asigna el enlace al objeto `$this->links`
        $init = $this->init_action(accion: $accion, link: $link, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar link', data: $init);
        }

        // Retorna el objeto `$this->links` con el enlace generado
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
     * REG
     * Genera enlaces con ID para una acción en todas las secciones disponibles.
     *
     * Este método recorre todas las secciones almacenadas en la propiedad `$this->secciones`
     * y genera un enlace para cada una de ellas llamando al método `con_id()`, el cual crea
     * un enlace basado en la acción y el ID del registro.
     *
     * Si ocurre algún error en el proceso, se devuelve un mensaje de error con los detalles.
     * En caso contrario, se retorna la estructura de enlaces generada.
     *
     * ### Comportamiento:
     * - Recorre todas las secciones registradas.
     * - Para cada sección, genera un enlace llamando a `con_id()`.
     * - Si ocurre un error, devuelve un mensaje de error.
     * - Retorna el objeto `links` con todos los enlaces generados.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->links_con_id("modifica", $pdo, 5);
     * print_r($resultado);
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Caso 1: Generación de enlaces exitosa**
     * ```php
     * $accion = "modifica";
     * $registro_id = 10;
     * ```
     * **Salida esperada (`$this->links` con enlaces generados para cada sección):**
     * ```php
     * stdClass Object
     * (
     *     [productos] => stdClass Object
     *         (
     *             [modifica] => index.php?seccion=productos&accion=modifica&registro_id=10&session_id=xyz
     *         )
     *     [usuarios] => stdClass Object
     *         (
     *             [modifica] => index.php?seccion=usuarios&accion=modifica&registro_id=10&session_id=xyz
     *         )
     * )
     * ```
     *
     * **Caso 2: Error en la generación de un enlace**
     * ```php
     * $accion = "";
     * $registro_id = 10;
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error accion esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Caso 3: Secciones vacías**
     * ```php
     * $this->secciones = [];
     * ```
     * **Salida esperada (`$this->links` sin cambios):**
     * ```php
     * stdClass Object
     * (
     * )
     * ```
     *
     * @param string $accion La acción para la cual se generarán enlaces.
     * @param PDO $link Conexión a la base de datos.
     * @param int $registro_id Identificador del registro al que se aplicará la acción.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con los enlaces generados para cada sección.
     *                        Retorna un array con un mensaje de error si ocurre un problema en la generación de enlaces.
     */
    private function links_con_id(string $accion, PDO $link, int $registro_id): array|stdClass
    {
        // Recorre todas las secciones registradas
        foreach ($this->secciones as $seccion) {
            // Genera un enlace para la acción en la sección actual
            $init = $this->con_id(accion: $accion, link: $link, registro_id: $registro_id, seccion: $seccion);

            // Si ocurre un error, retorna un mensaje de error con los detalles
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar link', data: $init);
            }
        }

        // Retorna la estructura de enlaces generada
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
     * REG
     * Obtiene y valida la sección de un controlador.
     *
     * Esta función devuelve el valor de la sección (`seccion`) del controlador. Si la sección está vacía,
     * intenta asignarla a partir de la tabla del controlador utilizando `asigna_seccion()`.
     * Si la asignación falla, devuelve un error. En caso contrario, retorna la sección validada.
     *
     * ### Ejemplo de Uso:
     * ```php
     * $controlador = new controler();
     * $controlador->tabla = "clientes"; // La sección será asignada automáticamente
     * $links_menu = new links_menu($pdo, 1);
     * $resultado = $links_menu->seccion($controlador);
     *
     * if (is_array($resultado)) {
     *     echo "Error: " . $resultado['mensaje']; // Manejo de error
     * } else {
     *     echo "Sección obtenida: " . $resultado;
     * }
     * ```
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada: Sección ya definida en el controlador**
     * ```php
     * $controler->seccion = "ordenes";
     * ```
     * **Salida esperada:**
     * ```php
     * "ordenes"
     * ```
     *
     * **Entrada: Sección vacía, pero tabla definida**
     * ```php
     * $controler->seccion = "";
     * $controler->tabla = "productos";
     * ```
     * **Salida esperada (sección asignada automáticamente):**
     * ```php
     * "productos"
     * ```
     *
     * **Entrada: Sección y tabla vacías (Error)**
     * ```php
     * $controler->seccion = "";
     * $controler->tabla = "";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Error en la asignación de sección:**
     * ```php
     * $controler->seccion = "";
     * $controler->tabla = "usuarios";
     * // Supongamos que `asigna_seccion` falla por alguna razón.
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al inicializar seccion',
     *     'data' => 'usuarios'
     * ]
     * ```
     *
     * @param controler $controler Instancia del controlador en ejecución.
     *
     * @return array|string Retorna el nombre de la sección si es válido.
     *                      Retorna un array con un mensaje de error si la sección o tabla están vacías.
     *
     * @throws array Si la tabla está vacía o hay un error al asignar la sección, devuelve un array con el mensaje de error.
     */
    private function seccion(controler $controler): array|string
    {
        $seccion = trim($controler->seccion);
        if($seccion === ''){
            $seccion_rs = $this->asigna_seccion(controler: $controler);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar seccion', data: $seccion_rs);
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
     * REG
     * Valida que los parámetros `$accion` y `$seccion` no estén vacíos.
     *
     * Esta función recibe dos cadenas de texto (`$accion` y `$seccion`), elimina espacios en blanco
     * al inicio y final de cada una, y verifica que no estén vacías. Si alguna de ellas está vacía,
     * se registra un error y se retorna un array con los detalles del error. En caso contrario,
     * la función retorna `true`.
     *
     * @param string $accion  La acción a validar. No debe estar vacía después de aplicar `trim()`.
     * @param string $seccion La sección a validar. No debe estar vacía después de aplicar `trim()`.
     *
     * @return true|array Retorna `true` si ambos parámetros son válidos. Si alguno está vacío,
     *                    retorna un array con los detalles del error.
     *
     * @example
     * ```php
     * $resultado = $this->valida_link('crear', 'usuario');
     * // Salida esperada:
     * // true
     *
     * $resultado = $this->valida_link(' ', 'usuario');
     * // Salida esperada:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error al accion esta vacia',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     *
     * $resultado = $this->valida_link('crear', '');
     * // Salida esperada:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error al seccion esta vacia',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     * ```
     */
    private function valida_link(string $accion, string $seccion): true|array
    {
        $accion = trim($accion);
        if ($accion === '') {
            return $this->error->error(mensaje: 'Error al accion esta vacia', data: $accion, es_final: true);
        }

        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error al seccion esta vacia', data: $seccion, es_final: true);
        }

        return true;
    }

    /**
     * REG
     * Valida si el usuario tiene permiso para ejecutar una acción en una sección específica.
     *
     * Esta función se encarga de verificar si el usuario tiene permiso para realizar una acción
     * dentro de una sección del sistema, basándose en los datos obtenidos de `data_link`.
     *
     * **Comportamiento de la función:**
     * 1. Si `$valida_permiso` es `false`, la función retorna `true` sin realizar validaciones.
     * 2. Si `$valida_permiso` es `true`, la función verifica si la propiedad `tengo_permiso`
     *    existe en el objeto `$data_link`. Si no existe, la inicializa en `false`.
     * 3. Si `tengo_permiso` es `false`, la función retorna un error indicando que el permiso
     *    ha sido denegado.
     * 4. Si `tengo_permiso` es `true`, la función retorna `true`, permitiendo la ejecución de la acción.
     *
     * @param string   $accion         La acción que se desea validar (ejemplo: 'crear', 'editar', 'eliminar').
     * @param stdClass $data_link      Objeto que contiene la información sobre los permisos, generado en `data_link()`.
     * @param string   $seccion        La sección en la que se desea ejecutar la acción (ejemplo: 'usuarios', 'facturas').
     * @param bool     $valida_permiso Si es `true`, se valida el permiso; si es `false`, se omite la validación y retorna `true`.
     *
     * @return true|array Retorna `true` si el permiso es válido o si la validación de permisos está deshabilitada.
     *                    En caso de error, retorna un array con detalles del error.
     *
     * @example
     * ```php
     * $data_link = new stdClass();
     * $data_link->tengo_permiso = true;
     *
     * $resultado = $this->valida_permiso('crear', $data_link, 'usuarios', true);
     * // Salida esperada:
     * // true
     *
     * $data_link = new stdClass();
     * $data_link->tengo_permiso = false;
     *
     * $resultado = $this->valida_permiso('eliminar', $data_link, 'facturas', true);
     * // Salida esperada:
     * // Array (
     * //     [error] => true
     * //     [mensaje] => 'Error permiso denegado facturas eliminar'
     * //     [data] => false
     * //     [es_final] => true
     * // )
     *
     * $data_link = new stdClass();
     * // Sin definir `tengo_permiso`, la función lo inicializa en `false`
     *
     * $resultado = $this->valida_permiso('actualizar', $data_link, 'productos', true);
     * // Salida esperada:
     * // Array (
     * //     [error] => true
     * //     [mensaje] => 'Error permiso denegado productos actualizar'
     * //     [data] => false
     * //     [es_final] => true
     * // )
     *
     * // Cuando `$valida_permiso` es `false`, no se realiza validación y siempre retorna `true`.
     * $resultado = $this->valida_permiso('ver', $data_link, 'reportes', false);
     * // Salida esperada:
     * // true
     * ```
     */
    private function valida_permiso(
        string $accion,
        stdClass $data_link,
        string $seccion,
        bool $valida_permiso
    ): true|array {
        if ($valida_permiso) {
            // Si no está definido, inicializa tengo_permiso en false
            if (!isset($data_link->tengo_permiso)) {
                $data_link->tengo_permiso = false;
            }
            // Si no tiene permiso, retorna error
            if (!$data_link->tengo_permiso) {
                return $this->error->error(
                    mensaje: 'Error permiso denegado ' . $seccion . ' ' . $accion,
                    data: $data_link->tengo_permiso,
                    es_final: true
                );
            }
        }
        return true;
    }


    /**
     * REG
     * Genera una cadena de parámetros GET a partir de un array asociativo.
     *
     * Este método toma un array asociativo donde las claves representan los nombres de los parámetros
     * y los valores representan sus respectivos valores. Luego, construye una cadena de consulta GET
     * concatenando cada par `clave=valor` con `&`.
     *
     * Validaciones:
     * - Si una clave (`var`) está vacía (`''`), devuelve un error.
     * - Si un valor (`value`) está vacío (`''`), devuelve un error.
     * - Todos los espacios en blanco en los nombres y valores se eliminan (`trim()`).
     *
     * @param array $params_get Un array asociativo con los parámetros GET a generar.
     *
     * @return string|array Devuelve una cadena con los parámetros GET formateados o un array con un error si hay valores vacíos.
     *
     * @example Uso correcto:
     * ```php
     * $params = ['usuario' => 'admin', 'id' => '123'];
     * $resultado = $this->var_gets($params);
     * echo $resultado;
     * // Salida esperada: "&usuario=admin&id=123"
     * ```
     *
     * @example Error: Clave vacía
     * ```php
     * $params = ['' => 'admin', 'id' => '123'];
     * print_r($this->var_gets($params));
     * // Salida esperada:
     * // Array (
     * //     [error] => Error var esta vacio
     * // )
     * ```
     *
     * @example Error: Valor vacío
     * ```php
     * $params = ['usuario' => '', 'id' => '123'];
     * print_r($this->var_gets($params));
     * // Salida esperada:
     * // Array (
     * //     [error] => Error value esta vacio
     * // )
     * ```
     */
    private function var_gets(array $params_get): string|array
    {
        $vars_get = '';

        foreach ($params_get as $var => $value) {
            $var = trim($var);
            if ($var === '') {
                return $this->error->error(mensaje: 'Error var esta vacio', data: $params_get, es_final: true);
            }

            $value = trim($value);
            if ($value === '') {
                return $this->error->error(mensaje: 'Error value esta vacio', data: $params_get, es_final: true);
            }

            $vars_get .= "&$var=$value";
        }

        return $vars_get;
    }

}
