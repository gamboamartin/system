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
     * REG
     * Inicializa el proceso de alta en la base de datos y define la vista siguiente.
     *
     * Esta función realiza dos acciones principales:
     * 1. **Determina la siguiente vista** después de completar la acción de alta.
     * 2. **Limpia los botones de la solicitud** eliminando ciertos valores de `$_POST`.
     *
     * Si ocurre un error en cualquiera de estos procesos, se retorna un mensaje de error.
     * De lo contrario, la función devuelve el nombre de la vista siguiente.
     *
     * ### Flujo de la función:
     * 1. Llama a `siguiente_view()` para obtener la vista siguiente.
     * 2. Si hay errores en la obtención de la vista, se devuelve un mensaje de error.
     * 3. Llama a `limpia_butons()` para eliminar botones no necesarios de `$_POST`.
     * 4. Si hay errores en la limpieza, se devuelve un mensaje de error.
     * 5. Finalmente, retorna el nombre de la vista siguiente.
     *
     * @param string $siguiente_view Vista por defecto a la que se redirigirá tras completar la acción.
     *                               Por defecto, su valor es `'modifica'`.
     *
     * @return array|string Retorna el nombre de la vista a la que se debe redirigir o un array con un error en caso de fallo.
     *
     * ### Ejemplos de entrada y salida:
     *
     * #### Ejemplo 1: Caso exitoso con vista por defecto
     * **Entrada:**
     * ```php
     * $_POST = ['nombre' => 'Ejemplo', 'btn_action_next' => 'detalle'];
     * $resultado = init_alta_bd();
     * ```
     * **Salida esperada:**
     * ```php
     * "detalle" // La vista definida en $_POST['btn_action_next']
     * ```
     *
     * #### Ejemplo 2: Caso exitoso con vista personalizada
     * **Entrada:**
     * ```php
     * $_POST = ['nombre' => 'Ejemplo'];
     * $resultado = init_alta_bd('alta');
     * ```
     * **Salida esperada:**
     * ```php
     * "alta" // Se usa la vista proporcionada como parámetro
     * ```
     *
     * #### Ejemplo 3: Error al obtener la vista siguiente
     * **Simulación de error en `siguiente_view()`:**
     * ```php
     * $_POST = ['btn_action_next' => ''];
     * $resultado = init_alta_bd();
     * ```
     * **Salida esperada (Error manejado):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al obtener siguiente view',
     *     'data' => ''
     * ]
     * ```
     *
     * #### Ejemplo 4: Error en la limpieza de botones
     * **Simulación de error en `limpia_butons()`:**
     * ```php
     * $_POST = ['btn_action_next' => null]; // Supongamos que la limpieza de botones genera un error
     * $resultado = init_alta_bd();
     * ```
     * **Salida esperada (Error manejado):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al limpiar botones',
     *     'data' => []
     * ]
     * ```
     *
     * ### Notas:
     * - Si la función `siguiente_view()` falla, se devuelve un error inmediatamente.
     * - Se limpia `$_POST` antes de continuar con el flujo normal de ejecución.
     * - Es fundamental manejar los posibles errores antes de usar el valor de retorno.
     *
     * @throws errores Si falla la obtención de la vista siguiente o la limpieza de botones.
     * @version 18.7.0
     * @url https://github.com/gamboamartin/system/wiki/src.actions.init_alta_bd.22.4.0
     */
    final public function init_alta_bd(string $siguiente_view = 'modifica'): array|string
    {
        $siguiente_view = $this->siguiente_view(siguiente_view: $siguiente_view);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view);
        }

        $limpia_button = $this->limpia_butons();
        if (errores::$error) {
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
     * REG
     * Elimina botones de acción de la variable `$_POST` antes de procesar los datos.
     *
     * Esta función revisa la existencia de ciertos botones dentro de `$_POST` y los elimina si están presentes.
     * Su objetivo es limpiar la solicitud POST antes de continuar con el procesamiento de datos,
     * evitando que estos botones interfieran en la lógica posterior.
     *
     * ### Botones eliminados:
     * - `'guarda'`: Indica que se intentó guardar un registro.
     * - `'guarda_otro'`: Indica que se intentó guardar y crear otro registro.
     * - `'btn_action_next'`: Define la siguiente acción a realizar.
     *
     * @return array Retorna el array `$_POST` limpio, sin los botones de acción eliminados.
     *
     * ### Ejemplos de entrada y salida:
     *
     * #### Ejemplo 1: `$_POST` con botones de acción
     * **Entrada (`$_POST` antes de la limpieza):**
     * ```php
     * $_POST = [
     *     'nombre' => 'Ejemplo',
     *     'email' => 'ejemplo@email.com',
     *     'guarda' => 'Guardar',
     *     'guarda_otro' => 'Guardar y crear otro',
     *     'btn_action_next' => 'detalle'
     * ];
     * ```
     * **Salida (`$_POST` después de la limpieza):**
     * ```php
     * [
     *     'nombre' => 'Ejemplo',
     *     'email' => 'ejemplo@email.com'
     * ]
     * ```
     *
     * #### Ejemplo 2: `$_POST` sin botones de acción
     * **Entrada (`$_POST` sin los botones a eliminar):**
     * ```php
     * $_POST = [
     *     'usuario' => 'admin',
     *     'clave' => 'secreta'
     * ];
     * ```
     * **Salida (sin cambios, ya que no había botones a eliminar):**
     * ```php
     * [
     *     'usuario' => 'admin',
     *     'clave' => 'secreta'
     * ]
     * ```
     *
     * ### Notas:
     * - La función **no** afecta otros valores dentro de `$_POST`.
     * - Solo se eliminan los botones mencionados si existen en la solicitud.
     * - Se utiliza para limpiar datos antes de su procesamiento posterior en el sistema.
     *
     * @version 18.1.0
     * @url https://github.com/gamboamartin/system/wiki/src.actions.limpia_butons.22.4.0
     */
    private function limpia_butons(): array
    {
        if (isset($_POST['guarda'])) {
            unset($_POST['guarda']);
        }
        if (isset($_POST['guarda_otro'])) {
            unset($_POST['guarda_otro']);
        }
        if (isset($_POST['btn_action_next'])) {
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
     * REG
     * Genera un link de retorno después de una acción de alta en la base de datos.
     *
     * Esta función construye un enlace basado en la sección, el identificador de registro y la siguiente vista a ejecutar.
     * También valida la existencia de permisos si es necesario.
     *
     * @param PDO $link Conexión a la base de datos.
     * @param int $registro_id ID del registro recién creado o en proceso.
     * @param string $seccion Sección en la que se ejecuta la acción.
     * @param string $siguiente_view Vista a la que se redirige después de la acción. Por defecto, 'modifica'.
     * @param array $params Parámetros adicionales para la URL (por defecto, un array vacío).
     * @param bool $valida_permiso Indica si se debe validar el permiso antes de generar el enlace (por defecto, `false`).
     *
     * @return array|string Retorna un string con la URL generada o un array con un mensaje de error en caso de fallo.
     *
     * @example
     * // Ejemplo de uso:
     * $pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'password');
     * $registro_id = 15;
     * $seccion = 'usuarios';
     * $siguiente_view = 'detalle';
     * $params = ['extra_param' => 'valor'];
     * $valida_permiso = true;
     *
     * $link = $this->retorno_alta_bd($pdo, $registro_id, $seccion, $siguiente_view, $params, $valida_permiso);
     *
     * @example
     * // Posible salida exitosa:
     * "./index.php?seccion=usuarios&accion=detalle&registro_id=15&adm_menu_id=2&session_id=abcd1234&extra_param=valor"
     *
     * @example
     * // Posible salida en caso de error:
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al generar link',
     *     'data' => null
     * ]
     *
     * @throws errores Si la sección está vacía o si falla la generación del enlace.
     * @version 0.22.2
     */
    final public function retorno_alta_bd(PDO $link, int $registro_id, string $seccion, string $siguiente_view,
                                          array $params = array(), bool $valida_permiso = false): array|string
    {
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(mensaje: 'Error la seccion esta vacia', data: $seccion);
        }

        $siguiente_view = trim($siguiente_view);
        if ($siguiente_view === '') {
            $siguiente_view = 'modifica';
        }

        $link = (new links_menu(link: $link, registro_id: $registro_id))->link_con_id(
            accion: $siguiente_view, link: $link, registro_id: $registro_id, seccion: $seccion, params: $params,
            valida_permiso: $valida_permiso
        );

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        return $link;
    }


    /**
     * REG
     * Determina la siguiente vista a mostrar en la aplicación.
     *
     * Esta función evalúa ciertos valores dentro de la variable `$_POST` para definir cuál será la siguiente vista
     * a la que se redirigirá después de una acción. Si un usuario ha presionado un botón específico, la vista
     * cambiará en función del botón presionado. En caso contrario, se mantiene la vista por defecto.
     *
     * ### Comportamiento:
     * - Si se presiona el botón `guarda_otro`, la siguiente vista será `'alta'`.
     * - Si existe un botón `btn_action_next`, la siguiente vista será el valor asignado a este botón.
     * - Si no se cumplen las condiciones anteriores, se retorna el valor por defecto de `$siguiente_view`.
     *
     * @param string $siguiente_view Vista a la que se redirigirá por defecto si no hay un cambio explícito.
     *                                Por defecto, su valor es `'modifica'`.
     *
     * @return string Retorna el nombre de la vista que se debe mostrar a continuación.
     *
     * ### Ejemplos de entrada y salida:
     * #### Ejemplo 1: Sin botones en `$_POST`
     * ```php
     * $_POST = [];
     * $resultado = siguiente_view(); // Retorna: 'modifica'
     * ```
     *
     * #### Ejemplo 2: Botón "guarda_otro" presente
     * ```php
     * $_POST = ['guarda_otro' => 'Guardar y crear otro'];
     * $resultado = siguiente_view(); // Retorna: 'alta'
     * ```
     *
     * #### Ejemplo 3: Botón "btn_action_next" con un valor específico
     * ```php
     * $_POST = ['btn_action_next' => 'detalle'];
     * $resultado = siguiente_view(); // Retorna: 'detalle'
     * ```
     *
     * #### Ejemplo 4: Ambos botones presentes
     * ```php
     * $_POST = ['guarda_otro' => 'Guardar y crear otro', 'btn_action_next' => 'detalle'];
     * $resultado = siguiente_view(); // Retorna: 'alta' (prioriza "guarda_otro" sobre "btn_action_next")
     * ```
     *
     * ### Notas:
     * - Si `$_POST['guarda_otro']` está definido, siempre se prioriza la vista `'alta'`, ignorando `btn_action_next`.
     * - Si `$_POST['btn_action_next']` está definido, se tomará su valor como la siguiente vista.
     * - Si ninguno de los dos está definido, la vista por defecto es `'modifica'`.
     *
     * @version 17.2.0
     * @url https://github.com/gamboamartin/system/wiki/src.actions.siguiente_view.22.4.0
     */
    final public function siguiente_view(string $siguiente_view = 'modifica'): string
    {
        if (isset($_POST['guarda_otro'])) {
            $siguiente_view = 'alta';
        } elseif (isset($_POST['btn_action_next'])) {
            $siguiente_view = (string) $_POST['btn_action_next'];
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
