<?php
namespace gamboamartin\system;

use base\controller\controler;
use base\orm\modelo;
use base\orm\modelo_base;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\params;
use gamboamartin\system\html_controler\select;
use gamboamartin\system\html_controler\template;
use gamboamartin\system\html_controler\texts;
use gamboamartin\system\html_controler\validacion_html;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;

use PDO;
use stdClass;

class html_controler
{
    public directivas $directivas;
    protected errores $error;
    public html $html_base;
    protected validacion_html $validacion;

    public function __construct(html $html)
    {
        $this->directivas = new directivas(html: $html);
        $this->error = new errores();
        $this->html_base = $html;
        $this->validacion = new validacion_html();
    }

    /**
     * REG
     * Genera los parámetros HTML para un enlace (`<a>`) con estilo y atributos personalizados.
     *
     * Esta función genera los parámetros necesarios para un enlace HTML (`<a>`), incluyendo atributos como `role`,
     * `title`, `href`, `class`, `id`, `style`, `target`, `onclick`, entre otros. Los parámetros de entrada son validados
     * antes de generar la cadena final. Si alguna validación falla, se genera un mensaje de error. La función también
     * asegura que los parámetros generados estén formateados correctamente, sin espacios redundantes.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorta y valida cada parámetro de entrada: `style`, `title`, `role`, `target`, etc.
     * 2. Se genera la cadena de parámetros con los atributos HTML adecuados.
     * 3. Si todo es válido, se retorna la cadena de parámetros HTML generada.
     * 4. Si ocurre algún error durante la validación, se retorna un mensaje de error detallado.
     * 5. Se elimina cualquier redundancia de espacios en la cadena generada.
     *
     * **Parámetros:**
     *
     * @param string $css_extra Clase CSS adicional que se agrega al botón.
     * @param string $cols_html Clase de las columnas de Bootstrap (ej. `'col-sm-12'`).
     * @param string $id_css El identificador único para el enlace (`<a>`), usado en el atributo `id`. Este parámetro es opcional.
     * @param string $link La URL de destino del enlace. Este parámetro es obligatorio.
     * @param string $onclick_event Código JavaScript que se ejecuta cuando se hace clic en el enlace (opcional).
     * @param string $role El valor del atributo `role` del enlace. Este parámetro es obligatorio.
     * @param string $style El estilo CSS del enlace. Este parámetro es obligatorio.
     * @param string $style_custom Estilos personalizados adicionales para el enlace.
     * @param string $target El destino del enlace, especificado con el atributo `target`. Ejemplo: `'_blank'` (opcional).
     * @param string $title El texto del atributo `title` que se muestra cuando el usuario pasa el cursor sobre el enlace. Este parámetro es obligatorio.
     *
     * **Retorno:**
     * - Devuelve una cadena con los parámetros HTML generados para el enlace (`<a>`), que incluye atributos como `role`, `title`, `href`, `style`, `id`, `onclick`, `target`, etc.
     * - Si ocurre algún error en los parámetros, se devuelve un mensaje de error detallado.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de parámetros HTML para un enlace**
     * ```php
     * $css_extra = "extra-class";
     * $cols_html = "col-sm-12";
     * $id_css = "myButton";
     * $link = "https://www.example.com";
     * $onclick_event = "handleClick";
     * $role = "button";
     * $style = "primary";
     * $style_custom = "background-color: red;";
     * $target = "_blank";
     * $title = "Haz clic aquí";
     *
     * $resultado = $this->a_params($css_extra, $cols_html, $id_css, $link, $onclick_event, $role, $style, $style_custom, $target, $title);
     * // Retorna: "role='button' title='Haz clic aquí' href='https://www.example.com' class='btn btn-primary col-sm-12 extra-class' style='background-color: red;' id='myButton' target='_blank' onclick='handleClick(event)'"
     * ```
     *
     * **Ejemplo 2: Error debido a un parámetro vacío**
     * ```php
     * $css_extra = "extra-class";
     * $cols_html = "col-sm-12";
     * $id_css = "";
     * $link = "https://www.example.com";
     * $onclick_event = "handleClick";
     * $role = "";
     * $style = "primary";
     * $style_custom = "background-color: red;";
     * $target = "_blank";
     * $title = "Haz clic aquí";
     *
     * $resultado = $this->a_params($css_extra, $cols_html, $id_css, $link, $onclick_event, $role, $style, $style_custom, $target, $title);
     * // Retorna: "Error role esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function a_params(string $css_extra, string $cols_html, string $id_css, string $link, string $onclick_event,
                              string $role, string $style, string $style_custom, string $target,
                              string $title): string|array
    {
        // Validar y recortar el estilo
        $style = trim($style);
        if ($style === '') {
            return $this->error->error(mensaje: 'Error style esta vacio', data: $style);
        }

        // Validar y recortar el título
        $title = trim($title);
        if ($title === '') {
            return $this->error->error(mensaje: 'Error title esta vacio', data: $title);
        }

        // Validar y recortar el role
        $role = trim($role);
        if ($role === '') {
            return $this->error->error(mensaje: 'Error role esta vacio', data: $role);
        }

        // Generar HTML para el atributo target
        $target_html = '';
        $target = trim($target);
        if ($target !== '') {
            $target_html = "target='$target'";
        }

        // Generar HTML para el atributo id
        $id_css_html = '';
        if($id_css!==''){
            $id_css_html = "id='$id_css'";
        }

        // Generar HTML para el evento onclick
        $onclick = "";
        $onclick_event = trim($onclick_event);
        if($onclick_event !== ''){
            $onclick = "onclick='$onclick_event(event)'";
        }

        // Crear la cadena de parámetros
        $params = "role='$role' title='$title' href='$link' class='btn btn-$style $cols_html $css_extra' $style_custom";
        $params .= " $id_css_html $target_html $onclick";
        $params = trim($params);

        // Eliminar redundancias de espacios
        $i = 0;
        $iteraciones = 5;
        while ($i <= $iteraciones) {
            $params = str_replace('  ', ' ', $params);
            $i++;
        }

        return $params;
    }


    /**
     * REG
     * Genera un enlace (`<a>`) con atributos personalizados y un ícono o etiqueta HTML, utilizando un conjunto de parámetros.
     *
     * Esta función toma los parámetros de un enlace (`<a>`), incluyendo atributos como `role`, `title`, `style`, `href`,
     * `onclick`, entre otros. También permite incluir un ícono y una etiqueta en el enlace, y se asegura de que los valores
     * proporcionados sean válidos antes de generar el enlace HTML final.
     *
     * **Pasos de procesamiento:**
     * 1. Se validan los parámetros de entrada `style`, `title`, `role`, `cols`, y otros.
     * 2. Se genera una clase CSS para las columnas utilizando la función `cols_html`.
     * 3. Se genera el valor para el atributo `role` utilizando la función `role_button`.
     * 4. Se genera el atributo `style` personalizado utilizando `genera_styles_custom`.
     * 5. Se combinan todos los parámetros en una cadena de parámetros HTML para el enlace.
     * 6. Se combina el ícono y la etiqueta HTML en el enlace.
     * 7. Se elimina cualquier redundancia en los espacios de la cadena generada.
     * 8. Si todo es válido, se genera y se retorna el enlace HTML completo.
     * 9. Si ocurre algún error en el proceso, se genera un mensaje de error detallado.
     *
     * **Parámetros:**
     *
     * @param string $css_extra Clase CSS adicional que se agrega al enlace.
     * @param int $cols El número de columnas que se usarán en el enlace (usado para generar la clase `col-sm-{cols}`).
     * @param string $etiqueta_html El contenido HTML de la etiqueta del enlace (por ejemplo, texto).
     * @param string $icon_html El contenido HTML del ícono del enlace (opcional).
     * @param string $id_css El identificador único para el enlace (opcional).
     * @param string $link La URL de destino del enlace (obligatorio).
     * @param string $onclick_event El código JavaScript que se ejecuta cuando se hace clic en el enlace (opcional).
     * @param string $role El valor del atributo `role` del enlace (obligatorio).
     * @param string $style El estilo CSS del enlace (obligatorio).
     * @param array $styles Estilos adicionales personalizados que se aplicarán al enlace.
     * @param string $target El atributo `target` del enlace (opcional), por ejemplo `"_blank"`.
     * @param string $title El texto del atributo `title` del enlace (obligatorio).
     *
     * **Retorno:**
     * - Devuelve el enlace HTML completo con los atributos generados si todo es válido.
     * - Si ocurre algún error, devuelve un mensaje de error detallado.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de un enlace HTML**
     * ```php
     * $css_extra = "extra-class";
     * $cols = 6;
     * $etiqueta_html = "Hacer clic aquí";
     * $icon_html = "<span class='fa fa-check'></span>";
     * $id_css = "myButton";
     * $link = "https://www.example.com";
     * $onclick_event = "handleClick()";
     * $role = "button";
     * $style = "primary";
     * $styles = ['color' => 'red'];
     * $target = "_blank";
     * $title = "Haz clic para ir al sitio";
     *
     * $resultado = $this->a_role($css_extra, $cols, $etiqueta_html, $icon_html, $id_css, $link, $onclick_event, $role, $style, $styles, $target, $title);
     * // Retorna: "<a role='button' title='Haz clic para ir al sitio' href='https://www.example.com' class='btn btn-primary col-sm-6 extra-class' style='color: red;' id='myButton' target='_blank' onclick='handleClick()'><span class='fa fa-check'></span> Hacer clic aquí</a>"
     * ```
     *
     * **Ejemplo 2: Error debido a un parámetro vacío**
     * ```php
     * $css_extra = "";
     * $cols = 6;
     * $etiqueta_html = "Hacer clic aquí";
     * $icon_html = "<span class='fa fa-check'></span>";
     * $id_css = "myButton";
     * $link = "https://www.example.com";
     * $onclick_event = "handleClick()";
     * $role = "";
     * $style = "primary";
     * $styles = ['color' => 'red'];
     * $target = "_blank";
     * $title = "Haz clic para ir al sitio";
     *
     * $resultado = $this->a_role($css_extra, $cols, $etiqueta_html, $icon_html, $id_css, $link, $onclick_event, $role, $style, $styles, $target, $title);
     * // Retorna: "Error role esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function a_role(string $css_extra, int $cols, string $etiqueta_html, string $icon_html, string $id_css,
                            string $link, string $onclick_event, string $role, string $style, array $styles,
                            string $target, string $title): string|array
    {
        // Validar y recortar el estilo
        $style = trim($style);
        if ($style === '') {
            return $this->error->error(mensaje: 'Error style esta vacio', data: $style);
        }

        // Validar y recortar el título
        $title = trim($title);
        if ($title === '') {
            $title = $etiqueta_html;
        }

        if ($title === '') {
            return $this->error->error(mensaje: 'Error title esta vacio', data: $title);
        }

        // Generar HTML para el atributo cols
        $cols_html = $this->cols_html(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar cols html', data: $cols_html);
        }

        // Generar el role del botón
        $role = $this->role_button(role: $role);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar role', data: $role);
        }

        // Generar estilos personalizados
        $style_custom = $this->genera_styles_custom(styles: $styles);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar style_custom', data: $style_custom);
        }

        // Generar los parámetros para el enlace
        $params = $this->a_params(css_extra: $css_extra, cols_html: $cols_html, id_css: $id_css, link: $link,
            onclick_event: $onclick_event, role: $role, style: $style, style_custom: $style_custom, target: $target,
            title: $title);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar params', data: $params);
        }

        // Generar el enlace completo
        $a = $this->a_role_button(etiqueta_html: $etiqueta_html, icon_html: $icon_html, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar button', data: $a);
        }

        // Retornar el enlace HTML generado
        return $a;
    }


    /**
     * REG
     * Genera un enlace (`<a>`) con un ícono y una etiqueta HTML a partir de los parámetros proporcionados.
     *
     * Esta función toma una etiqueta HTML, un ícono HTML y los parámetros de un enlace (`<a>`) para generar
     * un enlace completo con los atributos adecuados. Si alguno de los parámetros proporcionados está vacío,
     * la función genera un mensaje de error. Si todo es válido, se genera y retorna el enlace HTML completo.
     *
     * **Pasos de procesamiento:**
     * 1. Se recortan los valores de los parámetros `etiqueta_html`, `icon_html` y `params`.
     * 2. Si `params` está vacío, se genera un mensaje de error.
     * 3. Se combina el ícono y la etiqueta HTML en una sola cadena, generando un mensaje de error si el resultado es vacío.
     * 4. Se genera el enlace HTML (`<a>`) con los parámetros proporcionados y el contenido combinado del ícono y la etiqueta.
     * 5. Se eliminan los espacios redundantes en la cadena generada.
     * 6. Si todo es válido, se retorna el enlace HTML completo.
     * 7. Si ocurre algún error durante el proceso, se retorna un mensaje de error detallado.
     *
     * **Parámetros:**
     *
     * @param string $etiqueta_html El contenido HTML de la etiqueta. Este parámetro es obligatorio y se utiliza para
     *                              la parte visible del enlace (por ejemplo, texto).
     * @param string $icon_html El contenido HTML del ícono. Este parámetro es opcional y se usa para mostrar un ícono
     *                          dentro del enlace.
     * @param string $params Los parámetros del enlace (`<a>`), como `href`, `role`, `style`, etc. Este parámetro es
     *                       obligatorio y debe contener una cadena de atributos HTML válidos.
     *
     * **Retorno:**
     * - Devuelve una cadena con el enlace (`<a>`) generado si todo es válido.
     * - Si alguno de los parámetros está vacío o si ocurre un error, se devuelve un mensaje de error.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de un enlace**
     * ```php
     * $etiqueta_html = "Hacer clic aquí";
     * $icon_html = "<span class='fa fa-check'></span>";
     * $params = "href='https://www.example.com' role='button'";
     * $resultado = $this->a_role_button($etiqueta_html, $icon_html, $params);
     * // Retorna: "<a href='https://www.example.com' role='button'><span class='fa fa-check'></span> Hacer clic aquí</a>"
     * ```
     *
     * **Ejemplo 2: Error debido a parámetros vacíos**
     * ```php
     * $etiqueta_html = "";
     * $icon_html = "<span class='fa fa-check'></span>";
     * $params = "href='https://www.example.com' role='button'";
     * $resultado = $this->a_role_button($etiqueta_html, $icon_html, $params);
     * // Retorna: "Error al data_a esta vacio"
     * ```
     *
     * **Ejemplo 3: Error debido a `params` vacío**
     * ```php
     * $etiqueta_html = "Hacer clic aquí";
     * $icon_html = "<span class='fa fa-check'></span>";
     * $params = "";
     * $resultado = $this->a_role_button($etiqueta_html, $icon_html, $params);
     * // Retorna: "Error al params esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function a_role_button(string $etiqueta_html, string $icon_html, string $params): string|array
    {
        // Recortar los valores de los parámetros
        $etiqueta_html = trim($etiqueta_html);
        $icon_html = trim($icon_html);
        $params = trim($params);

        // Validar que los parámetros no estén vacíos
        if ($params === '') {
            return $this->error->error(mensaje: 'Error al params esta vacio', data: $params, es_final: true);
        }

        // Combinar el ícono y la etiqueta en el contenido del enlace
        $data_a = $icon_html . ' ' . $etiqueta_html;
        $data_a = trim($data_a);

        // Validar que el contenido no esté vacío
        if ($data_a === '') {
            return $this->error->error(mensaje: 'Error al data_a esta vacio', data: $data_a, es_final: true);
        }

        // Crear el enlace HTML
        $a = "<a $params>$data_a</a>";

        // Eliminar redundancias de espacios
        $i = 0;
        while ($i <= 5) {
            $a = str_replace('  ', ' ', $a);
            $i++;
        }

        // Retornar el enlace HTML generado
        return $a;
    }


    /**
     * Genera los inputs base de un alta de cualquier controller que herede
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.16.5
     */
    final public function alta(system $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        $cols = new stdClass();
        $cols->codigo = 6;
        $cols->codigo_bis = 6;
        $inputs_base = $this->inputs_base(cols: $cols, controler: $controler, value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }


    /**
     * Integra un boton link para rows de lista
     * @param array $accion_permitida Datos de accion
     * @param int $indice Indice de matriz de rows
     * @param int $registro_id Registro en proceso
     * @param array $rows registros
     * @param array $params Extraparams
     * @return array
     * @version 0.165.34
     */
    final public function boton_link_permitido(array $accion_permitida, int $indice, int $registro_id, array $rows,
                                               array $params = array()): array
    {
        $valida = $this->validacion->valida_boton_link(
            accion_permitida: $accion_permitida, indice: $indice, registro_id: $registro_id, rows: $rows);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }
        $valida = $this->valida_boton_data_accion(accion_permitida: $accion_permitida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar accion_permitida', data: $valida);
        }


        $style = $this->style_btn(accion_permitida: $accion_permitida, row: $rows[$indice]);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener style', data: $style);
        }

        $accion = $accion_permitida['adm_accion_descripcion'];
        $etiqueta = $accion_permitida['adm_accion_titulo'];
        $seccion = $accion_permitida['adm_seccion_descripcion'];
        $id_css = '';
        $css_extra = '';
        $onclick_event = '';
        if(isset($accion_permitida['adm_accion_id_css'])) {
            $id_css = $accion_permitida['adm_accion_id_css'];
            $css_extra = $accion_permitida['adm_accion_id_css'];
            $onclick_event = $accion_permitida['adm_accion_id_css'];
        }
        $icon = $accion_permitida['adm_accion_icono'];


        $data_icon = (new params())->data_icon(adm_accion: $accion_permitida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }


        $link = $this->button_href(accion: $accion, etiqueta: $etiqueta, registro_id: $registro_id, seccion: $seccion,
            style: $style, css_extra: $css_extra, cols: -1, icon: $icon, id_css: $id_css,
            muestra_icono_btn: $data_icon->muestra_icono_btn, muestra_titulo_btn: $data_icon->muestra_titulo_btn,
            onclick_event: $onclick_event, params: $params, styles: array('margin-right' => '2px'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        if (!is_array($rows[$indice])) {
            return $this->error->error(mensaje: 'rows[' . $indice . '] debe ser una array', data: $rows);
        }

        if (!isset($rows[$indice]['acciones'])) {
            $rows[$indice]['acciones'] = array();
        }

        if (array_key_exists($accion_permitida['adm_accion_descripcion'], $rows[$indice]['acciones'])) {
            return $this->error->error(mensaje: 'Error la accion esta repetida', data: $accion_permitida);
        }

        $rows[$indice]['acciones'][$accion_permitida['adm_accion_descripcion']] = $link;

        return $rows;
    }

    final public function boton_submit(string $class_button, string $class_control, string $style, string $tag,
                                       string $id_button = ''): string
    {
        return "
            <div class='control-group $class_control'>
                <div class='controls'>
                    <button type='submit' class='btn btn-$style $class_button' id='$id_button'>$tag</button>
                </div>
            </div>";
    }

    /**
     * REG
     * Genera un botón de enlace (`<a>`) con los atributos proporcionados y los parámetros personalizados.
     *
     * Esta función genera un enlace HTML (`<a>`) que actúa como un botón, con la posibilidad de agregar íconos, etiquetas,
     * estilos personalizados, y parámetros adicionales para el enlace. Además, valida los parámetros de entrada antes de
     * generar el enlace final. Si algún parámetro es inválido o falta, la función genera un mensaje de error detallado.
     *
     * **Pasos de procesamiento:**
     * 1. Se valida que los parámetros `accion`, `etiqueta`, `seccion`, y `style` sean correctos.
     * 2. Se obtiene el ID de sesión del usuario.
     * 3. Se generan los parámetros del botón, como el ícono y la etiqueta, utilizando la función `params_btn`.
     * 4. Se genera el enlace (`<a>`) con la URL correspondiente utilizando la función `link_a`.
     * 5. Se generan los parámetros del enlace, como la clase, el `role`, el estilo, etc., con la función `a_role`.
     * 6. Se retorna el enlace HTML generado o un mensaje de error si alguno de los pasos falla.
     *
     * **Parámetros:**
     *
     * @param string $accion La acción a realizar cuando se haga clic en el botón. Ejemplo: "guardar".
     * @param string $etiqueta El texto que se mostrará en el botón. Ejemplo: "Guardar cambios".
     * @param int $registro_id El ID del registro asociado a la acción.
     * @param string $seccion El nombre de la sección donde se realiza la acción. Ejemplo: "usuarios".
     * @param string $style El estilo CSS del botón (por ejemplo, "primary", "danger").
     * @param string $css_extra Clases CSS adicionales para el botón (opcional).
     * @param int $cols Número de columnas para el botón en un diseño basado en la grilla de Bootstrap (opcional, por defecto 12).
     * @param string $icon El ícono HTML que se mostrará en el botón (opcional).
     * @param string|null $id_css El ID CSS del botón (opcional).
     * @param bool $muestra_icono_btn Determina si se debe mostrar el ícono en el botón. Si es `true`, se muestra el ícono.
     * @param bool $muestra_titulo_btn Determina si se debe mostrar el título en el botón. Por defecto es `true`.
     * @param string $onclick_event El código JavaScript que se ejecutará al hacer clic en el botón (opcional).
     * @param array $params Parámetros adicionales que se incluirán en la URL del botón como parámetros GET (opcional).
     * @param string $role El valor del atributo `role` del botón. Por defecto es `"button"`.
     * @param array $styles Estilos adicionales personalizados que se aplicarán al botón.
     * @param string $target El atributo `target` del enlace, que determina cómo se abrirá el destino (opcional).
     *
     * **Retorno:**
     * - Devuelve un enlace HTML completo si todo es válido.
     * - Si ocurre un error en cualquiera de los pasos, devuelve un mensaje de error detallado.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de un botón de enlace**
     * ```php
     * $accion = "guardar";
     * $etiqueta = "Guardar cambios";
     * $registro_id = 123;
     * $seccion = "usuarios";
     * $style = "primary";
     * $css_extra = "extra-class";
     * $cols = 6;
     * $icon = "<span class='fa fa-check'></span>";
     * $id_css = "guardarBtn";
     * $muestra_icono_btn = true;
     * $muestra_titulo_btn = true;
     * $onclick_event = "handleClick()";
     * $params = ['redirigir' => 'true'];
     * $role = "button";
     * $styles = ['color' => 'red'];
     * $target = "_blank";
     *
     * $resultado = $this->button_href($accion, $etiqueta, $registro_id, $seccion, $style, $css_extra, $cols, $icon, $id_css,
     * $muestra_icono_btn, $muestra_titulo_btn, $onclick_event, $params, $role, $styles, $target);
     * // Retorna: "<a role='button' title='Guardar cambios' href='index.php?accion=guardar&seccion=usuarios&registro_id=123&session_id=abc123&adm_menu_id=-1&redirigir=true' class='btn btn-primary col-sm-6 extra-class' style='color: red;' id='guardarBtn' target='_blank' onclick='handleClick()'><span class='fa fa-check'></span> Guardar cambios</a>"
     * ```
     *
     * **Ejemplo 2: Error debido a un parámetro vacío**
     * ```php
     * $accion = "";
     * $etiqueta = "Guardar cambios";
     * $registro_id = 123;
     * $seccion = "usuarios";
     * $style = "primary";
     * $resultado = $this->button_href($accion, $etiqueta, $registro_id, $seccion, $style);
     * // Retorna: "Error al validar datos"
     * ```
     *
     * **@version 1.0.0**
     */
    final public function button_href(string $accion, string $etiqueta, int $registro_id, string $seccion,
                                      string $style, string $css_extra = '', int $cols = 12, string $icon = '',
                                      string|null $id_css = '', bool $muestra_icono_btn = false,
                                      bool $muestra_titulo_btn = true, string $onclick_event = '',
                                      array $params = array(), string $role = 'button', array $styles = array(),
                                      string $target = ''): string|array
    {
        // Validaciones
        if (is_null($id_css)) {
            $id_css = '';
        }

        $valida = $this->html_base->valida_input(accion: $accion, etiqueta: $etiqueta, seccion: $seccion, style: $style);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $session_id = (new generales())->session_id;

        if ($session_id === '') {
            return $this->error->error(mensaje: 'Error la $session_id esta vacia', data: $session_id);
        }

        // Generación de parámetros del botón
        $params_btn = $this->params_btn(icon: $icon, etiqueta: $etiqueta, muestra_icono_btn: $muestra_icono_btn,
            muestra_titulo_btn: $muestra_titulo_btn, params: $params);

        if (errores::$error) {
            $params_error = array();
            $params_error['accion'] = $accion;
            $params_error['seccion'] = $seccion;
            $params_error['muestra_titulo_btn'] = $muestra_titulo_btn;
            $params_error['icon'] = $icon;
            return $this->error->error(mensaje: 'Error al generar parametros de btn', data: $params_btn,
                params: $params_error);
        }

        // Generación del enlace
        $link = $this->link_a(accion: $accion, params_get: $params_btn->params_get, registro_id: $registro_id,
            seccion: $seccion, session_id: $session_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar link', data: $link);
        }

        // Generación del botón con enlace
        $a = $this->a_role(css_extra: $css_extra, cols: $cols, etiqueta_html: $params_btn->etiqueta_html,
            icon_html: $params_btn->icon_html, id_css: $id_css, link: $link, onclick_event: $onclick_event,
            role: $role, style: $style, styles: $styles, target: $target, title: $etiqueta);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar a', data: $a);
        }

        return $a;
    }


    /**
     * Genera un boton para ser utilizado con java
     * @param string $id_css Identificador css
     * @param string $style Estilo del boton
     * @param string $tag Etiqueta del boton
     * @return string|array
     * @version 11.9.0
     */
    final public function button_para_java(string $id_css, string $style, string $tag): string|array
    {
        $style = trim($style);
        if ($style === '') {
            return $this->error->error(mensaje: 'Error style esta vacio', data: $style);
        }
        $id_css = trim($id_css);
        if ($id_css === '') {
            return $this->error->error(mensaje: 'Error id_css esta vacio', data: $id_css);
        }
        $tag = trim($tag);
        if ($tag === '') {
            return $this->error->error(mensaje: 'Error tag esta vacio', data: $tag);
        }
        return "<a class='btn btn-$style' role='button' id='$id_css'>$tag</a>";

    }

    /**
     * REG
     * Genera una clase CSS para las columnas en una estructura de diseño basada en la grilla de Bootstrap.
     *
     * Esta función genera una clase CSS de tipo `col-sm-{cols}`, que es utilizada en el sistema de grillas de Bootstrap.
     * La clase generada es utilizada para controlar el tamaño de las columnas dentro de una fila en un diseño responsivo.
     * Si el parámetro `$cols` es igual a `-1`, la función retornará una cadena vacía, indicando que no se debe aplicar ninguna clase.
     *
     * **Pasos de procesamiento:**
     * 1. Se genera la clase CSS `col-sm-{cols}`, donde `{cols}` es el valor de `$cols`.
     * 2. Si el valor de `$cols` es igual a `-1`, se retorna una cadena vacía para indicar que no se debe aplicar la clase.
     * 3. Se devuelve la clase CSS generada o una cadena vacía si se aplica el valor especial `-1`.
     *
     * **Parámetros:**
     *
     * @param int $cols El número de columnas que se utilizarán en el diseño. Este parámetro es obligatorio y debe ser un valor entero.
     *                  Si es `-1`, se devuelve una cadena vacía, indicando que no se debe aplicar ninguna clase de columna.
     *
     * **Retorno:**
     * - Devuelve una cadena con la clase CSS correspondiente para las columnas de Bootstrap.
     * - Si el valor de `$cols` es `-1`, retorna una cadena vacía.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación de una clase CSS para 4 columnas**
     * ```php
     * $cols = 4;
     * $resultado = $this->cols_html($cols);
     * // Retorna: "col-sm-4"
     * ```
     *
     * **Ejemplo 2: No aplicar clase si $cols es -1**
     * ```php
     * $cols = -1;
     * $resultado = $this->cols_html($cols);
     * // Retorna: ""
     * ```
     *
     * **@version 1.0.0**
     */
    private function cols_html(int $cols): string
    {
        $cols_html = "col-sm-$cols";
        if ($cols === -1) {
            $cols_html = '';
        }
        return $cols_html;
    }


    /**
     * Genera los inputs de tipo fechas date
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de inputs
     * @return array|stdClass
     */
    final protected function dates_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $dates = new stdClass();

        foreach ($campos_view['dates'] as $item) {

            $item = trim($item);
            if (is_numeric($item)) {
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }

            $params_select = (new params())->params_select_init(item: $item, keys_selects: $keys_selects);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->dates_template(directivas: $this->directivas, params_select: $params_select, row_upd: $row_upd);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $dates->$item = $date;
        }

        return $dates;
    }

    private function div_input_text(array  $class_css, int $cols, bool $disabled, array $ids_css, string $name,
                                    string $place_holder, string $regex, bool $required, stdClass $row_upd,
                                    string $title, bool $value_vacio, string|null $value = ''): array|string
    {

        $valida = $this->directivas->valida_data_label(name: $name, place_holder: $place_holder);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $html = $this->directivas->input_text_base(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, class_css: $class_css, ids_css: $ids_css, regex: $regex,
            required: $required, title: $title, value: $value);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }
        return $div;
    }

    /**
     * Integra un input de texto required en un div
     * @param int $cols Columnas css
     * @param bool $disabled attr disabled
     * @param array $ids_css Identificadores extra de id
     * @param string $name name input
     * @param string $place_holder etiqueta input
     * @param string $regex integra un regex a un pattern
     * @param stdClass $row_upd registro en proceso
     * @param string $title title de input
     * @param bool $value_vacio valor vacio
     * @return array|string
     * @version 7.43.2
     */
    private function div_input_text_required(int    $cols, bool $disabled, array $ids_css, string $name,
                                             string $place_holder, string $regex, stdClass $row_upd,
                                             string $title, bool $value_vacio): array|string
    {

        $valida = $this->directivas->valida_data_label(name: $name, place_holder: $place_holder);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $html = $this->directivas->input_text_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, ids_css: $ids_css, regex: $regex, title: $title);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }
        return $div;
    }

    /**
     * Genera los email de una view
     * @param modelo $modelo Datos del modelo
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects parametros
     * @return array|stdClass
     * @version 5.1.0
     * @final rev
     */
    protected function emails_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $emails = new stdClass();

        foreach ($campos_view['emails'] as $item) {

            $params_select = (new params())->params_select_init(item: $item, keys_selects: $keys_selects);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->emails_template(directivas: $this->directivas, params_select: $params_select, row_upd: $row_upd);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $emails->$item = $date;
        }

        return $emails;
    }

    /**
     * REG
     * Genera el texto HTML de una etiqueta, validando si debe mostrarse o no.
     *
     * Esta función recibe una etiqueta y un parámetro que indica si la etiqueta debe mostrarse o no.
     * Si se debe mostrar la etiqueta (`$muestra_titulo_btn` es `true`), la función valida que el valor de la etiqueta
     * no esté vacío. Si la etiqueta está vacía cuando se requiere mostrarla, se genera un error. Si todo es válido,
     * la función devuelve el valor de la etiqueta, de lo contrario se devuelve un mensaje de error.
     *
     * **Pasos de procesamiento:**
     * 1. Si `$muestra_titulo_btn` es `true`, se valida que el valor de la etiqueta no esté vacío.
     * 2. Si el valor de la etiqueta está vacío, se genera un mensaje de error.
     * 3. Si todo es válido, se devuelve el valor de la etiqueta.
     * 4. Si `$muestra_titulo_btn` es `false`, no se devuelve nada (cadena vacía).
     *
     * **Parámetros:**
     *
     * @param string $etiqueta El texto que se mostrará como etiqueta. Este parámetro es obligatorio y debe ser una cadena.
     * @param bool $muestra_titulo_btn Determina si se debe mostrar la etiqueta. Si es `true`, se valida y muestra el texto de la etiqueta.
     *                                 Si es `false`, no se mostrará ningún texto.
     *
     * **Retorno:**
     * - Devuelve el texto de la etiqueta si `$muestra_titulo_btn` es `true` y la etiqueta no está vacía.
     * - Si la etiqueta está vacía y `$muestra_titulo_btn` es `true`, devuelve un mensaje de error.
     * - Si `$muestra_titulo_btn` es `false`, devuelve una cadena vacía.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de etiqueta**
     * ```php
     * $etiqueta = "Guardar cambios";
     * $muestra_titulo_btn = true;
     * $resultado = $this->etiqueta_html($etiqueta, $muestra_titulo_btn);
     * // Retorna: "Guardar cambios"
     * ```
     *
     * **Ejemplo 2: Error cuando la etiqueta está vacía**
     * ```php
     * $etiqueta = "";
     * $muestra_titulo_btn = true;
     * $resultado = $this->etiqueta_html($etiqueta, $muestra_titulo_btn);
     * // Retorna: 'Error si muestra_titulo_btn entonces etiqueta no puede venir vacio'
     * ```
     *
     * **Ejemplo 3: No mostrar etiqueta**
     * ```php
     * $etiqueta = "Guardar cambios";
     * $muestra_titulo_btn = false;
     * $resultado = $this->etiqueta_html($etiqueta, $muestra_titulo_btn);
     * // Retorna: ""
     * ```
     *
     * **@version 1.0.0**
     */
    private function etiqueta_html(string $etiqueta, bool $muestra_titulo_btn): array|string
    {
        $etiqueta_html = '';
        if ($muestra_titulo_btn) {
            $etiqueta = trim($etiqueta);
            if ($etiqueta === '') {
                return $this->error->error(
                    mensaje: 'Error si muestra_titulo_btn entonces etiqueta no puede venir vacio', data: $etiqueta);
            }
            $etiqueta_html = $etiqueta;
        }
        return $etiqueta_html;
    }


    /**
     * Genera los inputs de fecha
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Keys params
     * @return array|stdClass
     * @final rev
     * @version 7.38.2
     */
    protected function fechas_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $fechas = new stdClass();

        foreach ($campos_view['fechas'] as $item) {

            $params_select = (new params())->params_select_init(item: $item, keys_selects: $keys_selects);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $fecha = (new template())->fechas_template(directivas: $this->directivas, params_select: $params_select, row_upd: $row_upd);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar input', data: $fecha);
            }
            $fechas->$item = $fecha;
        }

        return $fechas;
    }

    /**
     * Genera un input de tipo file
     * @param array $campos_view campos de modelos para views
     * @param array $keys_selects parametros de selectores
     * @param stdClass $row_upd Registro en proceso
     * @return array|stdClass
     * @version 0.292.39
     */
    private function file_items(array $campos_view, array $keys_selects, stdClass $row_upd): array|stdClass
    {
        if (!isset($campos_view['files'])) {
            $campos_view['files'] = array();
        }

        if (!is_array($campos_view['files'])) {
            return $this->error->error(mensaje: 'Error campos_view[files] debe ser un array', data: $campos_view);
        }
        $files = new stdClass();
        foreach ($campos_view['files'] as $item) {
            $item = trim($item);
            if (is_numeric($item)) {
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }
            if ($item === '') {
                return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
            }

            $files = $this->text_item(item: $item, keys_selects: $keys_selects, row_upd: $row_upd, texts: $files);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar input', data: $files);
            }
        }
        return $files;
    }

    /**
     * Genera los inputs de tipo file
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de inputs
     * @return array|stdClass
     * @version 0.293.39
     */

    final protected function files_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $files = $this->file_items(campos_view: $campos_view, keys_selects: $keys_selects, row_upd: $row_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $files);
        }

        return $files;
    }

    /**
     * Integra un input de tipo FILE
     * @param stdClass $params_select Parametros de input
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 0.290.39
     */
    public function file_template(stdClass $params_select, stdClass $row_upd): array|string
    {
        $keys = array('cols', 'disabled', 'name', 'place_holder', 'required', 'value_vacio');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $params_select, valida_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = (new validacion())->valida_ids(keys: $keys, registro: $params_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('name', 'place_holder');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $params_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $valida = $this->directivas->valida_cols(cols: $params_select->cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }


        $html = $this->directivas->input_file(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder, required: $params_select->required, row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $params_select->cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * REG
     * Genera un atributo `style` personalizado a partir de un conjunto de propiedades CSS.
     *
     * Esta función toma un arreglo de propiedades CSS y sus valores, genera la cadena correspondiente a las propiedades
     * utilizando la función `propiedades_css` y luego encapsula esas propiedades en un atributo `style` utilizando la
     * función `style_custom`. Si alguna de las propiedades o valores es inválido, se genera un mensaje de error.
     *
     * **Pasos de procesamiento:**
     * 1. Se generan las propiedades CSS a partir del arreglo `$styles` utilizando la función `propiedades_css`.
     * 2. Si la validación de propiedades falla, se retorna un mensaje de error.
     * 3. Se genera el atributo `style` con las propiedades CSS utilizando la función `style_custom`.
     * 4. Si ocurre un error al generar el atributo `style`, se retorna un mensaje de error.
     * 5. Si todo es válido, se retorna el atributo `style` personalizado generado.
     *
     * **Parámetros:**
     *
     * @param array $styles Un arreglo asociativo que contiene las propiedades CSS como claves y los valores correspondientes.
     *                      Por ejemplo, `['color' => 'red', 'font-size' => '12px']`.
     *
     * **Retorno:**
     * - Devuelve el atributo `style` en formato HTML si las propiedades son válidas.
     * - Si ocurre un error durante la generación de las propiedades o del `style`, se devuelve un mensaje de error.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa del atributo `style` personalizado**
     * ```php
     * $styles = ['color' => 'red', 'font-size' => '12px'];
     * $resultado = $this->genera_styles_custom($styles);
     * // Retorna: "style='color: red; font-size: 12px;'"
     * ```
     *
     * **Ejemplo 2: Error al generar el atributo `style`**
     * ```php
     * $styles = ['color' => '', 'font-size' => '12px'];
     * $resultado = $this->genera_styles_custom($styles);
     * // Retorna: "Error al generar propiedades"
     * ```
     *
     * **@version 1.0.0**
     */
    private function genera_styles_custom(array $styles)
    {
        // Generar propiedades CSS
        $propiedades = $this->propiedades_css(styles: $styles);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar propiedades', data: $propiedades);
        }

        // Generar el atributo style
        $style_custom = $this->style_custom(propiedades: $propiedades);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar style_custom', data: $style_custom);
        }

        // Retornar el atributo style personalizado
        return $style_custom;
    }


    /**
     * Integra un header collapsible
     * @param string $id_css_button Identificador css
     * @param string $style_button Estilo de boton
     * @param string $tag_button Etiqueta de boton
     * @param string $tag_header Etiqueta de seccion
     * @return array|string
     */
    public function header_collapsible(string $id_css_button, string $style_button, string $tag_button,
                                             string $tag_header, array $acciones_headers = array(),
                                             string $n_apartado = ''): array|string
    {
        $style_button = trim($style_button);
        if($style_button === ''){
            return $this->error->error(mensaje: 'Error style_button esta vacio', data: $style_button);
        }
        $id_css_button = trim($id_css_button);
        if($id_css_button === ''){
            return $this->error->error(mensaje: 'Error id_css_button esta vacio', data: $id_css_button);
        }
        $tag_button = trim($tag_button);
        if($tag_button === ''){
            return $this->error->error(mensaje: 'Error tag_button esta vacio', data: $tag_button);
        }

        $btn = $this->button_para_java(id_css: $id_css_button,style:  $style_button,tag:  $tag_button);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al btn_collapse_all',data:  $btn);
        }

        $html_acc = "";
        foreach ($acciones_headers as $n_part => $accion){
            if((string)$n_part === $n_apartado){
                $btn_acc = $this->button_para_java(id_css: $accion->id_css_button_acc,
                    style:  $accion->style_button_acc,tag:  $accion->tag_button_acc);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al btn_collapse_all',data:  $btn);
                }
                $html_acc .= $btn_acc;
            }
        }

        $html = "<div class='col-md-12'>";
        $html .= "<hr><h4>$tag_header $btn $html_acc</h4><hr>";
        $html .= "</div>";

        return trim($html);
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera un campo hidden en un formulario HTML
     *
     * @param string $name Nombre del campo hidden
     * @param string $value Valor del campo hidden
     *
     * @return array|string Retorna una cadena que contiene el elemento hidden si todo está bien,
     * de lo contrario retorna un array con el error
     * @version 20.25.0
     */
    final public function hidden(string $name, string $value): array|string
    {
        $data_err = new stdClass();
        $data_err->name = $name;
        $data_err->value = $value;

        $name = trim($name);
        if($name === ''){
            return $this->error->error(mensaje: 'Error name esta vacio',data:  $data_err, es_final: true);
        }
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data:  $data_err, es_final: true);
        }
        
        return "<input type='hidden' name='$name' value='$value'>";
    }

    protected function init_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $selects = $this->selects_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    /**
     * REG
     * Genera el HTML de un ícono en formato `span` con una clase CSS dinámica.
     *
     * Esta función toma un nombre de ícono y un parámetro que determina si se debe mostrar o no el ícono.
     * Si se indica que se debe mostrar el ícono (`$muestra_icono_btn` es `true`), la función genera un elemento HTML `span`
     * con la clase correspondiente al ícono proporcionado. Si el nombre del ícono está vacío cuando se requiere mostrarlo,
     * se genera un error. Si todo es válido, se devuelve el HTML del ícono; de lo contrario, se devuelve un mensaje de error.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorta el valor del ícono para eliminar espacios adicionales al principio y al final.
     * 2. Si `$muestra_icono_btn` es `true`:
     *    - Se valida que el nombre del ícono no esté vacío.
     *    - Si el nombre del ícono está vacío, se genera un mensaje de error.
     *    - Si el nombre del ícono es válido, se genera el HTML con el ícono.
     * 3. Si `$muestra_icono_btn` es `false`, no se genera ningún ícono y se retorna una cadena vacía.
     * 4. Si todo es válido, se retorna el HTML del ícono.
     * 5. Si se genera un error, se retorna un mensaje de error detallado.
     *
     * **Parámetros:**
     *
     * @param string $icon El nombre del ícono. Este parámetro es obligatorio y debe ser una cadena que representa el ícono.
     *                     Por ejemplo, "fa-user" o "fa-home" si se utilizan íconos de FontAwesome.
     * @param bool $muestra_icono_btn Determina si se debe mostrar el ícono. Este parámetro es obligatorio.
     *                                Si es `true`, se genera el ícono; si es `false`, no se genera ningún ícono.
     *
     * **Retorno:**
     * - Devuelve una cadena con el HTML del ícono (como un `<span class="nombre_del_icono"></span>`) si todo es válido.
     * - Si el ícono debe mostrarse pero está vacío, devuelve un mensaje de error.
     * - Si `$muestra_icono_btn` es `false`, devuelve una cadena vacía.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de ícono**
     * ```php
     * $icon = "fa-user";
     * $muestra_icono_btn = true;
     * $resultado = $this->icon_html($icon, $muestra_icono_btn);
     * // Retorna: "<span class='fa-user'></span>"
     * ```
     *
     * **Ejemplo 2: Error cuando el nombre del ícono está vacío**
     * ```php
     * $icon = "";
     * $muestra_icono_btn = true;
     * $resultado = $this->icon_html($icon, $muestra_icono_btn);
     * // Retorna: 'Error si muestra_icono_btn entonces icon no puede venir vacio'
     * ```
     *
     * **Ejemplo 3: No mostrar ícono**
     * ```php
     * $icon = "fa-user";
     * $muestra_icono_btn = false;
     * $resultado = $this->icon_html($icon, $muestra_icono_btn);
     * // Retorna: ""
     * ```
     *
     * **@version 1.0.0**
     */
    private function icon_html(string $icon, bool $muestra_icono_btn): array|string
    {
        $icon = trim($icon);
        $icon_html = '';
        if($muestra_icono_btn){
            $icon = trim($icon);
            if($icon === ''){
                return $this->error->error(mensaje: 'Error si muestra_icono_btn entonces icon no puede venir vacio',
                    data: $icon);
            }
            $icon_html = "<span class='$icon'></span>";
        }
        return $icon_html;
    }


    /**
     * Integra los inputs para frontend
     * @param stdClass $row_upd Registro en proceso
     * @param modelo $modelo Modelo en proceso
     * @param array $keys_selects Parametros visuales de inputs
     * @return array|stdClass
     *
     */
    final public function init_alta2(stdClass $row_upd, modelo $modelo,
                                     array $keys_selects = array()): array|stdClass
    {
        $selects = $this->selects_alta2(modelo: $modelo, keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta2(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $textareas = $this->textareas_alta2(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $textareas);
        }

        $files = $this->files_alta2(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar files', data: $texts);
        }

        $dates = $this->dates_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar dates', data: $dates);
        }

        $passwords = $this->passwords_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar passwords', data: $dates);
        }

        $telefonos = $this->telefonos_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar telefonos', data: $dates);
        }

        $emails = $this->emails_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emails', data: $dates);
        }
        $fechas = $this->fechas_alta(modelo: $modelo,row_upd: $row_upd,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar emails', data: $dates);
        }

        $fields = array();
        $fields['selects'] = $selects;
        $fields['inputs'] = $texts;
        $fields['textareas'] = $textareas;
        $fields['files'] = $files;
        $fields['dates'] = $dates;
        $fields['passwords'] = $passwords;
        $fields['telefonos'] = $telefonos;
        $fields['emails'] = $emails;
        $fields['fechas'] = $fechas;

        return $fields;
    }


    /**
     * Genera un input de tipo codigo
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.72.32
     */
    public function input_codigo(int $cols, stdClass $row_upd, bool $value_vacio,bool $disabled = false,
                                 string $place_holder = 'Código'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'codigo',
            place_holder: $place_holder,row_upd: $row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo codigo bis
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.73.32
     */
    public function input_codigo_bis(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                 string $place_holder = 'Código BIS'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'codigo_bis',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo descripcion
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $con_label Si no con label deja vacio el input
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.74.32
     */
    public function input_descripcion(int $cols, stdClass $row_upd, bool $value_vacio, bool $con_label = true,
                                      bool $disabled = false, string $place_holder = 'Descripcion'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'descripcion',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, con_label: $con_label);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * REG
     * Genera un input tipo fecha o datetime-local (si se habilita `value_hora`) dentro de un `div` de tamaño de columnas especificado.
     * Este input se genera a través de la clase `directivas`, incluyendo validaciones de parámetros y construcción completa del HTML.
     *
     * @param int $cols Número de columnas Bootstrap (1 a 12) para el `div` contenedor del input.
     * @param stdClass $row_upd Objeto con valores preexistentes que pueden llenar el input. Debe contener al menos la propiedad con nombre igual al parámetro `$name`.
     * @param bool $value_vacio Si es `true`, se vacía el valor del campo en `$row_upd->$name` para forzar un valor en blanco.
     * @param bool $disabled Si es `true`, el input se genera como deshabilitado (`disabled`).
     * @param string $name Nombre del input (`name` y `id`). También se usa para recuperar el valor desde `$row_upd`.
     * @param string $place_holder Texto que se muestra como placeholder del input. Si está vacío, se genera a partir del nombre.
     * @param bool $required Si es `true`, se agrega el atributo `required` al input.
     * @param mixed $value Valor que se usará como contenido del input si no se toma de `$row_upd`.
     * @param bool $value_hora Si es `true`, se genera un input tipo `datetime-local`, si es `false`, tipo `date`.
     *
     * @return array|string Devuelve el HTML generado como string o un array con información del error en caso de falla.
     *
     * @example Ejemplo de entrada:
     * ```php
     * $row_upd = new stdClass();
     * $row_upd->fecha = '2025-03-20';
     * $html = $html_controler->input_fecha(
     *     cols: 6,
     *     row_upd: $row_upd,
     *     value_vacio: false,
     *     disabled: false,
     *     name: 'fecha',
     *     place_holder: 'Fecha del evento',
     *     required: true,
     *     value: null,
     *     value_hora: false
     * );
     * ```
     *
     * @example Ejemplo de salida:
     * ```html
     * <div class='control-group col-sm-6'>
     *     <label class='control-label' for='fecha'>Fecha del evento</label>
     *     <input type='date' name='fecha' value='2025-03-20' class='form-control' required id='fecha' placeholder='Fecha del evento' />
     * </div>
     * ```
     */
    public function input_fecha(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                string $name ='fecha', string $place_holder = 'Fecha', bool $required = true,
                                mixed $value = null, bool $value_hora = false): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols, es_final: true );
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols, es_final: true);
        }

        $html =$this->directivas->input_fecha_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, required: $required, value: $value, value_hora: $value_hora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera un input de tipo ID
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled si disabled deshabilita input
     * @param string $place_holder etiqueta a mostrar
     * @return array|string
     * @version 0.75.32
     */
    public function input_id(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                             string $place_holder = 'Id'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cols debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'id',place_holder:$place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Obtiene un input de tipo monto
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $con_label Si con label integra el label en el input
     * @param bool $disabled Si disabled integra atributo disabled en input
     * @param string $name Name del input
     * @param string $place_holder Info input
     * @param mixed|null $value Valor default
     * @return array|string
     * @version 8.63.0
     *
     */
    public function input_monto(int $cols, stdClass $row_upd, bool $value_vacio,bool $con_label = true,
                                bool $disabled = false, string $name = 'monto', string $place_holder = 'Monto',
                                mixed $value = null): array|string
    {

        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_monto_required(disabled: $disabled, name: $name, place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio, con_label: $con_label, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_numero_int(int $cols, string $name, stdClass $row_upd, bool $value_vacio,
                                     bool $disabled = false, array $ids_css = array(), string $place_holder = 'Numero',
                                     string $title = 'Numero'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cols debe ser menor o igual a  12', data: $cols);
        }

        $regex = $this->validacion->patterns['entero_positivo_html'];

        $div = $this->div_input_text_required(cols: $cols, disabled: $disabled, ids_css: $ids_css, name: $name,
            place_holder: $place_holder, regex: $regex, row_upd: $row_upd, title: $title, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * @param stdClass $cols Objeto con la definicion del numero de columnas a integrar en un input base
     * @version 0.11.5
     * @param system $controler
     * @param bool $value_vacio
     * @return array|stdClass
     */
    final protected function inputs_base(stdClass $cols, controler $controler, bool $value_vacio): array|stdClass
    {

        $keys = array('codigo','codigo_bis');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        $valida = (new validacion())->valida_numerics(keys: $keys, row: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }
        if(empty($controler->inputs)){
            $controler->inputs = new stdClass();
        }

        $html_codigo = $this->directivas->input_codigo(cols: $cols->codigo,row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }

        $controler->inputs->codigo = $html_codigo;

        $html_codigo_bis = $this->directivas->input_codigo_bis(cols: $cols->codigo_bis,
            row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }

        $controler->inputs->codigo_bis = $html_codigo_bis;

        $html_descripcion = $this->directivas->input_descripcion(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion);
        }
        $controler->inputs->descripcion = $html_descripcion;

        $html_alias = $this->directivas->input_alias(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_alias);
        }
        $controler->inputs->alias = $html_alias;

        $html_descripcion_select = $this->directivas->input_descripcion_select(row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion_select);
        }
        $controler->inputs->descripcion_select = $html_descripcion_select;

        return $controler->inputs;
    }

    public function input_file(int $cols, string $name, stdClass $row_upd, bool $value_vacio,bool $disabled = false,
                               string $place_holder = 'Documento', bool $required = true, bool $multiple = false): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_file(disabled: $disabled, name: $name, place_holder: $place_holder,
            required: $required, row_upd: $row_upd, value_vacio: $value_vacio,multiple: $multiple);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    final public function input_text(int $cols, bool $disabled, string $name, string $place_holder, stdClass $row_upd,
                                     bool $value_vacio, array $class_css = array(), array $ids_css = array(),
                                     string $regex = '', bool $required = true, string $title = '',
                                     string|null $value = ''): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $class_css[] = 'form-control';
        $class_css[] = $name;

        $div = $this->div_input_text(class_css: $class_css, cols: $cols, disabled: $disabled,
            ids_css: $ids_css, name: $name, place_holder: $place_holder, regex: $regex, required: $required,
            row_upd: $row_upd, title: $title, value_vacio: $value_vacio, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }


    /**
     * Genera un input text required
     * @param int $cols N columnas css
     * @param bool $disabled attr disable
     * @param string $name Name input
     * @param string $place_holder Tag Input
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio si vacio no valida
     * @param array $ids_css Identificadores extra id de css y java
     * @param string $regex integra atributo pattern
     * @param string $title integra un title a input
     * @return array|string
     * @version 11.1.0
     */
    final public function input_text_required(int $cols, bool $disabled, string $name, string $place_holder,
                                              stdClass $row_upd, bool $value_vacio, array $ids_css = array(),
                                              string $regex = '', string $title = ''): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        $valida = $this->directivas->valida_data_label(name: $name,place_holder:  $place_holder);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos ', data: $valida);
        }

        $div = $this->div_input_text_required(cols: $cols, disabled: $disabled, ids_css: $ids_css,
            name: $name, place_holder: $place_holder, regex: $regex, row_upd: $row_upd, title: $title,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    private function integra_password_item(string $item, array $keys_selects, stdClass $passwords, stdClass $row_upd){
        $item = $this->item(item: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar item', data: $item);
        }

        $passwords = $this->passwords(item: $item,keys_selects:  $keys_selects,passwords:  $passwords,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }

        return $passwords;
    }

    /**
     * REG
     * Integra una propiedad CSS y su valor en una cadena de propiedades CSS.
     *
     * Esta función toma una propiedad CSS y su valor, valida que sean correctos y los agrega a una cadena
     * de propiedades CSS. La propiedad y el valor se concatenan a la cadena de propiedades CSS existente,
     * separando cada propiedad por punto y coma.
     *
     * **Pasos de procesamiento:**
     * 1. Se valida la propiedad y su valor utilizando la función `valida_propiedad`.
     * 2. Si la validación es exitosa, se agrega la propiedad y su valor a la cadena de propiedades CSS.
     * 3. Si ocurre un error durante la validación, se genera un mensaje de error.
     * 4. Si todo es válido, se retorna la cadena de propiedades CSS actualizada.
     *
     * **Parámetros:**
     *
     * @param string $propiedad El nombre de la propiedad CSS (por ejemplo, `'color'`, `'font-size'`, etc.). Este parámetro es obligatorio.
     * @param string $propiedades La cadena de propiedades CSS donde se agregará la nueva propiedad y su valor. Este parámetro es obligatorio.
     * @param string $valor El valor correspondiente a la propiedad CSS (por ejemplo, `'red'`, `'12px'`, etc.). Este parámetro es obligatorio.
     *
     * **Retorno:**
     * - Devuelve la cadena de propiedades CSS actualizada, con la nueva propiedad y valor agregados al final.
     * - Si ocurre un error durante la validación de la propiedad o el valor, devuelve un mensaje de error.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Integración exitosa de propiedad CSS**
     * ```php
     * $propiedad = "color";
     * $valor = "red";
     * $propiedades = "font-size: 12px; ";
     * $resultado = $this->integra_propiedad($propiedad, $propiedades, $valor);
     * // Retorna: "font-size: 12px; color: red; "
     * ```
     *
     * **Ejemplo 2: Error al validar propiedad CSS**
     * ```php
     * $propiedad = "";
     * $valor = "red";
     * $propiedades = "font-size: 12px; ";
     * $resultado = $this->integra_propiedad($propiedad, $propiedades, $valor);
     * // Retorna: "Error propiedad esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function integra_propiedad(string $propiedad, string $propiedades, string $valor): string|array
    {
        // Validar la propiedad y su valor
        $valida = $this->valida_propiedad(propiedad: $propiedad, valor: $valor);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar propiedad', data: $valida);
        }

        // Integrar la propiedad y su valor a la cadena de propiedades CSS
        $propiedades .= $propiedad . ': ' . $valor . '; ';
        return $propiedades;
    }


    /**
     * Integra los inputs de tipo selects
     * @param array $keys_selects Keys a integrar
     * @param mixed $modelo Modelo de selector
     * @param string $item Nombre del input
     * @param stdClass $selects Selects previos cargados
     * @return array|stdClass
     * @version 10.9.0
     */
    private function integra_select(array $keys_selects, mixed $modelo, string $item, stdClass $selects): array|stdClass
    {
        $valida = $this->valida_data_select(keys_selects: $keys_selects,modelo:  $modelo,item:  $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida item', data: $valida);
        }

        $params_select = $this->params_select(item: $item, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa params_select', data: $params_select);
        }

        $select = $this->select_aut2(modelo: $modelo,params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->$item = $select;
        return $selects;
    }

    /**
     * Genera in item para salida de front
     * @param string $item Campo o input
     * @return array|string
     */
    private function item(string $item): array|string
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }
        return $item;
    }

    /**
     * REG
     * Genera un enlace (URL) con parámetros de consulta (GET) dinámicos y adicionales para ser utilizado en la interfaz.
     *
     * Esta función genera una URL a partir de los parámetros proporcionados, agregando parámetros de consulta
     * como `seccion`, `accion`, `session_id` y `registro_id`. Si los parámetros de la URL contienen valores no vacíos,
     * estos se agregan a la cadena de parámetros GET. Además, si el parámetro `adm_menu_id` está presente en los parámetros GET,
     * se incluye en la URL, si no, se usa el valor por defecto `-1`.
     *
     * **Pasos de procesamiento:**
     * 1. Se valida si los parámetros `seccion`, `accion`, `session_id` no están vacíos y se agregan a la URL.
     * 2. Se agrega el parámetro `adm_menu_id`, si está disponible en los parámetros GET, o se asigna `-1` por defecto.
     * 3. Se genera la cadena de la URL utilizando todos los parámetros validados.
     * 4. Se agrega cualquier parámetro adicional proporcionado en `$params_get`.
     * 5. Se limpia la URL para evitar redundancias, como `?&` o `&&`.
     * 6. Si todo es válido, la URL generada es retornada. Si ocurre algún error, se devuelve un mensaje de error detallado.
     *
     * **Parámetros:**
     *
     * @param string $accion La acción que se realizará cuando se haga clic en el enlace. Este parámetro es obligatorio.
     *                       Representa la acción que se llevará a cabo en la interfaz de usuario, como "guardar", "editar", etc.
     *                       Ejemplo: `'guardar'`.
     * @param string $params_get Parámetros adicionales que se incluirán en la URL como parámetros GET. Este parámetro es
     *                           opcional y debe ser una cadena con parámetros de consulta adicionales. Ejemplo: `'&redirigir=true'`.
     * @param int $registro_id El ID del registro asociado a la acción. Este parámetro es obligatorio y se incluye en la URL
     *                         para identificar el registro al que se aplica la acción. Ejemplo: `123`.
     * @param string $seccion El nombre de la sección en la que se realiza la acción. Este parámetro es obligatorio y se incluye
     *                         en la URL como un parámetro GET. Ejemplo: `'usuarios'`.
     * @param string $session_id El ID de la sesión actual. Este parámetro es obligatorio y se usa para identificar la sesión
     *                           del usuario. Ejemplo: `'abc123'`.
     *
     * **Retorno:**
     * - Devuelve la URL generada con todos los parámetros GET incluidos.
     * - Si algún parámetro es inválido o falta, se devuelve un mensaje de error.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de un enlace con parámetros GET**
     * ```php
     * $accion = "guardar";
     * $params_get = "&redirigir=true";
     * $registro_id = 123;
     * $seccion = "usuarios";
     * $session_id = "abc123";
     *
     * $resultado = $this->link_a($accion, $params_get, $registro_id, $seccion, $session_id);
     * // Retorna: "index.php?seccion=usuarios&accion=guardar&registro_id=123&session_id=abc123&adm_menu_id=-1&redirigir=true"
     * ```
     *
     * **Ejemplo 2: Enlace con un parámetro adicional**
     * ```php
     * $accion = "editar";
     * $params_get = "&mostrar=true";
     * $registro_id = 456;
     * $seccion = "productos";
     * $session_id = "xyz789";
     *
     * $resultado = $this->link_a($accion, $params_get, $registro_id, $seccion, $session_id);
     * // Retorna: "index.php?seccion=productos&accion=editar&registro_id=456&session_id=xyz789&adm_menu_id=-1&mostrar=true"
     * ```
     *
     * **Ejemplo 3: Error por falta de parámetros requeridos**
     * ```php
     * $accion = "";
     * $params_get = "&redirigir=true";
     * $registro_id = 123;
     * $seccion = "";
     * $session_id = "abc123";
     *
     * $resultado = $this->link_a($accion, $params_get, $registro_id, $seccion, $session_id);
     * // Retorna un mensaje de error: "Error la $seccion esta vacia"
     * ```
     *
     * **@version 1.0.0**
     */
    private function link_a(
        string $accion, string $params_get, int $registro_id, string $seccion, string $session_id): string
    {
        // Inicializar las partes del enlace con valores predeterminados vacíos
        $query_params = [];

        // Agregar parámetros al arreglo si no están vacíos
        if (!empty($seccion)) {
            $query_params[] = "seccion=" . trim($seccion);
        }

        if (!empty($accion)) {
            $query_params[] = "accion=" . trim($accion);
        }

        if (!empty($session_id)) {
            $query_params[] = "session_id=" . trim($session_id);
        }

        // Agregar el ID del menú si está disponible en los parámetros GET
        $adm_menu_id = $_GET['adm_menu_id'] ?? -1;
        $query_params[] = "adm_menu_id=$adm_menu_id";

        // Crear el enlace base
        $link = "index.php?" . implode("&", $query_params) . "&registro_id=$registro_id";

        // Agregar los parámetros adicionales
        $link .= $params_get;

        // Limpiar el enlace para eliminar posibles redundancias
        $link = str_replace("?&", "?", $link);
        return str_replace("&&", "&", $link);
    }



    /**
     * Genera un menu lateral con titulo
     * @param string $etiqueta Etiqueta del menu
     * @return array|string
     * @version 0.93.32
     */
    public function menu_lateral_title(string $etiqueta): array|string
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la etiqueta esta vacia', data: $etiqueta);
        }
        $menu_lateral = $this->html_base->menu_lateral($etiqueta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar menu lateral texto', data: $menu_lateral);
        }
        return $menu_lateral;

    }

    /**
     * Inicializa la base para modifica frontend
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.102.32
     */
    final public function modifica(controler $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }

        $html_id = $this->directivas->input_id(cols:4,row_upd: $controler->row_upd,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_id);
        }
        $controler->inputs->id = $html_id;

        $cols = new stdClass();
        $cols->codigo = 4;
        $cols->codigo_bis = 4;
        $inputs_base = $this->inputs_base(cols:$cols,controler: $controler,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }

    /**
     * Obtiene el nombre del modelo
     * @param string $name_model nombre base del modelo
     * @param stdClass $params parametros precargados
     * @return string|array
     */
    private function name_model(string $name_model, stdClass $params): string|array
    {
        $name_model = trim($name_model);
        if(isset($params->name_model)){
            $name_model = $params->name_model;
        }
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        return $name_model;
    }

    /**
     * Obtiene el namespace de los parametros integrados
     * @param stdClass $params
     * @return string
     */
    private function namespace_model(stdClass $params): string
    {
        $namespace_model = '';
        if(isset($params->namespace_model)){
            $namespace_model = $params->namespace_model;
        }
        return $namespace_model;
    }

    /**
     * @param array|stdClass $campos_view Campos definidos desde modelo
     * @return array|stdClass
     */
    protected function obtener_inputs(array|stdClass $campos_view): array|stdClass
    {
        $selects = array();
        $inputs = array();
        $textareas = array();
        $files = array();
        $dates = array();
        $passwords = array();
        $telefonos = array();
        $emails = array();
        $fechas = array();

        foreach ($campos_view as $item => $campo){

            $es_campo_valido = false;
            if(is_object($campo)){
                $es_campo_valido = true;
            }
            if(is_array($campo)){
                $es_campo_valido = true;
            }
            if(!$es_campo_valido){
                return $this->error->error(mensaje: 'Error el campo debe ser un array o stdclass', data: $campo);
            }
            if (!isset($campo['type'])){
                return $this->error->error(mensaje: 'Error no existe key type', data: $campo);
            }
            if(!is_string($campo['type'])){
                return $this->error->error(mensaje: 'Error type debe ser un string', data: $campo);
            }

            $tipo_input = $this->obtener_tipo_input(campo: $campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener el tipo de input', data: $tipo_input);
            }

            switch ($tipo_input) {

                case 'selects':
                    $select = $this->obtener_select(campo: $campo);
                    if(errores::$error){
                        return $this->error->error(mensaje: 'Error al obtener select', data: $select);
                    }
                    $selects[$item] = $select;
                    break;
                case 'inputs':
                    $inputs[] = $item;
                    break;
                case 'textareas':
                    $textareas[] = $item;
                    break;
                case 'files':
                    $files[] = $item;
                    break;
                case 'passwords':
                    $passwords[] = $item;
                    break;
                case 'telefonos':
                    $telefonos[] = $item;
                    break;
                case 'emails':
                    $emails[] = $item;
                    break;
                case 'fechas':
                    $fechas[] = $item;
                    break;
                case 'dates':
                    $dates[] = $item;
                    break;

            }

        }
        return ['selects' => $selects,'inputs' => $inputs,'textareas'=>$textareas,'files' => $files,'dates' => $dates,
            'passwords'=>$passwords, 'telefonos'=>$telefonos,'emails'=>$emails,'fechas'=>$fechas];
    }

    /**
     * Obtiene un modelo basado en campo
     * @param array $campo Conjunto de modelos
     * @return array|modelo
     * @version 0.120.33
     */
    final protected function obtener_select(array $campo): array|modelo
    {
        if (!isset($campo['model'])){
            return $this->error->error(mensaje: 'Error no existe key model', data: $campo);
        }

        if (!is_object($campo['model'])) {
            return $this->error->error(mensaje: 'Error: El modelo brindado no esta definido', data: $campo);
        }

        return $campo['model'];
    }

    /**
     * Obtiene el tipo de input para templates
     * @param array|stdClass $campo
     * @return string|array
     */
    final protected function obtener_tipo_input(array|stdClass $campo): string|array
    {
        if(is_object($campo)){
            $campo = (array)$campo;
        }
        if (!isset($campo['type'])){
            return $this->error->error(mensaje: 'Error no existe key type', data: $campo);
        }
        if(!is_string($campo['type'])){
            return $this->error->error(mensaje: 'Error type debe ser un string', data: $campo);
        }
        return trim($campo['type']);
    }

    /**
     * REG
     * Genera un objeto con los parámetros `params_get`, `icon_html` y `etiqueta_html` para ser utilizados en un botón.
     *
     * Esta función recibe el nombre de un ícono, una etiqueta y otros parámetros que determinan si se debe mostrar el ícono
     * y la etiqueta en el botón generado. Además, procesa y valida los parámetros proporcionados, generando el HTML adecuado
     * para los iconos y etiquetas. Si alguno de los parámetros es inválido, se retorna un mensaje de error.
     *
     * **Pasos de procesamiento:**
     * 1. Se genera una cadena de parámetros GET a partir del arreglo `$params` utilizando la función `params_get`.
     * 2. Se genera el HTML del ícono utilizando la función `icon_html` si `$muestra_icono_btn` es `true`.
     * 3. Se genera el HTML de la etiqueta utilizando la función `etiqueta_html` si `$muestra_titulo_btn` es `true`.
     * 4. Se retorna un objeto con los parámetros generados.
     * 5. Si ocurre algún error, se retorna un mensaje de error detallado.
     *
     * **Parámetros:**
     * @param string $icon El nombre del ícono que se usará para el botón. Por ejemplo, "fa-user".
     *                     Este parámetro es obligatorio si se desea mostrar el ícono.
     * @param string $etiqueta El texto de la etiqueta que se mostrará en el botón. Por ejemplo, "Crear usuario".
     *                         Este parámetro es obligatorio si se desea mostrar la etiqueta.
     * @param bool $muestra_icono_btn Determina si el ícono debe mostrarse en el botón. Si es `true`, se genera el ícono.
     *                                Si es `false`, no se genera ningún ícono.
     * @param bool $muestra_titulo_btn Determina si la etiqueta debe mostrarse en el botón. Si es `true`, se muestra el texto de la etiqueta.
     *                                 Si es `false`, no se genera ninguna etiqueta.
     * @param array $params Un arreglo de parámetros adicionales que se incluirán en la URL del botón como parámetros GET.
     *
     * **Retorno:**
     * - Devuelve un objeto con tres propiedades: `params_get`, `icon_html` y `etiqueta_html`.
     *   - `params_get` es una cadena con los parámetros GET generados.
     *   - `icon_html` es el HTML generado para el ícono (o una cadena vacía si no se muestra).
     *   - `etiqueta_html` es el HTML generado para la etiqueta (o una cadena vacía si no se muestra).
     * - Si ocurre un error en cualquiera de los pasos, devuelve un arreglo con el mensaje de error correspondiente.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de parámetros para el botón**
     * ```php
     * $icon = "fa-user";
     * $etiqueta = "Crear usuario";
     * $muestra_icono_btn = true;
     * $muestra_titulo_btn = true;
     * $params = ['id' => '123', 'redirigir' => 'true'];
     *
     * $resultado = $this->params_btn($icon, $etiqueta, $muestra_icono_btn, $muestra_titulo_btn, $params);
     * // Retorna:
     * // {
     * //     "params_get": "&id=123&redirigir=true",
     * //     "icon_html": "<span class='fa-user'></span>",
     * //     "etiqueta_html": "Crear usuario"
     * // }
     * ```
     *
     * **Ejemplo 2: Error al generar parámetros GET debido a una clave vacía**
     * ```php
     * $icon = "fa-user";
     * $etiqueta = "Crear usuario";
     * $muestra_icono_btn = true;
     * $muestra_titulo_btn = true;
     * $params = ['', 'email' => 'juan@example.com'];
     *
     * $resultado = $this->params_btn($icon, $etiqueta, $muestra_icono_btn, $muestra_titulo_btn, $params);
     * // Retorna:
     * // {
     * //     "mensaje": "Error en key no puede venir vacio",
     * //     "data": ""
     * // }
     * ```
     *
     * **Ejemplo 3: Error al generar HTML del ícono debido a un ícono vacío**
     * ```php
     * $icon = "";
     * $etiqueta = "Crear usuario";
     * $muestra_icono_btn = true;
     * $muestra_titulo_btn = true;
     * $params = ['id' => '123'];
     *
     * $resultado = $this->params_btn($icon, $etiqueta, $muestra_icono_btn, $muestra_titulo_btn, $params);
     * // Retorna:
     * // {
     * //     "mensaje": "Error si muestra_icono_btn entonces icon no puede venir vacio",
     * //     "data": ""
     * // }
     * ```
     *
     * **Ejemplo 4: Error al generar HTML de la etiqueta debido a una etiqueta vacía**
     * ```php
     * $icon = "fa-user";
     * $etiqueta = "";
     * $muestra_icono_btn = true;
     * $muestra_titulo_btn = true;
     * $params = ['id' => '123'];
     *
     * $resultado = $this->params_btn($icon, $etiqueta, $muestra_icono_btn, $muestra_titulo_btn, $params);
     * // Retorna:
     * // {
     * //     "mensaje": "Error si muestra_titulo_btn entonces etiqueta no puede venir vacio",
     * //     "data": ""
     * // }
     * ```
     *
     * **Ejemplo 5: Parámetros válidos pero sin mostrar ícono ni etiqueta**
     * ```php
     * $icon = "";
     * $etiqueta = "";
     * $muestra_icono_btn = false;
     * $muestra_titulo_btn = false;
     * $params = ['id' => '123'];

     * $resultado = $this->params_btn($icon, $etiqueta, $muestra_icono_btn, $muestra_titulo_btn, $params);
     * // Retorna:
     * // {
     * //     "params_get": "&id=123",
     * //     "icon_html": "",
     * //     "etiqueta_html": ""
     * // }
     * ```
     *
     * **@version 1.0.0**
     */
    private function params_btn(string $icon, string $etiqueta, bool $muestra_icono_btn, bool $muestra_titulo_btn,
                                array $params){
        $params_get = $this->params_get(params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params_get', data: $params_get);
        }
        $icon_html = $this->icon_html(icon: $icon, muestra_icono_btn:  $muestra_icono_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar icon_html', data: $icon_html);
        }
        $etiqueta_html = $this->etiqueta_html(etiqueta: $etiqueta, muestra_titulo_btn:  $muestra_titulo_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar etiqueta_html', data: $etiqueta_html);
        }
        $data = new stdClass();
        $data->params_get = $params_get;
        $data->icon_html = $icon_html;
        $data->etiqueta_html = $etiqueta_html;
        return $data;
    }


    /**
     * REG
     * Genera una cadena de parámetros GET a partir de un arreglo de parámetros clave-valor.
     *
     * Esta función recibe un arreglo de parámetros clave-valor y genera una cadena de parámetros GET,
     * validando que las claves y los valores no estén vacíos. Si alguno de los parámetros es inválido,
     * la función devolverá un mensaje de error. Si todos los parámetros son válidos, la función retornará
     * una cadena de texto con los parámetros en formato `&key=value`.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorre el arreglo `$params`, donde cada clave y valor se valida:
     *    - La clave no puede estar vacía ni ser numérica.
     *    - El valor no puede estar vacío.
     * 2. Si alguna de las claves o valores es inválida, se genera un mensaje de error.
     * 3. Si todos los parámetros son válidos, se concatenan en una cadena de texto en formato `&key=value`.
     * 4. Se devuelve la cadena de parámetros GET generada.
     *
     * **Parámetros:**
     *
     * @param array $params Un arreglo asociativo que contiene las claves y valores para generar los parámetros GET.
     *
     * **Retorno:**
     * - Devuelve una cadena de texto con los parámetros GET generados si todo es válido.
     * - Si ocurre un error durante la validación, devuelve un arreglo con el mensaje de error correspondiente.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de parámetros GET**
     * ```php
     * $params = ['nombre' => 'Juan', 'email' => 'juan@example.com'];
     * $resultado = $this->params_get($params);
     * // Retorna: "&nombre=Juan&email=juan@example.com"
     * ```
     *
     * **Ejemplo 2: Error por clave vacía**
     * ```php
     * $params = ['', 'email' => 'juan@example.com'];
     * $resultado = $this->params_get($params);
     * // Retorna: 'Error en key no puede venir vacio'
     * ```
     *
     * **Ejemplo 3: Error por valor vacío**
     * ```php
     * $params = ['nombre' => 'Juan', 'email' => ''];
     * $resultado = $this->params_get($params);
     * // Retorna: 'Error en value no puede venir vacio'
     * ```
     *
     * **Ejemplo 4: Error por clave numérica**
     * ```php
     * $params = [1 => 'Juan', 'email' => 'juan@example.com'];
     * $resultado = $this->params_get($params);
     * // Retorna: 'Error en key debe ser un texto'
     * ```
     *
     * **@version 1.0.0**
     */
    private function params_get(array $params): string|array
    {
        // Inicialización de la cadena que contendrá los parámetros GET
        $params_get = '';

        // Recorrer el arreglo de parámetros
        foreach ($params as $key => $value) {
            // Validar que la clave no esté vacía
            $key = trim($key);
            if ($key === '') {
                return $this->error->error(mensaje: 'Error en key no puede venir vacio', data: $key);
            }

            // Validar que la clave no sea numérica
            if (is_numeric($key)) {
                return $this->error->error(mensaje: 'Error en key debe ser un texto', data: $key);
            }

            // Validar que el valor no esté vacío
            $value = trim($value);
            if ($value === '') {
                return $this->error->error(mensaje: 'Error en value no puede venir vacio', data: $value);
            }

            // Concatenar el parámetro en la cadena de parámetros GET
            $params_get .= "&$key=$value";
        }

        // Devolver la cadena de parámetros GET
        return $params_get;
    }


    /**
     * Integra los parametros de un select
     * @param string $item Nombre del input
     * @param array $keys_selects Parametros de input select
     * @return array|stdClass
     * @version 10.8.0
     */
    private function params_select(string $item, array $keys_selects): array|stdClass
    {
        $item = trim($item);
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = new stdClass();

        if (array_key_exists($item, $keys_selects) ){
            $params_select = $keys_selects[$item];
        }

        $params_select = (new params())->params_select_col_6(params: $params_select,label: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }
        return $params_select;
    }

    /**
     * @param string $name_model
     * @param stdClass $params
     * @return array|stdClass
     */
    private function params_select_info(string $name_model, stdClass $params): array|stdClass
    {
        $tabla = $name_model;


        $name_model = $this->name_model(name_model: $name_model, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar name_model', data: $name_model);
        }
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        if(is_numeric($name_model)){
            return $this->error->error(mensaje: 'Error $name_model debe ser el nombre de un modelo valido',
                data: $name_model);
        }


        $namespace_model = $this->namespace_model(params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace_model', data: $namespace_model);
        }

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->name_model = $name_model;
        $data->namespace_model = $namespace_model;
        return $data;
    }

    /**
     * Obtiene el item de un input de tipo pass
     * @param array $campos_view Campos definidos en el modelo
     * @return array
     * @version 4.10.1
     */
    private function pass_item_init(array $campos_view): array
    {
        if(!isset($campos_view['passwords'])){
            $campos_view['passwords'] = array();
        }
        if(!is_array($campos_view['passwords'])){
            return $this->error->error(mensaje: 'Error campos_view[passwords] debe se run array', data: $campos_view);
        }
        return $campos_view;

    }

    /**
     * Integra los inputs de tipo password
     * @param string $item Campo
     * @param array $keys_selects Params de inputs
     * @param stdClass $passwords inputs previamente cargados
     * @param stdClass $row_upd Registro en proceso
     * @return array|stdClass
     * @version 3.9.1
     */
    private function passwords(string $item, array $keys_selects, stdClass $passwords, stdClass $row_upd): array|stdClass
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }
        $date = (new template())->passwords_template(directivas: $this->directivas, params_select: $params_select,
            row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $date);
        }
        $passwords->$item = $date;
        return $passwords;
    }

    /**
     * Integra para front los passwords para alta
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros para front
     * @return array|stdClass
     * @version 4.8.1
     */
    final protected function passwords_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $passwords = $this->passwords_campos(campos_view: $campos_view, keys_selects: $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }
        return $passwords;
    }

    /**
     * Genera los passwords inputs
     * @param array $campos_view Campos de modelo
     * @param array $keys_selects parametros de los inputs
     * @param stdClass $row_upd registro en proceso
     * @return array|stdClass
     * 3.12.1
     */
    private function passwords_campos(array $campos_view, array $keys_selects, stdClass $row_upd): array|stdClass
    {
        $campos_view = $this->pass_item_init(campos_view: $campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $campos_view);
        }
        $passwords = $this->passwords_campos_view(campos_view: $campos_view,keys_selects:  $keys_selects,
            row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
        }

        return $passwords;
    }

    private function passwords_campos_view(array $campos_view, array $keys_selects, stdClass $row_upd){
        $passwords = new stdClass();
        foreach ($campos_view['passwords'] as $item){
            $passwords = $this->integra_password_item(item: $item,keys_selects:  $keys_selects,passwords:  $passwords,row_upd:  $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar passwords', data: $passwords);
            }
        }
        return $passwords;
    }

    /**
     * REG
     * Genera una cadena de propiedades CSS a partir de un arreglo de propiedades y valores.
     *
     * Esta función toma un arreglo asociativo donde las claves son propiedades CSS y los valores son los valores correspondientes.
     * La función valida cada propiedad y valor utilizando `valida_propiedad` y luego integra cada propiedad y su valor en una cadena
     * de propiedades CSS. Si alguna propiedad o valor no es válido, se genera un mensaje de error. Si todas las propiedades son válidas,
     * se retorna una cadena con todas las propiedades CSS generadas.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorre el arreglo `$styles`, donde cada clave y valor se valida:
     *    - Se valida que la propiedad y su valor no estén vacíos y que la propiedad no sea un número.
     * 2. Si la validación es exitosa, se agrega la propiedad y su valor a la cadena de propiedades CSS.
     * 3. Si ocurre un error durante la validación o la integración, se retorna un mensaje de error detallado.
     * 4. Si todo es válido, se retorna la cadena de propiedades CSS generada.
     *
     * **Parámetros:**
     *
     * @param array $styles Un arreglo asociativo donde las claves son propiedades CSS (por ejemplo, `'color'`, `'font-size'`)
     *                      y los valores son los valores correspondientes (por ejemplo, `'red'`, `'12px'`).
     *
     * **Retorno:**
     * - Devuelve una cadena con todas las propiedades CSS generadas, separadas por punto y coma.
     * - Si ocurre un error durante la validación o la integración de las propiedades, se devuelve un mensaje de error.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa de propiedades CSS**
     * ```php
     * $styles = ['color' => 'red', 'font-size' => '12px'];
     * $resultado = $this->propiedades_css($styles);
     * // Retorna: "color: red; font-size: 12px; "
     * ```
     *
     * **Ejemplo 2: Error al validar propiedad CSS**
     * ```php
     * $styles = ['color' => '', 'font-size' => '12px'];
     * $resultado = $this->propiedades_css($styles);
     * // Retorna: "Error valor esta vacio"
     * ```
     *
     * **Ejemplo 3: Error al integrar propiedad CSS**
     * ```php
     * $styles = ['color' => 'red', 'font-size' => ''];
     * $resultado = $this->propiedades_css($styles);
     * // Retorna: "Error valor esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function propiedades_css(array $styles): array|string
    {
        // Inicializar la cadena de propiedades CSS
        $propiedades = '';

        // Recorrer el arreglo de estilos
        foreach ($styles as $propiedad => $valor) {
            // Validar la propiedad y el valor
            $valida = $this->valida_propiedad(propiedad: $propiedad, valor: $valor);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar propiedad', data: $valida);
            }

            // Integrar la propiedad y el valor en la cadena de propiedades CSS
            $propiedades = $this->integra_propiedad(propiedad: $propiedad, propiedades: $propiedades, valor: $valor);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar propiedades', data: $propiedades);
            }
        }

        // Retornar la cadena de propiedades CSS generada
        return $propiedades;
    }

    /**
     * Retornos hidden
     * @param int $registro_id Registro id a retornar
     * @param string $tabla Tabla a retornar
     * @return array|stdClass
     */
    final public function retornos(int $registro_id, string $tabla): array|stdClass
    {

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla, es_final: true);
        }
        $registro_id = trim($registro_id);
        if($registro_id === ''){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data:  $registro_id,
                es_final: true);
        }

        $hidden_id_retorno = $this->hidden(name: 'id_retorno', value: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener hidden_id_retorno',data:  $hidden_id_retorno);
        }
        $hidden_seccion_retorno = $this->hidden(name: 'seccion_retorno', value: $tabla);
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error al obtener hidden_seccion_retorno',data:  $hidden_seccion_retorno);
        }
        $data = new stdClass();
        $data->hidden_id_retorno = $hidden_id_retorno;
        $data->hidden_seccion_retorno = $hidden_seccion_retorno;
        return $data;
    }

    /**
     * REG
     * Asigna un valor al atributo `role` de un botón.
     *
     * Esta función recibe un valor para el atributo `role` de un botón. Si el valor proporcionado está vacío,
     * se asigna el valor por defecto `'button'` para asegurar que el elemento tenga un valor válido para el atributo `role`.
     * Si el valor de `$role` no está vacío, simplemente se retorna ese valor.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorta el valor de `$role` para eliminar cualquier espacio extra al principio o al final.
     * 2. Si el valor de `$role` está vacío, se asigna el valor por defecto `'button'`.
     * 3. Se retorna el valor del atributo `role`, ya sea el proporcionado o el valor por defecto.
     *
     * **Parámetros:**
     *
     * @param string $role El valor del atributo `role` para el botón. Este parámetro es opcional y, si está vacío,
     *                     se le asignará el valor por defecto `'button'`.
     *
     * **Retorno:**
     * - Devuelve el valor del atributo `role`, que será el valor proporcionado o `'button'` si el valor estaba vacío.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Asignar un role válido**
     * ```php
     * $role = "submit";
     * $resultado = $this->role_button($role);
     * // Retorna: "submit"
     * ```
     *
     * **Ejemplo 2: Asignar el valor por defecto si el role está vacío**
     * ```php
     * $role = "";
     * $resultado = $this->role_button($role);
     * // Retorna: "button"
     * ```
     *
     * **@version 1.0.0**
     */
    private function role_button(string $role): string
    {
        $role = trim($role);
        if($role === ''){;
            $role = 'button';
        }
        return $role;
    }



    /**
     * Genera un select automatico conforme a params
     * @param PDO $link Conexion a la BD
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros a ejecutar para select
     * @param stdClass $selects Selects precargados
     * @param string $namespace_model Nombre del namespace
     * @param string $tabla Tabla de datos
     * @return array|stdClass
     * @version 8.93.1
     */
    private function select_aut(
        PDO $link, string $name_model, stdClass $params, stdClass $selects,string $namespace_model = '' ,
        string $tabla = ''): array|stdClass
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        $params_select = (new params())->params_select(name_model: $name_model, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar params', data: $params_select);
        }
        if($tabla === ''){
            $tabla = $name_model;
        }

        $name_select_id = $tabla.'_id';
        $modelo = (new modelo_base($link))->genera_modelo(modelo: $name_model,namespace_model: $namespace_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $select  = $this->select_catalogo(cols: $params_select->cols, con_registros: $params_select->con_registros,
            id_selected: $params_select->id_selected, modelo: $modelo, columns_ds: $params_select->columns_ds,
            disabled: $params_select->disabled, filtro: $params_select->filtro, label: $params_select->label,
            not_in: $params_select->not_in, required: $params_select->required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }

        $selects->$name_select_id = $select;

        return $selects;
    }

    /**
     * Genera un select
     * @param modelo $modelo Modelo del select
     * @param stdClass $params_select Parametros visuales
     * @return array|stdClass|string
     * @example $params_select->extra_params_keys[] = 'tabla_id'; integra un extra param al option de un select
     * @version 10.3.0
     */
    private function select_aut2(modelo $modelo, stdClass $params_select): array|stdClass|string
    {
        $keys = array('cols','con_registros','id_selected','disabled','extra_params_keys','filtro','label','not_in',
            'required','registros', 'in');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('key_descripcion_select');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $params_select, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols','id_selected');
        $valida = $this->validacion->valida_numerics(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('con_registros','disabled','required');
        $valida = $this->validacion->valida_bools(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('extra_params_keys','filtro','not_in', 'in', 'columns_ds','registros');
        $valida = $this->validacion->valida_arrays(keys: $keys, row: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $modelo_preferido = false;

        if(is_object($params_select->modelo_preferido)){
            $modelo_preferido = $params_select->modelo_preferido;
        }

        $select  = $this->select_catalogo(cols: $params_select->cols, con_registros: $params_select->con_registros,
            id_selected: $params_select->id_selected, modelo: $modelo, modelo_preferido: $modelo_preferido,
            columns_ds: $params_select->columns_ds, disabled: $params_select->disabled,
            extra_params_keys: $params_select->extra_params_keys, filtro: $params_select->filtro,
            key_descripcion_select: $params_select->key_descripcion_select, label: $params_select->label,
            not_in: $params_select->not_in, in: $params_select->in, registros: $params_select->registros,
            required: $params_select->required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    /**
     * Genera el input de tipo select
     * @param int $cols Numero de columnas boostrap
     * @param bool $con_registros Si con registros , obtiene todos los registros activos del modelo en ejecucion
     *  para la asignacion de options, Si no, deja el select en blanco o vacio
     * @param int|string|float|null $id_selected Identificador de un registro y cargado utilizado para modifica, aplica selected
     * @param modelo $modelo Modelo de datos ejecucion
     * @param bool $aplica_default
     * @param modelo|bool $modelo_preferido
     * @param array $class_css Integra elementos css como class en select
     * @param array $columns_ds
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param array $extra_params_keys Extraparams datos a obtener para integrar en data-extra
     * @param array $filtro Filtro para obtencion de datos
     * @param string $id_css Si esta vacio integra el id como name
     * @param string $key_descripcion
     * @param string $key_descripcion_select Key para mostrar en options
     * @param string $key_id Key para integrar el value
     * @param string $key_value_custom
     * @param string $label Etiqueta a mostrar en select
     * @param string $name Nombre del input
     * @param array $not_in Omite los elementos en obtencion de datos
     * @param array $in
     * @param array $registros
     * @param bool $required si required agrega el atributo required a input
     * @return array|string Un string con options en forma de html
     */
    final public function select_catalogo(int $cols, bool $con_registros, int|null|string|float $id_selected,
                                          modelo $modelo, bool $aplica_default = true,
                                          modelo|bool $modelo_preferido = false, array $class_css = array(),
                                          array $columns_ds = array(), bool $disabled = false,
                                          array $extra_params_keys = array(), array $filtro=array(),
                                          string $id_css = '', string $key_descripcion = '',
                                          string $key_descripcion_select = '', string $key_id = '',
                                          string $key_value_custom = '', string $label = '', string $name = '',
                                          array $not_in = array(), array $in = array(), array $registros = array(),
                                          bool $required = false): array|string
    {

        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(is_object($modelo_preferido)){

            $id_selected = $modelo_preferido->id_preferido_detalle($modelo->tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener id preferido', data: $id_selected);
            }

        }

        $init = (new select())->init_data_select(con_registros: $con_registros, modelo: $modelo,
            aplica_default: $aplica_default, columns_ds: $columns_ds, extra_params_keys: $extra_params_keys,
            filtro: $filtro, key_descripcion: $key_descripcion, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, key_value_custom: $key_value_custom, label: $label, name: $name, not_in: $not_in,
            in: $in, registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $init);
        }

        $select = $this->html_base->select(cols: $cols, id_selected: $id_selected, label: $init->label,
            name: $init->name, values: $init->values, class_css: $class_css, columns_ds: $columns_ds,
            disabled: $disabled, extra_params_key: $extra_params_keys, id_css: $id_css,
            key_value_custom: $key_value_custom, required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


    private function selects(PDO $link, string $name_model, stdClass $params, stdClass $selects){
        $data_params = $this->params_select_info(name_model: $name_model,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar parametros de info select', data: $data_params);
        }


        $selects  = $this->select_aut(link: $link,name_model:  $data_params->name_model,params:  $params, selects: $selects,
            namespace_model: $data_params->namespace_model, tabla: $data_params->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $selects);
        }

        return $selects;
    }

    /**
     * Genera selects en volumen con parametros
     * @param array $keys_selects conjunto de selects
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.100.32
     */
    protected function selects_alta(array $keys_selects, PDO $link): array|stdClass
    {

        $selects = new stdClass();

        foreach ($keys_selects as $name_model=>$params){

            if(!is_object($params)){
                return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $params);
            }

            $selects  = $this->selects(link: $link, name_model: $name_model,params:  $params,selects:  $selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $selects);
            }

        }

        return $selects;

    }

    /**
     * Genera los selects para una view
     * @param modelo $modelo Modelo en ejecucion
     * @param array $keys_selects Parametros de selects
     * @return array|stdClass
     */
    final protected function selects_alta2(modelo $modelo,array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $selects = $this->selects_integra(campos_view: $campos_view, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar selects', data: $selects);
        }

        return $selects;
    }

    /**
     * Integra los selects para views
     * @param array $campos_view Campos precargados
     * @param array $keys_selects Selectores params
     * @return array|stdClass
     * @example keys_selects['name_input']->extra_params_keys[] = 'tabla_id';
     * integra un extra param al option de un select
     * @version 10.10.0
     */
    private function selects_integra(array $campos_view, array $keys_selects): array|stdClass
    {
        $selects = new stdClass();

        if(!isset($campos_view['selects'])){
            $campos_view['selects'] = array();
        }

        foreach ($campos_view['selects'] as $item => $modelo){
            $selects = $this->integra_select(keys_selects: $keys_selects,modelo:  $modelo,item:  $item,
                selects:  $selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
            }
        }
        return $selects;
    }

    /**
     * REG
     * Valida y determina el estilo CSS de un botón de acción permitida basado en el estado de la acción y los parámetros proporcionados.
     *
     * Esta función realiza la validación de los datos de la acción permitida, obteniendo el estilo CSS por defecto
     * o ajustado en función de si la acción está activa. La función verifica si los datos necesarios están presentes y
     * si son válidos, y ajusta el estilo del botón según el estado de la acción y las condiciones del registro.
     *
     * **Pasos principales:**
     * 1. Se verifica que el parámetro `$row` no esté vacío. Si está vacío, se lanza un error.
     * 2. Se valida que la acción permitida (`$accion_permitida`) tenga los datos correctos mediante la función
     *    `valida_boton_data_accion`.
     * 3. Si la acción está activa (`adm_accion_es_status === 'activo'`), se ajusta el estilo con base en el estado y los datos
     *    del registro utilizando la función `style_btn_status`.
     * 4. Si todo es correcto, se retorna el estilo CSS final del botón.
     *
     * **Parámetros:**
     * @param array $accion_permitida Un arreglo asociativo que contiene los datos de la acción permitida. Debe incluir
     *                                al menos los siguientes campos:
     *  - `adm_accion_css`: El estilo CSS de la acción, un valor que se usará como base.
     *  - `adm_accion_es_status`: El estado de la acción (por ejemplo, 'activo' o 'inactivo').
     *  - `adm_accion_descripcion`: Descripción de la acción (ej. 'alta', 'modificar', etc.).
     *  - `adm_seccion_descripcion`: Descripción de la sección a la que pertenece la acción (ej. 'usuarios', 'productos', etc.).
     *
     * @param array $row Un arreglo asociativo con los datos del registro que se utilizarán para aplicar el estilo.
     *                   Este parámetro es necesario para poder obtener el estilo ajustado si la acción está activa.
     *
     * **Valor de retorno:**
     * - Retorna un arreglo si ocurre un error durante la validación o la obtención del estilo.
     * - Retorna una cadena de texto (`string`) que representa el estilo CSS aplicado al botón si todo es válido.
     *
     * **Excepciones:**
     * - Si el parámetro `$row` está vacío, se lanza un error.
     * - Si los datos de la acción permitida no son válidos, se lanza un error.
     * - Si no se puede obtener el estilo correspondiente, se lanza un error.
     *
     * **Ejemplos de uso:**
     *
     * **Ejemplo 1: Estilo por defecto**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'info',
     *     'adm_accion_es_status' => 'inactivo',
     *     'adm_accion_descripcion' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios'
     * ];
     * $row = ['campo1' => 'valor1', 'campo2' => 'valor2'];
     * $style = $this->style_btn($accion_permitida, $row);
     * echo $style;  // Imprimirá 'info', ya que la acción no está activa.
     * ```
     *
     * **Ejemplo 2: Estilo ajustado cuando la acción está activa**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'success',
     *     'adm_accion_es_status' => 'activo',
     *     'adm_accion_descripcion' => 'Editar',
     *     'adm_seccion_descripcion' => 'Usuarios'
     * ];
     * $row = ['campo1' => 'valor1', 'campo2' => 'valor2'];
     * $style = $this->style_btn($accion_permitida, $row);
     * echo $style;  // Imprimirá el estilo ajustado si la acción está activa.
     * ```
     *
     * **Ejemplo 3: Error cuando $row está vacío**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'danger',
     *     'adm_accion_es_status' => 'activo',
     *     'adm_accion_descripcion' => 'Eliminar',
     *     'adm_seccion_descripcion' => 'Usuarios'
     * ];
     * $row = [];
     * $style = $this->style_btn($accion_permitida, $row);
     * // Retornará un error, ya que el parámetro $row está vacío.
     * ```
     *
     * **Ejemplo 4: Error al validar la acción permitida**
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'danger',
     *     'adm_accion_es_status' => 'activo',
     *     'adm_accion_descripcion' => 'Eliminar',
     *     'adm_seccion_descripcion' => 'Usuarios'
     * ];
     * $row = ['campo1' => 'valor1'];
     * // Suponiendo que la validación de los datos falle
     * $style = $this->style_btn($accion_permitida, $row);
     * // Retornará un error si la validación falla.
     * ```
     *
     * @version 1.0.0
     */
    final public function style_btn(array $accion_permitida, array $row): array|string
    {
        if (count($row) === 0) {
            return $this->error->error(mensaje: 'Error row esta vacio', data: $row);
        }

        // Valida los datos de la acción permitida
        $valida = $this->valida_boton_data_accion(accion_permitida: $accion_permitida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar accion_permitida', data: $valida);
        }

        $style = $accion_permitida['adm_accion_css'];
        $es_status = $accion_permitida['adm_accion_es_status'];
        $accion = $accion_permitida['adm_accion_descripcion'];
        $seccion = $accion_permitida['adm_seccion_descripcion'];
        $key_es_status = $seccion . '_' . $accion;

        // Si la acción está activa, ajusta el estilo
        if ($es_status === 'activo') {
            $style = $this->style_btn_status(key_es_status: $key_es_status, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener style', data: $style);
            }
        }

        return $style;
    }


    /**
     * REG
     * Ajusta el estilo de un botón basado en el estado de la acción (activo o inactivo).
     *
     * Esta función valida el estado de una acción, determinado por la clave `$key_es_status`, en el arreglo `$row`.
     * Si el estado es 'activo', la función devuelve un estilo CSS de 'success'. Si el estado es 'inactivo',
     * devuelve un estilo de 'danger'.
     *
     * **Pasos de validación y operación:**
     * 1. Verifica que la clave de estado no esté vacía.
     * 2. Verifica que la fila (`$row`) no esté vacía.
     * 3. Valida que el estado de la acción en `$row` sea válido ('activo' o 'inactivo').
     * 4. Ajusta el estilo según el estado de la acción:
     *    - Si el estado es 'activo', el estilo será 'success'.
     *    - Si el estado es 'inactivo', el estilo será 'danger'.
     *
     * **Notas:**
     * - Si la clave o la fila están vacías, o si el estado es inválido, se lanza un error.
     * - El estilo devuelto se utiliza para aplicar diferentes estilos a los botones según el estado de la acción.
     *
     * @param string $key_es_status Clave que indica el estado de la acción en el arreglo `$row`.
     *                               Este valor debe estar presente en `$row` y contener el estado de la acción ('activo' o 'inactivo').
     *
     * @param array $row Datos de la fila que contiene el estado de la acción. Debe incluir la clave `$key_es_status` con su valor correspondiente.
     *
     * @return array|string Devuelve:
     *  - 'success' si el estado de la acción es 'activo'.
     *  - 'danger' si el estado de la acción es 'inactivo'.
     *  - Un arreglo con el mensaje de error si alguna validación falla.
     *
     * @throws errores Si alguna validación falla, se genera un error que se captura y se devuelve como un mensaje.
     *
     * **Ejemplo 1: Estilo de botón para estado 'activo'**
     * ```php
     * $key_es_status = 'activo';
     * $row = [
     *     'activo' => 'activo'
     * ];
     * $resultado = $this->style_btn_status($key_es_status, $row);
     * // Retorna 'success', ya que el estado es 'activo'.
     * ```
     *
     * **Ejemplo 2: Estilo de botón para estado 'inactivo'**
     * ```php
     * $key_es_status = 'activo';
     * $row = [
     *     'activo' => 'inactivo'
     * ];
     * $resultado = $this->style_btn_status($key_es_status, $row);
     * // Retorna 'danger', ya que el estado es 'inactivo'.
     * ```
     *
     * **Ejemplo 3: Error por fila vacía**
     * ```php
     * $key_es_status = 'activo';
     * $row = [];
     * $resultado = $this->style_btn_status($key_es_status, $row);
     * // Retorna un error con el mensaje 'Error row esta vacio'.
     * ```
     *
     * @version 1.0.0
     */
    private function style_btn_status(string $key_es_status, array $row): array|string
    {
        // Verifica que la clave no esté vacía
        $key_es_status = trim($key_es_status);
        if($key_es_status === ''){
            return $this->error->error(mensaje: 'Error key_es_status esta vacio', data: $key_es_status);
        }

        // Verifica que la fila no esté vacía
        if(count($row) === 0){
            return $this->error->error(mensaje: 'Error row esta vacio', data: $row);
        }

        // Valida el estado de la acción en la fila
        $keys = array($key_es_status);
        $valida = $this->validacion->valida_statuses(keys: $keys, registro: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        // Asigna el estilo según el estado de la acción
        $style = 'danger'; // Valor predeterminado para 'inactivo'
        if($row[$key_es_status] === 'activo'){
            $style = 'success'; // Si el estado es 'activo', el estilo es 'success'
        }

        // Retorna el estilo correspondiente
        return $style;
    }


    /**
     * REG
     * Genera un atributo `style` en formato de cadena a partir de un conjunto de propiedades CSS.
     *
     * Esta función toma una cadena de propiedades CSS y las encapsula en un atributo `style` que puede ser utilizado
     * en elementos HTML. Si las propiedades proporcionadas no están vacías, se genera una cadena con el formato
     * adecuado para un atributo `style`. Si las propiedades están vacías, se retorna una cadena vacía.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorta cualquier espacio adicional al principio y al final de la cadena de propiedades CSS.
     * 2. Si las propiedades no están vacías, se genera una cadena con el formato `style='propiedad1: valor1; propiedad2: valor2;'`.
     * 3. Si las propiedades están vacías, se retorna una cadena vacía.
     * 4. Si todo es válido, se retorna el atributo `style` generado.
     *
     * **Parámetros:**
     *
     * @param string $propiedades La cadena que contiene las propiedades CSS. Este parámetro es obligatorio.
     *                            Debe ser una cadena con las propiedades en formato `propiedad1: valor1; propiedad2: valor2;`.
     *
     * **Retorno:**
     * - Devuelve una cadena con el atributo `style` en formato HTML si las propiedades no están vacías.
     * - Si las propiedades están vacías, retorna una cadena vacía.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Generación exitosa del atributo `style`**
     * ```php
     * $propiedades = "color: red; font-size: 12px;";
     * $resultado = $this->style_custom($propiedades);
     * // Retorna: "style='color: red; font-size: 12px;'"
     * ```
     *
     * **Ejemplo 2: No generar atributo `style` si las propiedades están vacías**
     * ```php
     * $propiedades = "";
     * $resultado = $this->style_custom($propiedades);
     * // Retorna: ""
     * ```
     *
     * **@version 1.0.0**
     */
    private function style_custom(string $propiedades): string
    {
        // Recortar cualquier espacio adicional al principio y al final
        $propiedades = trim($propiedades);

        // Si las propiedades no están vacías, generar el atributo style
        $style_custom = '';
        if ($propiedades !== '') {
            $style_custom = "style='$propiedades'";
        }

        // Retornar el atributo style generado o una cadena vacía
        return $style_custom;
    }


    /** Genera el template de telefonos para frontend
     * @param modelo $modelo Modelo en ejecucion
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Parametros de estilos
     * @return array|stdClass
     * @version 4.44.2
     * @final rev
     *
     */
    protected function telefonos_alta(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs(campos_view: $modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $telefonos = new stdClass();

        foreach ($campos_view['telefonos'] as $item){

            $item = trim($item);
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
            }


            $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
            }
            $date = (new template())->telefonos_template(directivas: $this->directivas,
                params_select: $params_select,row_upd: $row_upd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $date);
            }
            $telefonos->$item = $date;
        }

        return $telefonos;
    }

    /**
     * @param string $item Key de input
     * @param array $keys_selects Parametros
     * @param stdClass $row_upd Registro en proceso
     * @param stdClass $texts Inputs
     * @return array|stdClass
     * @version 0.291.39
     */
    private function text_item(string $item, array $keys_selects, stdClass $row_upd, stdClass $texts): array|stdClass
    {
        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }

        $params_select = (new params())->params_select_init(item: $item, keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar params', data: $params_select);
        }

        $keys = array('name','place_holder');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $input = $this->file_template(params_select: $params_select,row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $input);
        }
        $texts->$item = $input;
        return $texts;
    }

    /**
     * Funcion base altas txt
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio si vacio deja in input vacio
     * @param stdClass $params parametros a integrar
     * @return array|stdClass
     * @version 0.119.33
     *
     */
    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        return new stdClass();
    }

    /**
     * Integra los inputs de tipo text
     * @param modelo $modelo Modelo en ejecucion para uso campos view
     * @param stdClass $row_upd Registro en proceso
     * @param array $keys_selects Params de inputs
     * @return array|stdClass
     */
    final protected function texts_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $texts = (new texts())->texts_integra(campos_view: $campos_view, directivas: $this->directivas,
            keys_selects:  $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $texts);
        }

        return $texts;
    }

    protected function textareas_alta2(modelo $modelo, stdClass $row_upd, array $keys_selects = array()): array|stdClass
    {
        $campos_view = $this->obtener_inputs($modelo->campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos de la vista del modelo', data: $campos_view);
        }

        $texts = (new texts())->textareas_integra(campos_view: $campos_view, directivas: $this->directivas,
            keys_selects:  $keys_selects,row_upd:  $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $texts);
        }

        return $texts;
    }

    /**
     * REG
     * Valida los datos de una acción permitida en el sistema.
     *
     * Esta función se encarga de verificar que todos los parámetros necesarios para una acción permitida estén presentes
     * y sean válidos. Asegura que:
     * - El campo de estilo CSS (`adm_accion_css`) sea un valor válido según una lista de estilos permitidos.
     * - El estado de la acción (`adm_accion_es_status`) sea uno de los valores válidos: 'activo' o 'inactivo'.
     * - Los campos obligatorios de la acción estén presentes y no vacíos.
     *
     * Si alguna de las validaciones falla, se devuelve un arreglo con el mensaje de error correspondiente.
     * Si todas las validaciones pasan, se devuelve `true`.
     *
     * **Pasos de validación:**
     * 1. Valida que los campos `adm_accion_css`, `adm_accion_es_status`, `adm_accion_descripcion`,
     *    y `adm_seccion_descripcion` existan en el registro de la acción permitida.
     * 2. Valida que el valor de `adm_accion_css` sea un estilo CSS válido.
     * 3. Valida que el campo `adm_accion_es_status` contenga un valor válido, que sea 'activo' o 'inactivo'.
     *
     * **Notas:**
     * - Si alguna validación falla, se lanza un error con un mensaje descriptivo.
     * - Si todas las validaciones son correctas, se devuelve `true`.
     *
     * @param array $accion_permitida Registro de la acción permitida a validar. Debe contener los siguientes campos:
     * - `adm_accion_css`: El estilo CSS asociado a la acción (debe ser un estilo válido).
     * - `adm_accion_es_status`: El estado de la acción, que debe ser 'activo' o 'inactivo'.
     * - `adm_accion_descripcion`: Descripción de la acción (campo requerido).
     * - `adm_seccion_descripcion`: Descripción de la sección a la que pertenece la acción (campo requerido).
     *
     * @return bool|array Devuelve:
     *  - `true` si todas las validaciones pasan correctamente.
     *  - Un arreglo con información del error si alguna validación falla.
     *
     * @throws errores Si alguna validación falla, se genera un error que se captura y devuelve como un mensaje.
     *
     * @example Ejemplo 1: Validar una acción permitida válida
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'info',
     *     'adm_accion_es_status' => 'activo',
     *     'adm_accion_descripcion' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios',
     * ];
     * $resultado = $this->valida_boton_data_accion($accion_permitida);
     * // Retorna true si todos los campos son válidos.
     * ```
     *
     * @example Ejemplo 2: Validar acción permitida con estilo CSS inválido
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'invalid_style', // Estilo no válido
     *     'adm_accion_es_status' => 'activo',
     *     'adm_accion_descripcion' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios',
     * ];
     * $resultado = $this->valida_boton_data_accion($accion_permitida);
     * // Retorna un arreglo con el mensaje de error: 'Error al obtener style'.
     * ```
     *
     * @example Ejemplo 3: Validar acción permitida con estado no válido
     * ```php
     * $accion_permitida = [
     *     'adm_accion_css' => 'info',
     *     'adm_accion_es_status' => 'pendiente', // Estado no válido
     *     'adm_accion_descripcion' => 'Crear',
     *     'adm_seccion_descripcion' => 'Usuarios',
     * ];
     * $resultado = $this->valida_boton_data_accion($accion_permitida);
     * // Retorna un arreglo con el mensaje de error: 'Error al validar $accion_permitida'.
     * ```
     *
     * @version 1.0.0
     */
    final public function valida_boton_data_accion(array $accion_permitida): bool|array
    {
        // Validación de existencia de claves
        $keys = array('adm_accion_css','adm_accion_es_status','adm_accion_descripcion','adm_seccion_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $accion_permitida',data:  $valida);
        }

        // Validación del estilo CSS
        $valida = $this->validacion->valida_estilo_css(style: $accion_permitida['adm_accion_css']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $valida);
        }

        // Validación del estado de la acción
        $keys = array('adm_accion_es_status');
        $valida = $this->validacion->valida_statuses(keys:$keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $accion_permitida',data:  $valida);
        }

        // Si todas las validaciones pasan correctamente, retorna true
        return true;
    }


    /**
     * Valida los datos de un select
     * @param array $keys_selects Keys a verificar
     * @param mixed $modelo Modelo a verificar
     * @param string $item Item o nombre del campo
     * @return array|true
     * @version 10.7.0
     */
    private function valida_data_select(array $keys_selects, mixed $modelo, string $item): bool|array
    {
        $valida = $this->valida_item(item: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida item', data: $valida);
        }

        if (array_key_exists($item, $keys_selects) && !is_object($keys_selects[$item])){
            return $this->error->error(mensaje: 'Error $params debe ser un objeto', data: $keys_selects[$item]);
        }

        if(!is_object($modelo)){
            return $this->error->error(mensaje: 'Error modelo no es un objeto valido', data: $modelo);
        }
        return true;
    }

    /**
     * Valida que la entrada de item sea correcta, debe ser un texto con letras
     * @param string $item Valor a verificar
     * @return bool|array
     * @version 10.6.0
     */
    private function valida_item(string $item): bool|array
    {
        $item = trim($item);
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item es un numero', data: $item);
        }
        return true;
    }

    /**
     * REG
     * Valida que una propiedad y su valor sean correctos para ser utilizados en CSS.
     *
     * Esta función valida que tanto la propiedad CSS como su valor no estén vacíos y que la propiedad no sea un valor numérico.
     * Si alguna de estas condiciones no se cumple, se genera un mensaje de error. Si ambos son válidos, la función retorna `true`.
     *
     * **Pasos de procesamiento:**
     * 1. Se recorta el valor de la propiedad y el valor para eliminar cualquier espacio adicional al principio y al final.
     * 2. Se valida que la propiedad no esté vacía y que no sea numérica.
     * 3. Se valida que el valor no esté vacío.
     * 4. Si alguna validación falla, se genera un mensaje de error.
     * 5. Si todas las validaciones son correctas, se retorna `true`.
     *
     * **Parámetros:**
     *
     * @param string $propiedad El nombre de la propiedad CSS (por ejemplo, `'color'`, `'font-size'`, etc.).
     *                          Este parámetro es obligatorio y debe ser una cadena no vacía ni numérica.
     * @param string $valor El valor correspondiente a la propiedad CSS (por ejemplo, `'red'`, `'12px'`, etc.).
     *                      Este parámetro es obligatorio y debe ser una cadena no vacía.
     *
     * **Retorno:**
     * - Devuelve `true` si la propiedad y el valor son válidos.
     * - Si alguna de las validaciones falla, devuelve un arreglo con el mensaje de error correspondiente.
     *
     * **Ejemplos:**
     *
     * **Ejemplo 1: Validación exitosa**
     * ```php
     * $propiedad = "color";
     * $valor = "red";
     * $resultado = $this->valida_propiedad($propiedad, $valor);
     * // Retorna: true
     * ```
     *
     * **Ejemplo 2: Error por propiedad vacía**
     * ```php
     * $propiedad = "";
     * $valor = "red";
     * $resultado = $this->valida_propiedad($propiedad, $valor);
     * // Retorna: "Error propiedad esta vacio"
     * ```
     *
     * **Ejemplo 3: Error por valor vacío**
     * ```php
     * $propiedad = "color";
     * $valor = "";
     * $resultado = $this->valida_propiedad($propiedad, $valor);
     * // Retorna: "Error valor esta vacio"
     * ```
     *
     * **Ejemplo 4: Error por propiedad numérica**
     * ```php
     * $propiedad = "123";
     * $valor = "red";
     * $resultado = $this->valida_propiedad($propiedad, $valor);
     * // Retorna: "Error propiedad debe ser texto"
     * ```
     *
     * **@version 1.0.0**
     */
    private function valida_propiedad(string $propiedad, string $valor): bool|array
    {
        // Recortar espacios adicionales de la propiedad y el valor
        $propiedad = trim($propiedad);
        if ($propiedad === '') {
            return $this->error->error(mensaje: 'Error propiedad esta vacio', data: $propiedad);
        }

        // Validar que el valor no esté vacío
        $valor = trim($valor);
        if ($valor === '') {
            return $this->error->error(mensaje: 'Error valor esta vacio', data: $valor);
        }

        // Validar que la propiedad no sea un número
        if (is_numeric($propiedad)) {
            return $this->error->error(mensaje: 'Error propiedad debe ser texto', data: $propiedad);
        }

        // Si todo es válido, retornar true
        return true;
    }








}
