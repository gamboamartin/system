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
     * REG
     * Genera los datos clave para un campo en una lista de registros.
     *
     * Esta función se encarga de formatear un nombre de campo y asociarlo con su clave correspondiente,
     * generando un objeto con los valores necesarios para su uso en la representación de listas en la interfaz.
     *
     * @param string $campo_puro Nombre del campo sin prefijo de tabla.
     * @param string $tabla Nombre de la tabla o sección a la que pertenece el campo.
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las siguientes propiedades:
     *  - `key_value` (string): Clave generada para el campo en la lista.
     *  - `name_lista` (string): Nombre formateado para su uso en la UI.
     *
     * @throws errores Si `$tabla` o `$campo_puro` están vacíos, devuelve un error a través de `$this->error->error()`.
     *                   El error es un array de tipo `errores::$error`.
     *
     * @example
     * ```php
     * $init = new init();
     *
     * // Ejemplo válido
     * $resultado = $init->data_key_row_lista('nombre', 'usuario');
     * echo $resultado->key_value;  // Salida: "usuario_nombre"
     * echo $resultado->name_lista; // Salida: "Nombre"
     *
     * // Otro ejemplo válido
     * $resultado = $init->data_key_row_lista('fecha_registro', 'cliente');
     * echo $resultado->key_value;  // Salida: "cliente_fecha_registro"
     * echo $resultado->name_lista; // Salida: "Fecha Registro"
     *
     * // Ejemplo con error
     * $resultado = $init->data_key_row_lista('', 'cliente');
     * print_r($resultado); // Salida: Array de error indicando que `$campo_puro` está vacío.
     * ```
     *
     * @version 0.163.34
     */
    private function data_key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        $campo_puro = trim($campo_puro);
        if ($campo_puro === '') {
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio', data: $campo_puro);
        }

        $key_value = $this->key_value_campo(campo_puro: $campo_puro, tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar key value lista', data: $key_value);
        }

        $name_lista = $this->name_lista(campo_puro: $campo_puro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar $name_lista', data: $name_lista);
        }

        $data = new stdClass();
        $data->key_value = $key_value;
        $data->name_lista = $name_lista;

        return $data;
    }


    /**
     * REG
     * Genera un objeto con las claves para representar un campo en una lista de datos.
     *
     * Esta función recibe una clave única (`key_value`) y un nombre descriptivo (`name_lista`)
     * y devuelve un objeto `stdClass` que contiene estos valores, estructurados para su uso en
     * listas de datos o visualización de tablas en interfaces gráficas.
     *
     * @param string $key_value Clave única generada a partir de la tabla y el campo correspondiente.
     * @param string $name_lista Nombre legible para mostrar en la interfaz de usuario.
     *
     * @return stdClass|array Devuelve un objeto `stdClass` con las siguientes propiedades:
     *  - `campo` (string): Clave única generada para el campo.
     *  - `name_lista` (string): Nombre formateado para su uso en UI.
     *
     * @throws errores Si `$key_value` o `$name_lista` están vacíos, devuelve un error
     *                   a través de `$this->error->error()`. El error es un array de tipo `errores::$error`.
     *
     * @example
     * ```php
     * $init = new init();
     *
     * // Ejemplo válido
     * $resultado = $init->genera_key_row_lista('usuario_nombre', 'Nombre');
     * echo $resultado->campo;      // Salida: "usuario_nombre"
     * echo $resultado->name_lista; // Salida: "Nombre"
     *
     * // Otro ejemplo válido
     * $resultado = $init->genera_key_row_lista('cliente_fecha_registro', 'Fecha de Registro');
     * echo $resultado->campo;      // Salida: "cliente_fecha_registro"
     * echo $resultado->name_lista; // Salida: "Fecha de Registro"
     *
     * // Ejemplo con error
     * $resultado = $init->genera_key_row_lista('', 'Fecha de Registro');
     * print_r($resultado); // Salida: Array de error indicando que `key_value` está vacío.
     * ```
     *
     * @version 0.166.34
     */
    private function genera_key_row_lista(string $key_value, string $name_lista): stdClass|array
    {
        $key_value = trim($key_value);
        if ($key_value === '') {
            return $this->error->error(mensaje: 'Error key_value esta vacio', data: $key_value);
        }

        $name_lista = trim($name_lista);
        if ($name_lista === '') {
            return $this->error->error(mensaje: 'Error name_lista esta vacio', data: $name_lista);
        }

        $keys_row_lista = new stdClass();
        $keys_row_lista->campo = $key_value;
        $keys_row_lista->name_lista = $name_lista;

        return $keys_row_lista;
    }


    /**
     * REG
     * Inicializa y asigna la ruta del breadcrumb al controlador.
     *
     * Esta función genera la ruta de un archivo breadcrumb (`title.php`) basado en la acción y tabla
     * del controlador. Luego, verifica si el archivo existe en el sistema y, si es así, asigna la
     * ruta correspondiente a la propiedad `include_breadcrumb` del controlador.
     *
     * - Si la ruta generada existe, se asigna como el breadcrumb válido.
     * - Si la ruta **NO** existe, se asigna una ruta alternativa basada en la acción del controlador.
     * - En caso de error, se devuelve un array de error generado por `$this->error->error()`.
     *
     * ---
     *
     * ## **Ejemplo de Uso**
     * ```php
     * $controlador = new system();
     * $controlador->tabla = "usuario";
     * $controlador->accion = "index";
     *
     * $init = new init();
     *
     * // Llamada a la función
     * $resultado = $init->include_breadcrumb($controlador);
     * echo $resultado;
     * // Salida esperada (si el archivo existe): "templates/head/usuario/index/title.php"
     * // Si no existe: "ruta/templates/head/index/title.php"
     * ```
     *
     * ---
     *
     * ## **Parámetros**
     *
     * @param system $controler Instancia del controlador en ejecución.
     *                          Se usará para determinar la acción y la tabla del breadcrumb.
     *
     * ---
     *
     * ## **Valor de Retorno**
     *
     * @return array|string Devuelve la **ruta del breadcrumb** si el archivo existe.
     *                      Si ocurre un error, devuelve un **array con información del error**.
     *
     * ---
     *
     * ## **Casos de Uso y Resultados Esperados**
     *
     * ### **✅ Caso 1: Archivo de breadcrumb válido**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $controlador->tabla = "usuario";
     * $controlador->accion = "index";
     * $resultado = $init->include_breadcrumb($controlador);
     * ```
     * **Salida esperada (si el archivo existe):**
     * ```php
     * "templates/head/usuario/index/title.php"
     * ```
     * ---
     *
     * ### **❌ Caso 2: Archivo de breadcrumb NO existente, pero con acción válida**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $controlador->tabla = "cliente";
     * $controlador->accion = "detalle";
     * $resultado = $init->include_breadcrumb($controlador);
     * ```
     * **Salida esperada (si el archivo no existe en `templates/head/cliente/detalle/title.php`):**
     * ```php
     * "ruta/templates/head/detalle/title.php"
     * ```
     * ---
     *
     * ### **❌ Caso 3: Error al generar el breadcrumb**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $controlador->tabla = "";
     * $controlador->accion = "";
     * $resultado = $init->include_breadcrumb($controlador);
     * ```
     * **Salida esperada (error detectado por `errores::$error`):**
     * ```php
     * Array (
     *     [error] => true,
     *     [mensaje] => "Error al inicializar include_breadcrumb_rs",
     *     [data] => ""
     * )
     * ```
     * ---
     *
     * ## **Errores y Manejo de Excepciones**
     * - **Si `$controler->tabla` o `$controler->accion` están vacíos**, la función generará un error y devolverá un array de error.
     * - **Si el archivo no existe**, se intentará asignar una ruta alternativa basada en la acción.
     * - **Si `errores::$error` está activado, la función devuelve un array con la información del error.**
     *
     * ---
     *
     * @version 7.56.3
     */
    final public function include_breadcrumb(system $controler): array|string
    {
        // Generar la ruta base para el breadcrumb
        $include_breadcrumb = (new views())->ruta_templates . "head/$controler->accion/title.php";

        // Verificar si existe un breadcrumb específico para la tabla y acción del controlador
        if (file_exists("templates/head/$controler->tabla/$controler->accion/title.php")) {
            $include_breadcrumb = "templates/head/$controler->tabla/$controler->accion/title.php";
        }

        // Llamar a la función interna que valida y asigna el breadcrumb al controlador
        $include_breadcrumb_rs = $this->include_breadcrumbs(controlador: $controler,
            include_breadcrumb: $include_breadcrumb);

        // Si hay un error en la inicialización, devolver un array de error
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar include_breadcrumb_rs',
                data: $include_breadcrumb_rs);
        }

        return $include_breadcrumb_rs;
    }


    /**
     * REG
     * Asigna la ruta de un breadcrumb al controlador y valida su existencia.
     *
     * Esta función recibe una ruta de breadcrumb (`$include_breadcrumb`) y la asigna a la propiedad
     * `include_breadcrumb` del controlador. Luego, verifica si el archivo existe en el sistema.
     *
     * - Si el archivo existe, la ruta se mantiene asignada.
     * - Si el archivo **NO** existe, la propiedad `include_breadcrumb` se establece como una cadena vacía (`''`).
     *
     * ---
     *
     * ## **Ejemplo de Uso**
     * ```php
     * $controlador = new system();
     *
     * // Ruta de un breadcrumb válido
     * $ruta_valida = "templates/head/usuario/index/title.php";
     * $resultado = $this->include_breadcrumbs($controlador, $ruta_valida);
     * echo $resultado;
     * // Salida esperada (si el archivo existe): "templates/head/usuario/index/title.php"
     *
     * // Ruta de un breadcrumb inexistente
     * $ruta_invalida = "templates/head/usuario/index/no_existe.php";
     * $resultado = $this->include_breadcrumbs($controlador, $ruta_invalida);
     * echo $resultado;
     * // Salida esperada: ""
     * ```
     *
     * ---
     *
     * ## **Parámetros**
     *
     * @param system $controlador Instancia del controlador en ejecución.
     *                            Se le asignará la ruta del breadcrumb en la propiedad `include_breadcrumb`.
     *
     * @param string $include_breadcrumb Ruta del archivo breadcrumb a asignar.
     *                                   Puede ser una ruta relativa dentro del directorio de plantillas.
     *
     * ---
     *
     * ## **Valor de Retorno**
     *
     * @return string Devuelve la **ruta del breadcrumb** si el archivo existe.
     *                Si el archivo **NO** existe, devuelve una **cadena vacía** (`''`).
     *
     * ---
     *
     * ## **Casos de Uso y Resultados Esperados**
     *
     * ### **✅ Caso 1: Archivo de breadcrumb válido**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $ruta = "templates/head/usuario/index/title.php";
     * $resultado = $this->include_breadcrumbs($controlador, $ruta);
     * ```
     * **Salida esperada (si el archivo existe):**
     * ```php
     * "templates/head/usuario/index/title.php"
     * ```
     * ---
     *
     * ### **❌ Caso 2: Archivo de breadcrumb NO existente**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $ruta = "templates/head/usuario/index/no_existe.php";
     * $resultado = $this->include_breadcrumbs($controlador, $ruta);
     * ```
     * **Salida esperada:**
     * ```php
     * ""
     * ```
     * ---
     *
     * ### **❌ Caso 3: Ruta vacía**
     * **Entrada:**
     * ```php
     * $controlador = new system();
     * $ruta = "";
     * $resultado = $this->include_breadcrumbs($controlador, $ruta);
     * ```
     * **Salida esperada:**
     * ```php
     * ""
     * ```
     * ---
     *
     * ## **Errores y Manejo de Excepciones**
     * - **Si `$include_breadcrumb` está vacío**, la función simplemente asigna una cadena vacía sin generar errores.
     * - **Si el archivo no existe**, la propiedad `include_breadcrumb` del controlador se establece como `''` (vacío).
     *
     * ---
     *
     * @version 7.54.4
     */
    private function include_breadcrumbs(system $controlador, string $include_breadcrumb): string
    {
        // Asignar la ruta al controlador
        $controlador->include_breadcrumb = $include_breadcrumb;

        // Si el archivo no existe, asignar una cadena vacía
        if (!file_exists($include_breadcrumb)) {
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
     * REG
     * Genera un objeto con las claves necesarias para representar un campo en una lista de datos.
     *
     * Esta función toma el nombre de un campo (`campo_puro`) y el nombre de una tabla (`tabla`),
     * y genera un objeto `stdClass` que contiene un identificador de campo (`key_value`) y
     * un nombre legible para mostrar en la interfaz (`name_lista`).
     *
     * @param string $campo_puro Nombre del campo en la base de datos sin prefijo de tabla.
     * @param string $tabla Nombre de la tabla a la que pertenece el campo.
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las siguientes propiedades:
     *  - `campo` (string): Clave única generada a partir del campo y la tabla.
     *  - `name_lista` (string): Nombre formateado para su uso en la UI.
     *
     * @throws errores Si `$tabla` o `$campo_puro` están vacíos, devuelve un error a través de
     *                   `$this->error->error()`. El error es un array de tipo `errores::$error`.
     *
     * @example
     * ```php
     * $init = new init();
     *
     * // Ejemplo válido
     * $resultado = $init->key_row_lista('nombre', 'usuario');
     * echo $resultado->campo;      // Salida: "usuario_nombre"
     * echo $resultado->name_lista; // Salida: "Nombre"
     *
     * // Otro ejemplo válido
     * $resultado = $init->key_row_lista('fecha_registro', 'cliente');
     * echo $resultado->campo;      // Salida: "cliente_fecha_registro"
     * echo $resultado->name_lista; // Salida: "Fecha Registro"
     *
     * // Ejemplo con error
     * $resultado = $init->key_row_lista('', 'usuario');
     * print_r($resultado); // Salida: Array de error indicando que `$campo_puro` está vacío.
     * ```
     *
     * @version 0.168.34
     */
    private function key_row_lista(string $campo_puro, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        $campo_puro = trim($campo_puro);
        if ($campo_puro === '') {
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio', data: $campo_puro);
        }

        $data_key_row_lista = $this->data_key_row_lista(campo_puro: $campo_puro, tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar $data_key_row_lista', data: $data_key_row_lista);
        }

        $key_row_lista = $this->genera_key_row_lista(
            key_value: $data_key_row_lista->key_value,
            name_lista: $data_key_row_lista->name_lista
        );

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar $keys_row_lista', data: $key_row_lista);
        }

        return $key_row_lista;
    }


    /**
     * REG
     * Genera una lista de claves (`keys_row_lista`) a partir de los campos de la lista del controlador.
     *
     * Esta función recorre los campos definidos en `rows_lista` dentro del controlador `system`,
     * generando una clave única y un nombre formateado para cada uno. Luego, almacena estos datos
     * en `keys_row_lista` dentro del mismo controlador.
     *
     * @param system $controler Instancia del controlador en ejecución.
     *
     * @return array Devuelve un array de objetos `stdClass`, donde cada objeto representa
     *               una clave de campo (`campo`) y su nombre formateado (`name_lista`).
     *
     * @throws errores Si ocurre un error al inicializar alguna clave (`key_row_lista`),
     *                   se devuelve un error a través de `$this->error->error()`. El error
     *                   es un array de tipo `errores::$error`.
     *
     * @example
     * ```php
     * $controler = new system();
     * $controler->tabla = "usuario";
     * $controler->rows_lista = ["nombre", "email", "fecha_registro"];
     *
     * $init = new init();
     * $keys_lista = $init->keys_row_lista($controler);
     *
     * print_r($keys_lista);
     * // Salida esperada:
     * // [
     * //   (object) ["campo" => "usuario_nombre", "name_lista" => "Nombre"],
     * //   (object) ["campo" => "usuario_email", "name_lista" => "Email"],
     * //   (object) ["campo" => "usuario_fecha_registro", "name_lista" => "Fecha Registro"]
     * // ]
     * ```
     *
     * @version 0.169.34
     */
    final public function keys_row_lista(system $controler): array
    {
        foreach ($controler->rows_lista as $row) {
            $key_row_lista = $this->key_row_lista(campo_puro: $row, tabla: $controler->tabla);

            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar $key_row_lista', data: $key_row_lista);
            }

            $controler->keys_row_lista[] = $key_row_lista;
        }

        return $controler->keys_row_lista;
    }


    /**
     * REG
     * Genera una clave compuesta para un campo dentro de una tabla.
     *
     * Esta función concatena el nombre de una tabla y un campo para generar una clave única
     * que puede ser utilizada en diferentes operaciones dentro del sistema.
     *
     * @param string $campo_puro Nombre del campo puro de la tabla en ejecución. No debe estar vacío.
     * @param string $tabla Nombre de la tabla o modelo donde se encuentra el campo. No debe estar vacío.
     *
     * @return string|array Devuelve la clave compuesta en formato `{tabla}_{campo_puro}` si la entrada es válida.
     *                      En caso de error, devuelve un **array de tipo `errores::$error`** con el mensaje correspondiente.
     *
     * @throws errores Si `$tabla` o `$campo_puro` están vacíos, se devuelve un error a través de `$this->error->error()`.
     *                   El error devuelto es un **array de tipo `errores::$error`**.
     *
     * @example
     * ```php
     * $init = new init();
     *
     * // Ejemplo válido
     * $key = $init->key_value_campo('nombre', 'usuario');
     * echo $key; // Salida: "usuario_nombre"
     *
     * // Ejemplo con error (tabla vacía)
     * $key = $init->key_value_campo('nombre', '');
     * print_r($key);
     * // Salida esperada:
     * // Array (
     * //     [error] => true,
     * //     [mensaje] => "Error tabla esta vacia",
     * //     [data] => ""
     * // )
     *
     * // Ejemplo con error (campo vacío)
     * $key = $init->key_value_campo('', 'usuario');
     * print_r($key);
     * // Salida esperada:
     * // Array (
     * //     [error] => true,
     * //     [mensaje] => "Error $campo_puro esta vacio",
     * //     [data] => ""
     * // )
     * ```
     *
     * @version 0.20.5
     */
    private function key_value_campo(string $campo_puro, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $campo_puro = trim($campo_puro);
        if ($campo_puro === '') {
            return $this->error->error(mensaje: 'Error $campo_puro esta vacio', data: $campo_puro);
        }

        return $tabla . '_' . $campo_puro;
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
     * REG
     * Convierte un nombre de campo en un formato legible para listas.
     *
     * Esta función toma un nombre de campo con guiones bajos (`_`) y los reemplaza con espacios,
     * luego capitaliza cada palabra, generando un formato más legible para su uso en interfaces gráficas.
     *
     * @param string $campo_puro Nombre del campo a transformar. No debe estar vacío.
     *
     * @return string Devuelve el nombre del campo formateado, con espacios en lugar de guiones bajos
     *                y con cada palabra en mayúscula.
     *
     * @example
     * ```php
     * $init = new init();
     *
     * // Ejemplo válido
     * $nombre = $init->name_lista('nombre_completo');
     * echo $nombre; // Salida: "Nombre Completo"
     *
     * $nombre = $init->name_lista('fecha_nacimiento');
     * echo $nombre; // Salida: "Fecha Nacimiento"
     *
     * // Ejemplo con campo vacío
     * $nombre = $init->name_lista('');
     * echo $nombre; // Salida: ""
     * ```
     *
     * @version 0.62.32
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
