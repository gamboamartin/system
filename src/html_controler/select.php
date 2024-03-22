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

    private function data_keys(array $columns_ds, string $key_descripcion, string $key_descripcion_select): stdClass
    {
        $data = new stdClass();
        $data->key_descripcion = $key_descripcion;
        $data->key_descripcion_select = $key_descripcion_select;
        if (count($columns_ds) > 0){
            $data->key_descripcion = $columns_ds[0];
            $data->key_descripcion_select = $columns_ds[0];
        }
        return $data;

    }

    private function genera_data_keys(array $columns_ds, string $key_descripcion, string $key_descripcion_select, string $tabla)
    {
        $key_descripcion_select = $this->key_descripcion_select(key_descripcion_select: $key_descripcion_select,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar key_descripcion_select',data:  $key_descripcion_select);
        }

        $key_descripcion = $this->key_descripcion(key_descripcion: $key_descripcion,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar key_descripcion',data:  $key_descripcion);
        }

        $data_keys = $this->data_keys(columns_ds: $columns_ds,key_descripcion: $key_descripcion,key_descripcion_select:  $key_descripcion_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_keys',data:  $data_keys);
        }

        return $data_keys;

    }

    /**
     * Asigna los values de un select
     * @refactorizar Refactorizar
     * @param stdClass $keys Keys para asignacion basica
     * @param array $registros Conjunto de registros a integrar
     * @param string $tabla Tabla del modelo en ejecucion
     * @return array
     * @version 0.48.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-02 18:12
     * @author mgamboa
     */
    private function genera_values_selects(stdClass $keys, array $registros, string $tabla): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $values = $this->values(keys: $keys,registros:  $registros,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra values para select',data:  $values);
        }
        return $values;
    }

    /**
     * Inicializa los datos de un select
     * @param bool $con_registros Si no con registros integra el select vacio para ser llenado posterior con ajax
     * @param modelo $modelo Modelo en ejecucion para la asignacion de datos
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
    final public function init_data_select(bool $con_registros, modelo $modelo, array $columns_ds = array(),
                                           array $extra_params_keys = array(), array $filtro = array(),
                                           string $key_descripcion = '', string $key_descripcion_select= '',
                                           string $key_id = '', string $key_value_custom = '', string $label = '',
                                           string $name = '', array $not_in = array(), array $in = array(),
                                           array $registros = array()): array|stdClass
    {

        $keys = $this->keys_base(tabla: $modelo->tabla, key_descripcion: $key_descripcion,
            key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, name: $name,columns_ds: $columns_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(columns_ds: $columns_ds, con_registros: $con_registros,
            extra_params_keys: $extra_params_keys, filtro: $filtro, in: $in, key_value_custom: $key_value_custom,
            keys: $keys, modelo: $modelo, not_in: $not_in, registros: $registros);
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

    private function key_descripcion(string $key_descripcion, string $tabla): string
    {
        $key_descripcion = trim($key_descripcion);
        if($key_descripcion === ''){
            $key_descripcion = $tabla.'_descripcion';
        }
        return $key_descripcion;

    }


    private function integra_descripcion_select(stdClass $keys, array $registro, string $tabla){
        $key_descripcion = $tabla.'_descripcion';
        if(!isset($registro[$keys->descripcion_select])){
            $registro = $this->key_descripcion_select_default(key_descripcion: $key_descripcion, keys: $keys, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar descripcion_select',data:  $registro);
            }
        }
        return $registro;
    }

    private function key_descripcion_select(string $key_descripcion_select, string $tabla): string
    {
        if($key_descripcion_select === '') {
            $key_descripcion_select = $tabla.'_descripcion_select';
        }
        return $key_descripcion_select;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Función key_id
     *
     * Esta función se encarga de devolver la clave única para un registro de una tabla específica en la base de datos.
     * Se le proporciona inicialmente una clave y el nombre de una tabla.
     * Primero, verifica que el nombre de la tabla no esté vacío. Si está vacío, devuelve un error.
     * Luego, verifica si se le proporcionó una clave. Si no se proporcionó ninguna clave, la genera automáticamente
     * agregando el sufijo '_id' al nombre de la tabla.
     *
     * @param string $key_id La clave única proporcionada. Si está vacía, la función genera una automáticamente.
     * @param string $tabla El nombre de la tabla en cuestión
     * @return string|array Devuelve la clave única, o un error si el nombre de la tabla está vacío
     * @version 18.19.0
     */
    private function key_id(string $key_id, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        if($key_id === '') {
            $key_id = $tabla . '_id';
        }
        return $key_id;

    }


    /**
     * Asigna los keys necesarios para un select
     * @param string $tabla Tabla del select
     * @param string $key_descripcion_select base de descripcion
     * @param string $key_id identificador key
     * @param string $name Name del input
     * @return stdClass|array obj->id, obj->descripcion_select
     */
    private function keys_base(string $tabla, string $key_descripcion = '', string $key_descripcion_select = '',
                               string $key_id = '', string $name = '', array $columns_ds = array()): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }

        $key_id = $this->key_id(key_id: $key_id,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar key_id',data:  $key_id);
        }

        $name = $this->name(key_id: $key_id,name:  $name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar name',data:  $name);
        }


        $data_keys = $this->genera_data_keys(
            columns_ds: $columns_ds,key_descripcion: $key_descripcion,key_descripcion_select:  $key_descripcion_select,
            tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_keys',data:  $data_keys);
        }

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
        $keys_val_row = array($keys->id,$key_descripcion);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val_row, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
        }

        $registro[$keys->descripcion_select] = $registro[$keys->id].' '.$registro[$key_descripcion];
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
     * POR DOCUMENTAR EN WIKI
     * Este método se encarga de asignar un nombre a la clave dada.
     *
     * @param string $key_id La clave a la que se asignará el nombre.
     * @param string $name El nombre que se asignará a la clave.
     * @return string El nombre que se ha asignado a la clave. Si el nombre proporcionado estaba vacío, se devolverá la misma clave.
     * @version 19.3.0
     */
    private function name(string $key_id, string $name): string
    {
        $name = trim($name);
        if($name === ''){
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

    private function value_select_row(stdClass $keys, array $registro, string $tabla, array $values){
        $registro = $this->integra_descripcion_select(keys: $keys,registro:  $registro, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select',data:  $registro);
        }

        $values = $this->value_select(keys: $keys,registro:  $registro,values:  $values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra values para select',data:  $values);
        }
        return $values;
    }

    private function values(stdClass $keys, array $registros, string $tabla){
        $values = array();
        foreach ($registros as $registro){
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error registro debe ser un array',data:  $registro);
            }
            $values = $this->value_select_row(keys: $keys,registro:  $registro, tabla: $tabla,values:  $values);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integra values para select',data:  $values);
            }
        }

        return $values;
    }

    /**
     * Genera los values para ser utilizados en los selects options
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
    private function values_selects(array $columns_ds, bool $con_registros, array $extra_params_keys, array $filtro,
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

        $values = $this->genera_values_selects(keys: $keys,registros: $registros, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valores',data:  $values);
        }

        return $values;
    }
}
