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
     * Asigna los values de un select
     * @refactorizar Refactorizar
     * @param bool $aplica_default
     * @param stdClass $keys Keys para asignacion basica
     * @param array $registros Conjunto de registros a integrar
     * @param string $tabla Tabla del modelo en ejecucion
     * @return array
     * @version 0.48.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-02 18:12
     * @author mgamboa
     */
    private function genera_values_selects(bool $aplica_default,stdClass $keys, array $registros, string $tabla): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $values = $this->values(aplica_default: $aplica_default, keys: $keys, registros: $registros, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra values para select',data:  $values);
        }
        return $values;
    }

    /**
     * Inicializa los datos de un select
     * @param bool $con_registros Si no con registros integra el select vacio para ser llenado posterior con ajax
     * @param modelo $modelo Modelo en ejecucion para la asignacion de datos
     * @param bool $aplica_default
     * @param array $columns_ds Columnas a integrar en options
     * @param array $extra_params_keys Keys de extra params para ser cargados en un select
     * @param array $filtro Filtro para obtencion de datos para options
     * @param string $key_descripcion Key de descripcion
     * @param string $key_descripcion_select key del registro para mostrar en un select
     * @param string $key_id key Id de value para option
     * @param string $key_value_custom
     * @param string $label Etiqueta a mostrar
     * @param string $name Nombre del input
     * @param array $not_in Omite resultado de options
     * @param array $in
     * @param array $registros Registros para integrar en select
     * @return array|stdClass
     */
    final public function init_data_select(bool $con_registros, modelo $modelo, bool $aplica_default = true,
                                           array $columns_ds = array(), array $extra_params_keys = array(),
                                           array $filtro = array(), string $key_descripcion = '',
                                           string $key_descripcion_select= '', string $key_id = '',
                                           string $key_value_custom = '', string $label = '', string $name = '',
                                           array $not_in = array(), array $in = array(),
                                           array $registros = array()): array|stdClass
    {

        $keys = $this->keys_base(tabla: $modelo->tabla, key_descripcion: $key_descripcion,
            key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, name: $name,columns_ds: $columns_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(aplica_default: $aplica_default, columns_ds: $columns_ds,
            con_registros: $con_registros, extra_params_keys: $extra_params_keys, filtro: $filtro, in: $in,
            key_value_custom: $key_value_custom, keys: $keys, modelo: $modelo, not_in: $not_in, registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valores',data:  $values);
        }


        $label_ = $this->label_(label: $label,tabla:  $modelo->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener label', data: $label_);
        }

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



    private function integra_descripcion_select(bool $aplica_default, stdClass $keys, array $registro, string $tabla){
        $key_descripcion = $tabla.'_descripcion';
        if(!isset($registro[$keys->descripcion_select]) && $aplica_default){
            $registro = $this->key_descripcion_select_default(
                key_descripcion: $key_descripcion, keys: $keys, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar descripcion_select',data:  $registro);
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
     * Integra una descripcion select con id y descripcion
     * @param string $key_descripcion Key de la descripcion
     * @param stdClass $keys keys con key_id
     * @param array $registro Registro en proceso
     * @return array
     */
    private function key_descripcion_select_default(string $key_descripcion, stdClass $keys, array $registro): array
    {
        $keys_val_row = array($keys->id, $key_descripcion);


        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val_row, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
        }

        $descripcion_select = $registro[$keys->id] . ' ' . $registro[$key_descripcion];


        $registro[$keys->descripcion_select] = $descripcion_select;
        return $registro;
    }

    /**
     * Genera un label valido para se mostrado en front
     * @param string $tabla Tabla o estructura para generar etiqueta
     * @return string|array
     * @version 0.50.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-03 09:22
     * @author mgamboa
     */
    private function label(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $label = str_replace('_', ' ', $tabla);

        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error $label esta vacio', data: $label);
        }


        return ucwords($label);
    }

    /**
     * Ajusta el label de un registro select
     * @param string $label Label original
     * @param string $tabla Tabla origen
     * @return array|string
     * @version 8.68.0
     */
    private function label_(string $label, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        $label_ =$label;
        if($label_ === '') {
            $label_ = $this->label(tabla: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener label', data: $label_);
            }
        }
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
     * Obtiene los registros para un select
     * @param array $columns_ds Columnas a mostrar en cada registro
     * @param bool $con_registros Si con registros integrara los elementos del modelo
     * @param array $extra_params_keys Parametros para data extra
     * @param array $filtro Filtro de datos
     * @param array $in
     * @param string $key_value_custom
     * @param stdClass $keys Keys para la obtencion de campos
     * @param modelo $modelo Modelo de datos
     * @param array $not_in Integra elementos que se quieran omitir en los rows
     * @param array $registros Registros a integrar
     * @return array
     * @version 8.59.0
     */
    private function registros_select(array $columns_ds, bool $con_registros, array $extra_params_keys, array $filtro,
                                      array $in, string $key_value_custom, stdClass $keys, modelo $modelo,
                                      array $not_in, array $registros ): array
    {
        if($con_registros) {
            if(count($registros) === 0) {
                $registros = $this->rows_select(columns_ds: $columns_ds, extra_params_keys: $extra_params_keys,
                    filtro: $filtro, in: $in, key_value_custom: $key_value_custom, keys: $keys, modelo: $modelo,
                    not_in: $not_in);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
                }
            }
        }
        return $registros;
    }


    /**
     * Obtiene los registros para un select
     * @param array $columns_ds Columnas a integrar en option
     * @param array $extra_params_keys Datos a integrar para extra params
     * @param array $filtro Filtro de datos para filtro and
     * @param array $in
     * @param string $key_value_custom
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo del select
     * @param array $not_in Omite resultados para options
     * @return array
     * @fecha 2022-08-02 17:32
     * @fecha 2022-08-02 17:32
     * @author mgamboa
     */
    private function rows_select(array $columns_ds, array $extra_params_keys, array $filtro, array $in,
                                 string $key_value_custom, stdClass $keys, modelo $modelo, array $not_in): array
    {
        $keys_val = array('id','descripcion_select', 'descripcion');

        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val,registro:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys ',data:  $valida);
        }

        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;
        $columnas[] = $keys->descripcion;

        if($key_value_custom !== ''){
            $columnas[] = $key_value_custom;
        }


        foreach ($columns_ds as $column){
            /**
             * REFACTORIZAR
             */
            $column = trim($column);
            if($column === ''){
                return $this->error->error(mensaje: 'Error el column de extra params esta vacio',data:  $columns_ds);
            }
            $columnas[] = $column;
        }

        foreach ($extra_params_keys as $key){
            /**
             * REFACTORIZAR
             */
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error el key de extra params esta vacio',data:  $extra_params_keys);
            }
            $columnas[] = $key;
        }

        $filtro[$modelo->tabla.'.status'] = 'activo';
        $registros = $modelo->filtro_and(columnas: $columnas, filtro: $filtro, in: $in, not_in: $not_in);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }
        return $registros->registros;
    }

    private function value_select(stdClass $keys, array $registro, array $values){
        $keys_valida = array($keys->id,$keys->descripcion_select);
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
        }

        $values[$registro[$keys->id]] = $registro;
        $values[$registro[$keys->id]]['descripcion_select'] = $registro[$keys->descripcion_select];

        return $values;
    }

    private function value_select_row(bool $aplica_default, stdClass $keys, array $registro, string $tabla, array $values){
        $registro = $this->integra_descripcion_select(aplica_default: $aplica_default, keys: $keys, registro: $registro, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select',data:  $registro);
        }

        if($aplica_default) {
            $values = $this->value_select(keys: $keys, registro: $registro, values: $values);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integra values para select', data: $values);
            }
        }
        return $values;
    }

    private function values(bool $aplica_default, stdClass $keys, array $registros, string $tabla){
        $values = array();
        foreach ($registros as $registro){
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error registro debe ser un array',data:  $registro);
            }
            $values = $this->value_select_row(aplica_default: $aplica_default, keys: $keys, registro: $registro, tabla: $tabla, values: $values);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integra values para select',data:  $values);
            }
        }

        return $values;
    }

    /**
     * Genera los values para ser utilizados en los selects options
     * @param bool $aplica_default
     * @param array $columns_ds Columnas a integrar en option
     * @param bool $con_registros si con registros muestra todos los registros
     * @param array $extra_params_keys Keys para asignacion de extra params para ser utilizado en javascript
     * @param array $filtro Filtro para obtencion de datos del select
     * @param array $in
     * @param string $key_value_custom
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo para asignacion de datos
     * @param array $not_in Omite resultados para options
     * @param array $registros registros a mostrar en caso de que este vacio los obtiene de la entidad
     * @return array
     * @author mgamboa
     * @version 8.60.0
     */
    private function values_selects(bool $aplica_default, array $columns_ds, bool $con_registros, array $extra_params_keys, array $filtro,
                                    array $in, string $key_value_custom, stdClass $keys, modelo $modelo, array $not_in ,
                                    array $registros ): array
    {
        $keys_valida = array('id','descripcion_select','descripcion');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $registros = $this->registros_select(columns_ds: $columns_ds, con_registros: $con_registros,
            extra_params_keys: $extra_params_keys, filtro: $filtro, in: $in, key_value_custom: $key_value_custom,
            keys: $keys, modelo: $modelo, not_in: $not_in, registros: $registros);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }

        $values = $registros;
        if($aplica_default) {
            $values = $this->genera_values_selects(aplica_default: $aplica_default, keys: $keys, registros: $registros, tabla: $modelo->tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar valores', data: $values);
            }
        }

        return $values;
    }
}
