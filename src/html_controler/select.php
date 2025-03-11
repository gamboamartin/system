<?php
namespace gamboamartin\system\html_controler;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class select{
    protected errores $error;
    protected validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Genera un objeto con las claves de descripción y descripción seleccionada para un conjunto de datos.
     *
     * Este método permite definir las claves que serán utilizadas para la descripción de los registros
     * en un select o una lista de datos. Si `$columns_ds` tiene valores, se asigna el primer elemento de ese
     * array como las claves `key_descripcion` y `key_descripcion_select`.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $dataKeys = $objeto->data_keys(
     *     columns_ds: ['nombre_producto', 'codigo_producto'],
     *     key_descripcion: 'descripcion_producto',
     *     key_descripcion_select: 'descripcion_producto_select'
     * );
     * echo json_encode($dataKeys);
     * ```
     *
     * ### Ejemplo de salida esperada:
     * Si `$columns_ds` tiene valores:
     * ```json
     * {
     *   "key_descripcion": "nombre_producto",
     *   "key_descripcion_select": "nombre_producto"
     * }
     * ```
     * Si `$columns_ds` está vacío:
     * ```json
     * {
     *   "key_descripcion": "descripcion_producto",
     *   "key_descripcion_select": "descripcion_producto_select"
     * }
     * ```
     *
     * @param array $columns_ds Lista de columnas disponibles en el dataset. Si tiene valores,
     *                          la primera columna se usa como `key_descripcion` y `key_descripcion_select`.
     * @param string $key_descripcion Clave por defecto para la descripción.
     * @param string $key_descripcion_select Clave por defecto para la descripción seleccionada.
     * @return stdClass Devuelve un objeto con las propiedades `key_descripcion` y `key_descripcion_select`.
     */
    private function data_keys(array $columns_ds, string $key_descripcion, string $key_descripcion_select): stdClass
    {
        $data = new stdClass();
        $data->key_descripcion = $key_descripcion;
        $data->key_descripcion_select = $key_descripcion_select;

        // Si hay columnas en $columns_ds, usar la primera como clave de descripción
        if (count($columns_ds) > 0) {
            $data->key_descripcion = $columns_ds[0];
            $data->key_descripcion_select = $columns_ds[0];
        }

        return $data;
    }


    /**
     * REG
     * Genera un objeto con las claves de descripción y descripción seleccionada para un conjunto de datos.
     *
     * Este método permite definir las claves que serán utilizadas para la descripción de los registros
     * en un select o una lista de datos, asegurando que los valores no estén vacíos y asignando valores
     * predeterminados si es necesario.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $dataKeys = $objeto->genera_data_keys(
     *     columns_ds: ['nombre_producto', 'codigo_producto'],
     *     key_descripcion: 'descripcion_producto',
     *     key_descripcion_select: 'descripcion_producto_select',
     *     tabla: 'productos'
     * );
     * echo json_encode($dataKeys);
     * ```
     *
     * ### Ejemplo de salida esperada:
     * Si `$columns_ds` tiene valores:
     * ```json
     * {
     *   "key_descripcion": "nombre_producto",
     *   "key_descripcion_select": "nombre_producto"
     * }
     * ```
     * Si `$columns_ds` está vacío:
     * ```json
     * {
     *   "key_descripcion": "productos_descripcion",
     *   "key_descripcion_select": "productos_descripcion_select"
     * }
     * ```
     *
     * @param array $columns_ds Lista de columnas disponibles en el dataset. Si tiene valores,
     *                          la primera columna se usa como `key_descripcion` y `key_descripcion_select`.
     * @param string $key_descripcion Clave predeterminada para la descripción.
     * @param string $key_descripcion_select Clave predeterminada para la descripción seleccionada.
     * @param string $tabla Nombre de la tabla en la base de datos.
     *
     * @return stdClass|array Devuelve un objeto con las propiedades `key_descripcion` y `key_descripcion_select`,
     *                        o un array con el error en caso de fallo.
     */
    private function genera_data_keys(
        array $columns_ds,
        string $key_descripcion,
        string $key_descripcion_select,
        string $tabla
    ): array|stdClass
    {
        // Validar que la tabla no esté vacía
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        // Obtener la clave de descripción seleccionada
        $key_descripcion_select = $this->key_descripcion_select(
            key_descripcion_select: $key_descripcion_select,
            tabla: $tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar key_descripcion_select',
                data: $key_descripcion_select
            );
        }

        // Obtener la clave de descripción
        $key_descripcion = $this->key_descripcion(
            key_descripcion: $key_descripcion,
            tabla: $tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar key_descripcion',
                data: $key_descripcion
            );
        }

        // Generar las claves de descripción con base en columnas disponibles
        $data_keys = $this->data_keys(
            columns_ds: $columns_ds,
            key_descripcion: $key_descripcion,
            key_descripcion_select: $key_descripcion_select
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar data_keys',
                data: $data_keys
            );
        }

        return $data_keys;
    }


    /**
     * REG
     * Genera un array de valores para un select a partir de un conjunto de registros.
     *
     * Este método se encarga de validar que los registros contienen las claves `id` y `descripcion_select`,
     * y luego llama a `values()` para procesar los valores correctamente.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'producto_id',
     *     'descripcion_select' => 'producto_nombre_select'
     * ];
     *
     * $registros = [
     *     ['producto_id' => 1, 'producto_nombre' => 'Laptop HP'],
     *     ['producto_id' => 2, 'producto_nombre' => 'Mouse Logitech']
     * ];
     *
     * $values = $this->genera_values_selects(
     *     aplica_default: true,
     *     keys: $keys,
     *     registros: $registros,
     *     tabla: 'producto'
     * );
     *
     * print_r($values);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     1 => [
     *         'producto_id' => 1,
     *         'producto_nombre' => 'Laptop HP',
     *         'descripcion_select' => '1 Laptop HP'
     *     ],
     *     2 => [
     *         'producto_id' => 2,
     *         'producto_nombre' => 'Mouse Logitech',
     *         'descripcion_select' => '2 Mouse Logitech'
     *     ]
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$keys` no contiene `id` o `descripcion_select`, devuelve un error.
     * - Si `values()` falla, devuelve un error.
     *
     * @param bool $aplica_default Indica si se deben asignar valores predeterminados a `descripcion_select`.
     * @param stdClass $keys Objeto con las claves `id` y `descripcion_select` para los registros.
     * @param array $registros Conjunto de registros a procesar.
     * @param string $tabla Nombre de la tabla en la base de datos.
     *
     * @return array Devuelve un array `$values` con los registros procesados.
     * @throws array Retorna un error si `$keys` no contiene los campos requeridos o si ocurre un fallo en `values()`.
     */
    private function genera_values_selects(
        bool $aplica_default, stdClass $keys, array $registros, string $tabla): array
    {
        // Validar que los keys contengan 'id' y 'descripcion_select'
        $keys_valida = array('id', 'descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar keys',
                data: $valida
            );
        }

        // Generar los valores procesados
        $values = $this->values(
            aplica_default: $aplica_default,
            keys: $keys,
            registros: $registros,
            tabla: $tabla
        );

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al integrar values para select',
                data: $values
            );
        }

        return $values;
    }


    /**
     * REG
     * Inicializa los datos de un `select` para su uso en formularios.
     *
     * Este método genera las claves (`keys`) necesarias para un `select`, obtiene los valores (`values`)
     * disponibles en la base de datos y genera la etiqueta (`label`) para la presentación en la interfaz de usuario.
     *
     * ## Ejemplo de Uso:
     * ```php
     * $modelo = new ProductoModelo($pdo);
     * $select_data = $this->init_data_select(
     *     con_registros: true,
     *     modelo: $modelo,
     *     aplica_default: true,
     *     columns_ds: ['nombre', 'codigo'],
     *     key_descripcion: 'descripcion',
     *     key_descripcion_select: 'descripcion_select',
     *     key_id: 'producto_id',
     *     label: 'Seleccione un producto'
     * );
     * print_r($select_data);
     * ```
     *
     * ## Salida esperada:
     * ```json
     * {
     *   "id": "producto_id",
     *   "descripcion_select": "descripcion_select",
     *   "name": "producto_id",
     *   "descripcion": "descripcion",
     *   "values": {
     *     "1": {
     *       "id": "1",
     *       "descripcion_select": "Producto A"
     *     },
     *     "2": {
     *       "id": "2",
     *       "descripcion_select": "Producto B"
     *     }
     *   },
     *   "label": "Seleccione un producto"
     * }
     * ```
     *
     * ## Manejo de Errores:
     * - Si ocurre un error en la generación de `keys`, `values` o `label`, el método devuelve un array con detalles del error.
     * - Si `$modelo->tabla` está vacío, se devuelve un error.
     * - Si algún paso intermedio (generación de valores o claves) falla, el método lo captura y devuelve la descripción del error.
     *
     * @param bool $con_registros Indica si se deben obtener los registros de la base de datos.
     * @param modelo $modelo Instancia del modelo de datos a utilizar.
     * @param bool $aplica_default Indica si se deben aplicar valores por defecto en el `select`.
     * @param array $columns_ds Lista de columnas a incluir en los valores del `select`.
     * @param array $extra_params_keys Claves adicionales para parámetros extra.
     * @param array $filtro Filtros para limitar los registros obtenidos.
     * @param string $key_descripcion Clave para la descripción principal.
     * @param string $key_descripcion_select Clave para la descripción usada en `select`.
     * @param string $key_id Clave del identificador del registro.
     * @param string $key_value_custom Clave personalizada para los valores.
     * @param string $label Etiqueta para mostrar en la UI.
     * @param string $name Nombre del campo en HTML.
     * @param array $not_in Lista de valores a excluir.
     * @param array $in Lista de valores a incluir.
     * @param array $registros Lista de registros a incluir en el `select` (si `$con_registros` es `false`).
     *
     * @return array|stdClass Devuelve un objeto con las claves (`keys`), valores (`values`) y etiqueta (`label`),
     *                        o un array con un mensaje de error en caso de fallo.
     */
    final public function init_data_select(
        bool $con_registros,
        modelo $modelo,
        bool $aplica_default = true,
        array $columns_ds = array(),
        array $extra_params_keys = array(),
        array $filtro = array(),
        string $key_descripcion = '',
        string $key_descripcion_select = '',
        string $key_id = '',
        string $key_value_custom = '',
        string $label = '',
        string $name = '',
        array $not_in = array(),
        array $in = array(),
        array $registros = array()
    ): array|stdClass {

        // Genera las claves base para el `select`
        $keys = $this->keys_base(
            tabla: $modelo->tabla,
            key_descripcion: $key_descripcion,
            key_descripcion_select: $key_descripcion_select,
            key_id: $key_id,
            name: $name,
            columns_ds: $columns_ds
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar keys',
                data: $keys
            );
        }

        // Obtiene los valores para el `select`
        $values = $this->values_selects(
            aplica_default: $aplica_default,
            columns_ds: $columns_ds,
            con_registros: $con_registros,
            extra_params_keys: $extra_params_keys,
            filtro: $filtro,
            in: $in,
            key_value_custom: $key_value_custom,
            keys: $keys,
            modelo: $modelo,
            not_in: $not_in,
            registros: $registros
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener valores',
                data: $values
            );
        }

        // Genera el label para el `select`
        $label_ = $this->label_(
            label: $label,
            tabla: $modelo->tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener label',
                data: $label_
            );
        }

        // Asigna los valores y el label a las claves base
        $keys->values = $values;
        $keys->label = $label_;

        return $keys;
    }


    /**
     * REG
     * Genera la clave de descripción de un elemento basado en el nombre de la tabla.
     *
     * Este método valida que el nombre de la tabla no esté vacío y, si `$key_descripcion` está vacío,
     * genera una clave predeterminada basada en el nombre de la tabla, agregando el sufijo `_descripcion`.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $key = $objeto->key_descripcion('', 'producto');
     * echo $key; // producto_descripcion
     *
     * $key = $objeto->key_descripcion('nombre_producto', 'producto');
     * echo $key; // nombre_producto
     * ```
     *
     * ### Ejemplo de salida esperada:
     * - Entrada: `('', 'producto')` → Salida: `'producto_descripcion'`
     * - Entrada: `('nombre_producto', 'producto')` → Salida: `'nombre_producto'`
     * - Entrada: `('', '')` → **Error: "Error $tabla esta vacia"**
     *
     * @param string $key_descripcion Clave de descripción proporcionada. Si está vacía, se generará automáticamente.
     * @param string $tabla Nombre de la tabla asociada. No debe estar vacío.
     * @return array|string Devuelve la clave de descripción generada o un error si la tabla está vacía.
     */
    private function key_descripcion(string $key_descripcion, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error $tabla esta vacia', data: $tabla, es_final: true);
        }

        $key_descripcion = trim($key_descripcion);
        if ($key_descripcion === '') {
            $key_descripcion = $tabla . '_descripcion';
        }

        return $key_descripcion;
    }



    /**
     * REG
     * Integra una clave de descripción seleccionada en un registro de datos.
     *
     * Esta función valida la tabla, verifica si la clave `descripcion_select` está presente en el registro,
     * y en caso contrario, la genera utilizando la función `key_descripcion_select_default()`.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'producto_id',
     *     'descripcion_select' => 'producto_nombre_select'
     * ];
     *
     * $registro = [
     *     'producto_id' => 101,
     *     'producto_nombre' => 'Laptop HP'
     * ];
     *
     * $resultado = $this->integra_descripcion_select(
     *     aplica_default: true,
     *     keys: $keys,
     *     registro: $registro,
     *     tabla: 'producto'
     * );
     *
     * print_r($resultado);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     'producto_id' => 101,
     *     'producto_nombre' => 'Laptop HP',
     *     'producto_nombre_select' => '101 Laptop HP'
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$tabla` está vacía, devuelve un error.
     * - Si `$keys->id` no está definido, devuelve un error.
     * - Si ocurre un error al asignar `descripcion_select`, se devuelve un error detallado.
     *
     * @param bool $aplica_default Indica si se debe generar `descripcion_select` en caso de que no esté presente.
     * @param stdClass $keys Objeto que contiene las claves necesarias para la asignación.
     *                       - `id`: Clave del identificador del registro. Ejemplo: `'producto_id'`
     *                       - `descripcion_select`: Clave de la descripción para select. Ejemplo: `'producto_nombre_select'`
     * @param array $registro Array asociativo con los datos del registro.
     *                        Debe contener las claves indicadas en `$keys`.
     * @param string $tabla Nombre de la tabla en la base de datos.
     *
     * @return array Devuelve el registro modificado con la clave `descripcion_select` generada.
     * @throws array Retorna un error si `$tabla` está vacío o `$keys->id` no existe.
     */
    private function integra_descripcion_select(
        bool $aplica_default, stdClass $keys, array $registro, string $tabla): array
    {
        // Validar que la tabla no esté vacía
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error $tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        // Generar la clave de descripción con base en el nombre de la tabla
        $key_descripcion = $tabla . '_descripcion';

        // Verificar si el registro ya tiene `descripcion_select` y si se debe aplicar el valor por defecto
        if (!isset($registro[$keys->descripcion_select]) && $aplica_default) {

            // Validar si `id` está presente en `$keys`
            if (!isset($keys->id)) {
                return $this->error->error(
                    mensaje: 'Error $keys->id no existe',
                    data: $keys,
                    es_final: true
                );
            }

            // Generar el valor de `descripcion_select` utilizando `key_descripcion_select_default()`
            $registro = $this->key_descripcion_select_default(
                key_descripcion: $key_descripcion,
                keys: $keys,
                registro: $registro
            );

            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al asignar descripcion_select',
                    data: $registro
                );
            }
        }

        return $registro;
    }


    /**
     * REG
     * Genera la clave de descripción para la selección de un elemento en una tabla.
     *
     * Este método valida que el nombre de la tabla no esté vacío y genera una clave de descripción
     * para un select basado en el nombre de la tabla si `$key_descripcion_select` no se proporciona.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $key = $objeto->key_descripcion_select('', 'usuario');
     * echo $key; // usuario_descripcion_select
     *
     * $key = $objeto->key_descripcion_select('nombre_completo', 'usuario');
     * echo $key; // nombre_completo
     * ```
     *
     * ### Ejemplo de salida esperada:
     * - Entrada: `('', 'producto')` → Salida: `'producto_descripcion_select'`
     * - Entrada: `('nombre_producto', 'producto')` → Salida: `'nombre_producto'`
     *
     * @param string $key_descripcion_select Clave de descripción proporcionada. Si está vacía, se generará automáticamente.
     * @param string $tabla Nombre de la tabla asociada. No debe estar vacío.
     * @return string|array Devuelve la clave de descripción generada o un error si la tabla está vacía.
     */
    private function key_descripcion_select(string $key_descripcion_select, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla, es_final: true);
        }

        $key_descripcion_select = trim($key_descripcion_select);
        if ($key_descripcion_select === '') {
            $key_descripcion_select = $tabla . '_descripcion_select';
        }

        return $key_descripcion_select;
    }


    /**
     * REG
     * Genera un identificador clave (`key_id`) basado en el nombre de la tabla.
     *
     * Este método valida si el identificador clave (`key_id`) está vacío y, en ese caso, lo genera concatenando
     * el nombre de la tabla con el sufijo `_id`. También valida que el nombre de la tabla no esté vacío.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $key_id = $objeto->key_id('', 'usuario');
     * echo $key_id; // usuario_id
     *
     * $key_id = $objeto->key_id('cliente_id', 'cliente');
     * echo $key_id; // cliente_id
     * ```
     *
     * ### Ejemplo de salida esperada:
     * - Entrada: `('', 'usuario')` → Salida: `'usuario_id'`
     * - Entrada: `('cliente_id', 'cliente')` → Salida: `'cliente_id'`
     * - Entrada: `('', '')` → Salida: `array` con error
     *
     * @param string $key_id Identificador clave proporcionado. Si está vacío, se generará automáticamente.
     * @param string $tabla Nombre de la tabla en cuestión. No debe estar vacío.
     * @return string|array Devuelve el identificador clave generado o el mismo proporcionado.
     *                      En caso de error, devuelve un array con el mensaje de error.
     */
    private function key_id(string $key_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        if ($key_id === '') {
            $key_id = $tabla . '_id';
        }

        return $key_id;
    }



    /**
     * REG
     * Genera un objeto con las claves necesarias para la estructuración de un `select` o una consulta de datos.
     *
     * Este método asigna identificadores clave (`id`), nombres de campo (`name`), y descripciones (`descripcion`)
     * asegurando que los valores sean consistentes y no estén vacíos. Si los valores proporcionados están vacíos,
     * se generan con base en el nombre de la tabla y los parámetros predeterminados.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $keys = $objeto->keys_base(
     *     tabla: 'clientes',
     *     key_descripcion: 'nombre_cliente',
     *     key_descripcion_select: 'nombre_cliente_select',
     *     key_id: 'id_cliente',
     *     name: 'select_cliente',
     *     columns_ds: ['nombre_cliente', 'apellido_cliente']
     * );
     * print_r($keys);
     * ```
     *
     * ### Ejemplo de salida esperada:
     * ```json
     * {
     *   "id": "id_cliente",
     *   "descripcion_select": "nombre_cliente_select",
     *   "name": "select_cliente",
     *   "descripcion": "nombre_cliente"
     * }
     * ```
     * Si los valores opcionales están vacíos:
     * ```json
     * {
     *   "id": "clientes_id",
     *   "descripcion_select": "clientes_descripcion_select",
     *   "name": "clientes_id",
     *   "descripcion": "clientes_descripcion"
     * }
     * ```
     *
     * @param string $tabla Nombre de la tabla en la base de datos.
     * @param string $key_descripcion Clave de descripción principal (opcional, por defecto se genera como `{tabla}_descripcion`).
     * @param string $key_descripcion_select Clave para la descripción del `select` (opcional, por defecto `{tabla}_descripcion_select`).
     * @param string $key_id Clave identificadora (opcional, por defecto `{tabla}_id`).
     * @param string $name Nombre del campo `name` en HTML (opcional, por defecto `{tabla}_id`).
     * @param array $columns_ds Lista de columnas disponibles en el dataset para asignar descripciones dinámicas.
     *
     * @return stdClass|array Devuelve un objeto con las propiedades `id`, `descripcion_select`, `name`, y `descripcion`,
     *                        o un array con el error en caso de fallo.
     */
    private function keys_base(
        string $tabla,
        string $key_descripcion = '',
        string $key_descripcion_select = '',
        string $key_id = '',
        string $name = '',
        array $columns_ds = array()
    ): stdClass|array
    {
        // Validar que la tabla no esté vacía
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        // Obtener el key ID, si está vacío se genera con base en la tabla
        $key_id = $this->key_id(key_id: $key_id, tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar key_id',
                data: $key_id
            );
        }

        // Obtener el nombre, si está vacío se usa el key ID
        $name = $this->name(key_id: $key_id, name: $name);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar name',
                data: $name
            );
        }

        // Generar claves de descripción y descripción seleccionada
        $data_keys = $this->genera_data_keys(
            columns_ds: $columns_ds,
            key_descripcion: $key_descripcion,
            key_descripcion_select: $key_descripcion_select,
            tabla: $tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar data_keys',
                data: $data_keys
            );
        }

        // Crear y retornar el objeto con las claves generadas
        $data = new stdClass();
        $data->id = $key_id;
        $data->descripcion_select = $data_keys->key_descripcion_select;
        $data->name = $name;
        $data->descripcion = $data_keys->key_descripcion;

        return $data;
    }


    /**
     * REG
     * Genera una clave de descripción seleccionada para un registro de datos.
     *
     * Esta función toma un registro y asigna un valor a la clave de `descripcion_select`,
     * combinando el identificador (`id`) con la descripción (`key_descripcion`).
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'usuario_id',
     *     'descripcion_select' => 'usuario_nombre_select'
     * ];
     *
     * $registro = [
     *     'usuario_id' => 5,
     *     'usuario_nombre' => 'Juan Pérez'
     * ];
     *
     * $resultado = $this->key_descripcion_select_default(
     *     key_descripcion: 'usuario_nombre',
     *     keys: $keys,
     *     registro: $registro
     * );
     *
     * print_r($resultado);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     'usuario_id' => 5,
     *     'usuario_nombre' => 'Juan Pérez',
     *     'usuario_nombre_select' => '5 Juan Pérez'
     * ]
     * ```
     *
     * @param string $key_descripcion Clave de la descripción principal del registro.
     *                                Ejemplo: `'usuario_nombre'`
     * @param stdClass $keys Objeto que contiene las claves necesarias para la asignación.
     *                       - `id`: Clave del identificador del registro. Ejemplo: `'usuario_id'`
     *                       - `descripcion_select`: Clave de la descripción para select. Ejemplo: `'usuario_nombre_select'`
     * @param array $registro Array asociativo con los datos del registro.
     *                        Debe contener las claves indicadas en `$keys`.
     *
     * @return array Devuelve el registro modificado con la clave `descripcion_select` generada.
     * @throws array Retorna un error si alguna clave es inexistente o vacía.
     */
    private function key_descripcion_select_default(string $key_descripcion, stdClass $keys, array $registro): array
    {
        // Verifica que el objeto keys contenga las claves necesarias
        if (!isset($keys->id)) {
            return $this->error->error(
                mensaje: 'Error $keys->id no existe',
                data: $keys,
                es_final: true
            );
        }

        if (!isset($keys->descripcion_select)) {
            return $this->error->error(
                mensaje: 'Error $keys->descripcion_select no existe',
                data: $keys,
                es_final: true
            );
        }

        // Validar que las claves no estén vacías
        $key_id = trim($keys->id);
        if ($key_id === '') {
            return $this->error->error(
                mensaje: 'Error $keys->id esta vacio',
                data: $keys,
                es_final: true
            );
        }

        $key_descripcion_select = trim($keys->descripcion_select);
        if ($key_descripcion_select === '') {
            return $this->error->error(
                mensaje: 'Error $keys->descripcion_select esta vacio',
                data: $keys,
                es_final: true
            );
        }

        // Verificar que el registro contenga las claves requeridas
        $keys_val_row = array($keys->id, $key_descripcion);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val_row, registro: $registro);

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar registro',
                data: $valida,
                es_final: true
            );
        }

        // Generar la clave de descripción seleccionada
        $descripcion_select = $registro[$keys->id] . ' ' . $registro[$key_descripcion];

        // Asignar el valor al registro
        $registro[$keys->descripcion_select] = $descripcion_select;

        return $registro;
    }


    /**
     * REG
     * Genera un label a partir del nombre de una tabla.
     *
     * Este método convierte el nombre de una tabla en un texto más legible, adecuado para su uso como etiqueta
     * en formularios o interfaces de usuario. Reemplaza los guiones bajos (`_`) por espacios y capitaliza
     * cada palabra.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $label = $this->label('usuario_direccion');
     * echo $label; // "Usuario Direccion"
     * ```
     *
     * ```php
     * $label = $this->label('producto_precio');
     * echo $label; // "Producto Precio"
     * ```
     *
     * ## Salida Esperada:
     *
     * - Entrada: `"usuario_direccion"` → Salida: `"Usuario Direccion"`
     * - Entrada: `"producto_precio"` → Salida: `"Producto Precio"`
     * - Entrada: `""` → **Error: `"Error tabla esta vacia"`**
     *
     * ## Manejo de Errores:
     * - Si `$tabla` está vacía, devuelve un error.
     * - Si el label generado queda vacío, devuelve un error.
     *
     * @param string $tabla Nombre de la tabla que será convertida en etiqueta.
     * @return string|array Devuelve el label generado o un array con el mensaje de error en caso de fallo.
     */
    private function label(string $tabla): string|array
    {
        // Eliminar espacios en blanco al inicio y al final del string
        $tabla = trim($tabla);

        // Validar que el nombre de la tabla no esté vacío
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error tabla esta vacia',
                data: $tabla
            );
        }

        // Reemplazar guiones bajos por espacios
        $label = str_replace('_', ' ', $tabla);

        // Eliminar espacios en blanco nuevamente
        $label = trim($label);

        // Validar que el label generado no esté vacío
        if ($label === '') {
            return $this->error->error(
                mensaje: 'Error $label esta vacio',
                data: $label
            );
        }

        // Capitalizar cada palabra
        return ucwords($label);
    }


    /**
     * REG
     * Genera o asigna una etiqueta (`label`) para una tabla.
     *
     * Este método permite definir un `label` para una tabla en un formulario o interfaz de usuario.
     * Si el parámetro `$label` está vacío, se genera un label automáticamente a partir del nombre de la tabla
     * mediante la función `label()`, que transforma los nombres en un formato legible.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $label = $this->label_('', 'usuario_direccion');
     * echo $label; // "Usuario Direccion"
     *
     * $label = $this->label_('Dirección del Usuario', 'usuario_direccion');
     * echo $label; // "Dirección del Usuario"
     * ```
     *
     * ## Salida Esperada:
     *
     * | Entrada (`$label`, `$tabla`)    | Salida                         |
     * |---------------------------------|--------------------------------|
     * | `('', 'usuario_direccion')`     | `"Usuario Direccion"`          |
     * | `('Nombre Completo', 'usuario')` | `"Nombre Completo"`           |
     * | `('', '')`                      | **Error: "Error tabla esta vacia"** |
     *
     * ## Manejo de Errores:
     * - Si `$tabla` está vacía, se devuelve un error.
     * - Si `$label` está vacío, se genera automáticamente con la función `label()`.
     * - Si `label()` produce un error, el mismo se devuelve.
     *
     * @param string $label Etiqueta proporcionada para la tabla. Si está vacía, se genera automáticamente.
     * @param string $tabla Nombre de la tabla en la base de datos.
     * @return array|string Devuelve la etiqueta generada o un array con un mensaje de error en caso de fallo.
     */
    private function label_(string $label, string $tabla): array|string
    {
        // Eliminar espacios en blanco del nombre de la tabla
        $tabla = trim($tabla);

        // Validar que el nombre de la tabla no esté vacío
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error tabla esta vacia',
                data: $tabla
            );
        }

        // Asignar el label si ya se proporcionó
        $label_ = $label;

        // Si no se proporcionó un label, generar uno a partir de la tabla
        if ($label_ === '') {
            $label_ = $this->label(tabla: $tabla);

            // Validar si la función label() arrojó un error
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al obtener label',
                    data: $label_
                );
            }
        }

        // Devolver el label generado o proporcionado
        return $label_;
    }


    /**
     * REG
     * Genera un nombre basado en el identificador clave (`key_id`).
     *
     * Este método verifica si el parámetro `$name` está vacío. En ese caso, asigna el valor de `$key_id`
     * como nombre. Si `$name` ya tiene un valor, lo devuelve sin modificaciones.
     *
     * ### Ejemplo de uso:
     * ```php
     * $objeto = new ClaseEjemplo();
     * $nombre = $objeto->name('usuario_id', '');
     * echo $nombre; // usuario_id
     *
     * $nombre = $objeto->name('cliente_id', 'nombre_cliente');
     * echo $nombre; // nombre_cliente
     * ```
     *
     * ### Ejemplo de salida esperada:
     * - Entrada: `('usuario_id', '')` → Salida: `'usuario_id'`
     * - Entrada: `('cliente_id', 'nombre_cliente')` → Salida: `'nombre_cliente'`
     *
     * @param string $key_id Identificador clave, utilizado como valor por defecto si `$name` está vacío.
     * @param string $name Nombre proporcionado. Si está vacío, se le asignará el valor de `$key_id`.
     * @return string Devuelve el nombre final asignado.
     */
    private function name(string $key_id, string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            $name = $key_id;
        }
        return $name;
    }


    /**
     * REG
     * Obtiene registros para un select basándose en filtros, columnas y opciones de inclusión.
     *
     * Esta función devuelve un conjunto de registros de una tabla en la base de datos, aplicando filtros y seleccionando
     * columnas específicas. Si `$con_registros` es `true` y `$registros` está vacío, se ejecuta `rows_select` para obtener
     * los registros desde la base de datos.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $columns_ds = ['email', 'telefono'];
     * $con_registros = true;
     * $extra_params_keys = ['fecha_registro'];
     * $filtro = ['usuarios.tipo' => 'admin'];
     * $in = ['usuarios.id' => [1, 2, 3]];
     * $key_value_custom = '';
     * $keys = (object)[
     *     'id' => 'usuarios_id',
     *     'descripcion' => 'usuarios_nombre',
     *     'descripcion_select' => 'usuarios_nombre'
     * ];
     * $modelo = new ModeloUsuarios($pdo); // Instancia del modelo basado en la clase `modelo`
     * $not_in = ['usuarios.id' => [5, 6]];
     * $registros = []; // Inicialmente vacío
     *
     * $resultados = $this->registros_select(
     *     columns_ds: $columns_ds,
     *     con_registros: $con_registros,
     *     extra_params_keys: $extra_params_keys,
     *     filtro: $filtro,
     *     in: $in,
     *     key_value_custom: $key_value_custom,
     *     keys: $keys,
     *     modelo: $modelo,
     *     not_in: $not_in,
     *     registros: $registros
     * );
     *
     * print_r($resultados);
     * ```
     *
     * ## Salida Esperada:
     * ```php
     * [
     *     [
     *         'usuarios_id' => 1,
     *         'usuarios_nombre' => 'Juan Pérez',
     *         'email' => 'juan@example.com',
     *         'telefono' => '555-1234',
     *         'fecha_registro' => '2024-01-01'
     *     ],
     *     [
     *         'usuarios_id' => 2,
     *         'usuarios_nombre' => 'María Gómez',
     *         'email' => 'maria@example.com',
     *         'telefono' => '555-5678',
     *         'fecha_registro' => '2024-02-01'
     *     ]
     * ]
     * ```
     *
     * @param array $columns_ds Columnas adicionales a incluir en la consulta.
     *                          Ejemplo: `['email', 'telefono']`
     * @param bool $con_registros Indica si se deben obtener registros desde la base de datos si `$registros` está vacío.
     * @param array $extra_params_keys Claves de parámetros extra que se deben incluir en los registros obtenidos.
     *                                 Ejemplo: `['fecha_registro']`
     * @param array $filtro Filtros que se aplicarán en la consulta SQL.
     *                      Ejemplo: `['usuarios.tipo' => 'admin']`
     * @param array $in Filtros tipo "IN" que permiten seleccionar valores específicos dentro de un conjunto.
     *                  Ejemplo: `['usuarios.id' => [1, 2, 3]]`
     * @param string $key_value_custom Clave personalizada para valores específicos en la consulta.
     *                                 Si está vacío, no se agrega ninguna clave personalizada.
     *                                 Ejemplo: `'usuarios_email'`
     * @param stdClass $keys Objeto con las claves necesarias para la consulta (id, descripción y descripción_select).
     *                       - `id`: Clave del identificador del registro. Ejemplo: `'usuarios_id'`
     *                       - `descripcion`: Clave de la descripción principal. Ejemplo: `'usuarios_nombre'`
     *                       - `descripcion_select`: Clave usada en select. Ejemplo: `'usuarios_nombre'`
     * @param modelo $modelo Instancia del modelo sobre el cual se realizará la consulta.
     * @param array $not_in Filtros tipo "NOT IN" para excluir valores específicos.
     *                      Ejemplo: `['usuarios.id' => [5, 6]]`
     * @param array $registros Registros existentes. Si está vacío y `$con_registros` es `true`, se obtienen desde la base de datos.
     *
     * @return array Registros obtenidos de la base de datos o el conjunto de registros proporcionado si `$registros` no está vacío.
     */
    private function registros_select(
        array $columns_ds,
        bool $con_registros,
        array $extra_params_keys,
        array $filtro,
        array $in,
        string $key_value_custom,
        stdClass $keys,
        modelo $modelo,
        array $not_in,
        array $registros
    ): array {
        // Si se solicita obtener registros y el array de registros está vacío, ejecutar la consulta
        if ($con_registros) {
            if (count($registros) === 0) {
                $registros = $this->rows_select(
                    columns_ds: $columns_ds,
                    extra_params_keys: $extra_params_keys,
                    filtro: $filtro,
                    in: $in,
                    key_value_custom: $key_value_custom,
                    keys: $keys,
                    modelo: $modelo,
                    not_in: $not_in
                );

                // Si ocurre un error al obtener los registros, retornar el error
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
                }
            }
        }

        return $registros;
    }



    /**
     * REG
     * Obtiene los registros para un select basándose en los filtros y columnas especificadas.
     *
     * Esta función genera una consulta SQL utilizando los filtros y columnas proporcionados, obteniendo registros
     * desde un modelo de base de datos. La consulta incluye validaciones de claves esenciales para asegurar
     * la estructura correcta de los datos.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $columns_ds = ['email', 'telefono'];
     * $extra_params_keys = ['fecha_registro'];
     * $filtro = ['usuarios.tipo' => 'admin'];
     * $in = ['usuarios.id' => [1, 2, 3]];
     * $key_value_custom = '';
     * $keys = (object)[
     *     'id' => 'usuarios_id',
     *     'descripcion' => 'usuarios_nombre',
     *     'descripcion_select' => 'usuarios_nombre'
     * ];
     * $modelo = new ModeloUsuarios($pdo); // Instancia del modelo que extiende la clase `modelo`
     * $not_in = ['usuarios.id' => [5, 6]];
     *
     * $resultados = $this->rows_select(
     *     columns_ds: $columns_ds,
     *     extra_params_keys: $extra_params_keys,
     *     filtro: $filtro,
     *     in: $in,
     *     key_value_custom: $key_value_custom,
     *     keys: $keys,
     *     modelo: $modelo,
     *     not_in: $not_in
     * );
     *
     * print_r($resultados);
     * ```
     *
     * ## Salida Esperada:
     * ```php
     * [
     *     [
     *         'usuarios_id' => 1,
     *         'usuarios_nombre' => 'Juan Pérez',
     *         'email' => 'juan@example.com',
     *         'telefono' => '555-1234',
     *         'fecha_registro' => '2024-01-01'
     *     ],
     *     [
     *         'usuarios_id' => 2,
     *         'usuarios_nombre' => 'María Gómez',
     *         'email' => 'maria@example.com',
     *         'telefono' => '555-5678',
     *         'fecha_registro' => '2024-02-01'
     *     ]
     * ]
     * ```
     *
     * @param array $columns_ds Lista de columnas adicionales a incluir en la consulta.
     *                          Ejemplo: `['email', 'telefono']`
     * @param array $extra_params_keys Claves de parámetros extra que se deben incluir en los registros obtenidos.
     *                                 Ejemplo: `['fecha_registro']`
     * @param array $filtro Filtros que se aplicarán en la consulta SQL.
     *                      Ejemplo: `['usuarios.tipo' => 'admin']`
     * @param array $in Filtros tipo "IN" que permiten seleccionar valores específicos dentro de un conjunto.
     *                  Ejemplo: `['usuarios.id' => [1, 2, 3]]`
     * @param string $key_value_custom Clave personalizada para valores específicos en la consulta.
     *                                 Si está vacío, no se agrega ninguna clave personalizada.
     *                                 Ejemplo: `'usuarios_email'`
     * @param stdClass $keys Objeto con las claves necesarias para la consulta (id, descripción y descripción_select).
     *                       - `id`: Clave del identificador del registro. Ejemplo: `'usuarios_id'`
     *                       - `descripcion`: Clave de la descripción principal. Ejemplo: `'usuarios_nombre'`
     *                       - `descripcion_select`: Clave usada en select. Ejemplo: `'usuarios_nombre'`
     * @param modelo $modelo Instancia del modelo sobre el cual se realizará la consulta.
     * @param array $not_in Filtros tipo "NOT IN" para excluir valores específicos.
     *                      Ejemplo: `['usuarios.id' => [5, 6]]`
     *
     * @return array Registros obtenidos de la base de datos en un formato de array asociativo.
     *               - Cada registro contiene las claves especificadas en `$keys`, `$columns_ds` y `$extra_params_keys`.
     *               - Si ocurre un error, se devuelve un array con información del error.
     */
    private function rows_select(
        array $columns_ds,
        array $extra_params_keys,
        array $filtro,
        array $in,
        string $key_value_custom,
        stdClass $keys,
        modelo $modelo,
        array $not_in
    ): array {
        $keys_val = array('id', 'descripcion_select', 'descripcion');

        // Validar que las claves esenciales existan en el objeto $keys
        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val, registro: $keys);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar keys ', data: $valida);
        }

        // Inicializar el array de columnas a seleccionar en la consulta
        $columnas = [];
        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;
        $columnas[] = $keys->descripcion;

        // Si se especificó una clave personalizada, agregarla a la lista de columnas
        if ($key_value_custom !== '') {
            $columnas[] = $key_value_custom;
        }

        // Agregar las columnas adicionales desde `$columns_ds`
        foreach ($columns_ds as $column) {
            $column = trim($column);
            if ($column === '') {
                return $this->error->error(mensaje: 'Error el column de extra params esta vacio', data: $columns_ds);
            }
            $columnas[] = $column;
        }

        // Agregar las claves de parámetros extra desde `$extra_params_keys`
        foreach ($extra_params_keys as $key) {
            $key = trim($key);
            if ($key === '') {
                return $this->error->error(mensaje: 'Error el key de extra params esta vacio', data: $extra_params_keys);
            }
            $columnas[] = $key;
        }

        // Aplicar filtro por estatus activo a la tabla
        $filtro[$modelo->tabla . '.status'] = 'activo';

        // Ejecutar la consulta utilizando el método filtro_and del modelo
        $registros = $modelo->filtro_and(columnas: $columnas, filtro: $filtro, in: $in, not_in: $not_in);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }

        return $registros->registros;
    }


    /**
     * REG
     * Agrega un registro al array `$values`, asegurando la validez de las claves necesarias.
     *
     * Esta función valida que las claves esenciales (`id` y `descripcion_select`) existan en `$keys` y en `$registro`.
     * Luego, asigna el registro al array `$values` utilizando `id` como clave, asegurando que `descripcion_select`
     * también esté presente en la estructura de salida.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'usuario_id',
     *     'descripcion_select' => 'usuario_nombre_select'
     * ];
     *
     * $registro = [
     *     'usuario_id' => 1,
     *     'usuario_nombre_select' => 'Juan Pérez'
     * ];
     *
     * $values = [];
     *
     * $resultado = $this->value_select(
     *     keys: $keys,
     *     registro: $registro,
     *     values: $values
     * );
     *
     * print_r($resultado);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     1 => [
     *         'usuario_id' => 1,
     *         'usuario_nombre_select' => 'Juan Pérez',
     *         'descripcion_select' => 'Juan Pérez'
     *     ]
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$keys->id` o `$keys->descripcion_select` no están definidos, devuelve un error.
     * - Si `$keys->id` o `$keys->descripcion_select` están vacíos, devuelve un error.
     * - Si alguna de las claves no existe en `$registro`, devuelve un error.
     *
     * @param stdClass $keys Objeto con las claves de `id` y `descripcion_select`.
     *                       - `id`: Clave que identifica el registro. Ejemplo: `'usuario_id'`
     *                       - `descripcion_select`: Clave de la descripción seleccionada. Ejemplo: `'usuario_nombre_select'`
     * @param array $registro Registro de datos a agregar en `$values`. Debe contener las claves en `$keys`.
     * @param array $values Array donde se almacenan los registros procesados.
     *
     * @return array Retorna el array `$values` con el registro agregado.
     * @throws array Retorna un error si faltan claves en `$keys` o `$registro`, o si hay problemas de validación.
     */
    private function value_select(stdClass $keys, array $registro, array $values): array
    {
        // Validar existencia y contenido de `id`
        if (!isset($keys->id)) {
            return $this->error->error(
                mensaje: 'Error $keys->id no existe',
                data: $keys
            );
        }
        if (trim($keys->id) === '') {
            return $this->error->error(
                mensaje: 'Error $keys->id esta vacio',
                data: $keys
            );
        }

        // Validar existencia y contenido de `descripcion_select`
        if (!isset($keys->descripcion_select)) {
            return $this->error->error(
                mensaje: 'Error $keys->descripcion_select no existe',
                data: $keys
            );
        }
        if (trim($keys->descripcion_select) === '') {
            return $this->error->error(
                mensaje: 'Error $keys->descripcion_select esta vacio',
                data: $keys
            );
        }

        // Verificar que las claves necesarias existen en `$registro`
        $keys_valida = [$keys->id, $keys->descripcion_select];
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $registro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar registro',
                data: $valida
            );
        }

        // Asignar el registro a `$values` con `id` como clave
        $values[$registro[$keys->id]] = $registro;

        // Asegurar que `descripcion_select` esté presente en la salida
        $values[$registro[$keys->id]]['descripcion_select'] = $registro[$keys->descripcion_select];

        return $values;
    }


    /**
     * REG
     * Procesa un registro y lo integra en `$values`, asegurando que tenga una descripción válida.
     *
     * Esta función valida que la tabla no esté vacía y luego intenta asignar un valor a `descripcion_select`
     * en `$registro`. Si `$aplica_default` es `true`, llama a `value_select` para agregar el registro a `$values`.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'producto_id',
     *     'descripcion_select' => 'producto_nombre_select'
     * ];
     *
     * $registro = [
     *     'producto_id' => 5,
     *     'producto_nombre' => 'Laptop HP'
     * ];
     *
     * $values = [];
     *
     * $resultado = $this->value_select_row(
     *     aplica_default: true,
     *     keys: $keys,
     *     registro: $registro,
     *     tabla: 'producto',
     *     values: $values
     * );
     *
     * print_r($resultado);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     5 => [
     *         'producto_id' => 5,
     *         'producto_nombre' => 'Laptop HP',
     *         'descripcion_select' => '5 Laptop HP'
     *     ]
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$tabla` está vacía, devuelve un error.
     * - Si `integra_descripcion_select` falla, devuelve un error.
     * - Si `$aplica_default` es `true` y `value_select` falla, devuelve un error.
     *
     * @param bool $aplica_default Indica si se debe asignar un valor por defecto a `descripcion_select`.
     * @param stdClass $keys Objeto con las claves `id` y `descripcion_select`:
     *                       - `id`: Clave identificadora del registro.
     *                       - `descripcion_select`: Clave de la descripción seleccionada.
     * @param array $registro Registro de datos a procesar.
     * @param string $tabla Nombre de la tabla en la base de datos.
     * @param array $values Array donde se almacenan los registros procesados.
     *
     * @return array Retorna el array `$values` con el registro agregado si `$aplica_default` es `true`.
     * @throws array Retorna un error si `$tabla` está vacía o si alguna validación falla.
     */
    private function value_select_row(
        bool $aplica_default, stdClass $keys, array $registro, string $tabla, array $values
    ): array
    {
        // Validar que la tabla no esté vacía
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error $tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        // Integrar la descripción si es necesario
        $registro = $this->integra_descripcion_select(
            aplica_default: $aplica_default,
            keys: $keys,
            registro: $registro,
            tabla: $tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar descripcion_select',
                data: $registro
            );
        }

        // Si aplica_default es true, agregar el registro a values
        if ($aplica_default) {
            $values = $this->value_select(keys: $keys, registro: $registro, values: $values);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al integra values para select',
                    data: $values
                );
            }
        }

        return $values;
    }


    /**
     * REG
     * Genera un array de valores procesados a partir de registros, asegurando su correcta estructura.
     *
     * Este método recorre un conjunto de registros, validando cada uno y aplicando la lógica de `value_select_row`
     * para integrarlos en `$values`. Si `$aplica_default` es `true`, asigna valores predeterminados a `descripcion_select`.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $keys = (object)[
     *     'id' => 'producto_id',
     *     'descripcion_select' => 'producto_nombre_select'
     * ];
     *
     * $registros = [
     *     ['producto_id' => 1, 'producto_nombre' => 'Laptop HP'],
     *     ['producto_id' => 2, 'producto_nombre' => 'Mouse Logitech']
     * ];
     *
     * $values = $this->values(
     *     aplica_default: true,
     *     keys: $keys,
     *     registros: $registros,
     *     tabla: 'producto'
     * );
     *
     * print_r($values);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     1 => [
     *         'producto_id' => 1,
     *         'producto_nombre' => 'Laptop HP',
     *         'descripcion_select' => '1 Laptop HP'
     *     ],
     *     2 => [
     *         'producto_id' => 2,
     *         'producto_nombre' => 'Mouse Logitech',
     *         'descripcion_select' => '2 Mouse Logitech'
     *     ]
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$tabla` está vacía, devuelve un error.
     * - Si algún registro no es un array, devuelve un error.
     * - Si `value_select_row` falla, devuelve un error.
     *
     * @param bool $aplica_default Indica si se deben asignar valores predeterminados a `descripcion_select`.
     * @param stdClass $keys Objeto con las claves `id` y `descripcion_select`.
     * @param array $registros Conjunto de registros a procesar.
     * @param string $tabla Nombre de la tabla en la base de datos.
     *
     * @return array Devuelve un array `$values` con los registros procesados.
     * @throws array Retorna un error si `$tabla` está vacía o si alguna validación falla.
     */
    private function values(bool $aplica_default, stdClass $keys, array $registros, string $tabla): array
    {
        // Validar que la tabla no esté vacía
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: 'Error $tabla esta vacia',
                data: $tabla,
                es_final: true
            );
        }

        $values = array();

        // Procesar cada registro
        foreach ($registros as $registro) {
            if (!is_array($registro)) {
                return $this->error->error(
                    mensaje: 'Error registro debe ser un array',
                    data: $registro
                );
            }

            // Integrar el registro en values
            $values = $this->value_select_row(
                aplica_default: $aplica_default,
                keys: $keys,
                registro: $registro,
                tabla: $tabla,
                values: $values
            );

            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al integra values para select',
                    data: $values
                );
            }
        }

        return $values;
    }


    /**
     * REG
     * Genera un array de valores para un select basándose en registros obtenidos de un modelo.
     *
     * Este método se encarga de validar que los registros contienen las claves necesarias (`id`, `descripcion_select`,
     * `descripcion`), obtiene los registros a través de `registros_select()`, y luego, si `$aplica_default` es `true`,
     * procesa los valores con `genera_values_selects()`.
     *
     * ## Ejemplo de Uso:
     *
     * ```php
     * $modelo = new modelo($pdo);
     *
     * $keys = (object)[
     *     'id' => 'producto_id',
     *     'descripcion_select' => 'producto_nombre_select',
     *     'descripcion' => 'producto_nombre'
     * ];
     *
     * $registros = [
     *     ['producto_id' => 1, 'producto_nombre' => 'Laptop HP'],
     *     ['producto_id' => 2, 'producto_nombre' => 'Mouse Logitech']
     * ];
     *
     * $values = $this->values_selects(
     *     aplica_default: true,
     *     columns_ds: ['producto_nombre'],
     *     con_registros: true,
     *     extra_params_keys: [],
     *     filtro: [],
     *     in: [],
     *     key_value_custom: '',
     *     keys: $keys,
     *     modelo: $modelo,
     *     not_in: [],
     *     registros: $registros
     * );
     *
     * print_r($values);
     * ```
     *
     * ## Salida Esperada:
     *
     * ```php
     * [
     *     1 => [
     *         'producto_id' => 1,
     *         'producto_nombre' => 'Laptop HP',
     *         'descripcion_select' => '1 Laptop HP'
     *     ],
     *     2 => [
     *         'producto_id' => 2,
     *         'producto_nombre' => 'Mouse Logitech',
     *         'descripcion_select' => '2 Mouse Logitech'
     *     ]
     * ]
     * ```
     *
     * ## Manejo de Errores:
     * - Si `$keys` no contiene `id`, `descripcion_select` o `descripcion`, devuelve un error.
     * - Si `registros_select()` falla, devuelve un error.
     * - Si `genera_values_selects()` falla, devuelve un error.
     *
     * @param bool $aplica_default Indica si se deben asignar valores predeterminados a `descripcion_select`.
     * @param array $columns_ds Columnas a mostrar en cada registro.
     * @param bool $con_registros Indica si se deben recuperar registros de la base de datos.
     * @param array $extra_params_keys Claves adicionales a integrar en los registros.
     * @param array $filtro Filtros aplicados a la consulta.
     * @param array $in Valores para la condición `IN` en la consulta.
     * @param string $key_value_custom Clave personalizada para el value de los options.
     * @param stdClass $keys Objeto con las claves `id`, `descripcion_select` y `descripcion` para los registros.
     * @param modelo $modelo Instancia del modelo que maneja la base de datos.
     * @param array $not_in Valores para la condición `NOT IN` en la consulta.
     * @param array $registros Conjunto de registros a procesar.
     *
     * @return array Devuelve un array `$values` con los registros procesados.
     * @throws array Retorna un error si alguna validación o consulta falla.
     */
    private function values_selects(
        bool $aplica_default, array $columns_ds, bool $con_registros, array $extra_params_keys, array $filtro,
        array $in, string $key_value_custom, stdClass $keys, modelo $modelo, array $not_in, array $registros
    ): array
    {
        // Validar que $keys contenga las claves necesarias
        $keys_valida = array('id', 'descripcion_select', 'descripcion');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar keys',
                data: $valida
            );
        }

        // Obtener los registros de la base de datos si es necesario
        $registros = $this->registros_select(
            columns_ds: $columns_ds,
            con_registros: $con_registros,
            extra_params_keys: $extra_params_keys,
            filtro: $filtro,
            in: $in,
            key_value_custom: $key_value_custom,
            keys: $keys,
            modelo: $modelo,
            not_in: $not_in,
            registros: $registros
        );

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener registros',
                data: $registros
            );
        }

        // Si se aplica el valor por defecto, procesamos los valores
        $values = $registros;
        if ($aplica_default) {
            $values = $this->genera_values_selects(
                aplica_default: $aplica_default,
                keys: $keys,
                registros: $registros,
                tabla: $modelo->tabla
            );

            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al asignar valores',
                    data: $values
                );
            }
        }

        return $values;
    }

}
