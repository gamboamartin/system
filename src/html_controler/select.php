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
        $values = array();
        foreach ($registros as $registro){
            /**
             * REFACTORIZAR
             */
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error registro debe ser un array',data:  $registro);
            }

            $key_descripcion_select = $tabla.'_descripcion_select';
            $key_id = $tabla.'_id';
            $key_descripcion = $tabla.'_descripcion';
            if(!isset($registro[$keys->descripcion_select])){

                $keys_val_row = array($key_id,$key_descripcion);
                $valida = $this->validacion->valida_existencia_keys(keys: $keys_val_row, registro: $registro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
                }

                $registro[$key_descripcion_select] = $registro[$key_id].' '.$registro[$key_descripcion];
            }

            $keys_valida = array($keys->id,$keys->descripcion_select);
            $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
            }

            $values[$registro[$keys->id]] = $registro;
            $values[$registro[$keys->id]]['descripcion_select'] = $registro[$keys->descripcion_select];
        }
        return $values;
    }

    /**
     * Inicializa los datos de un select
     * @refactorizar Refactorizar metodo
     * @param bool $con_registros Si no con registros integra el select vacio para ser llenado posterior con ajax
     * @param modelo $modelo Modelo en ejecucion para la asignacion de datos
     * @param array $extra_params_keys Keys de extra params para ser cargados en un select
     * @param array $filtro Filtro para obtencion de datos para options
     * @param string $key_descripcion_select key del registro para mostrar en un select
     * @param string $key_id key Id de value para option
     * @param string $label Etiqueta a mostrar
     * @param string $name Nombre del input
     * @param array $not_in Omite resultado de options
     * @return array|stdClass
     * @version 0.52.32
     * @version 0.55.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:55
     * @author mgamboa
     */
    public function init_data_select(bool $con_registros, modelo $modelo, array $extra_params_keys = array(),
                                      array $filtro = array(), string $key_descripcion_select= '', string $key_id = '',
                                      string $label = '', string $name = '', array $not_in = array()): array|stdClass
    {

        $keys = $this->keys_base(tabla: $modelo->tabla, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, name: $name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(con_registros: $con_registros, keys: $keys,modelo: $modelo,
            extra_params_keys: $extra_params_keys, filtro: $filtro, not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valores',data:  $values);
        }

        /**
         * REFACTORIZAR
         */
        $label_ =$label;
        if($label_ === '') {
            $label_ = $this->label(tabla: $modelo->tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener label', data: $label_);
            }
        }

        $keys->values = $values;
        $keys->label = $label_;
        return $keys;
    }



    /**
     * Asigna los keys necesarios para un select
     * @param string $tabla Tabla del select
     * @param string $key_descripcion_select base de descripcion
     * @param string $key_id identificador key
     * @param string $name Name del input
     * @return stdClass|array obj->id, obj->descripcion_select
     * @version 0.2.5
     * @verfuncion 0.2.0 Se carga name
     */
    private function keys_base(string $tabla, string $key_descripcion_select = '', string $key_id = '',
                               string $name = ''): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        if($key_id === '') {
            $key_id = $tabla . '_id';
        }
        $name = trim($name);
        if($name === ''){
            $name = $key_id;
        }
        if($key_descripcion_select === '') {
            $key_descripcion_select = $tabla.'_descripcion_select';
        }


        $data = new stdClass();
        $data->id = $key_id;
        $data->descripcion_select = $key_descripcion_select;
        $data->name = $name;

        return $data;
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
     * Obtiene los registros para un select
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo del select
     * @param array $extra_params_keys Datos a integrar para extra params
     * @param array $filtro Filtro de datos para filtro and
     * @param array $not_in Omite resultados para options
     * @return array
     * @version 0.47.32
     * @version 0.53.32
     * @verfuncion 0.1.0 UT fin
     * @verfuncion 0.2.0 Se integra param filtro
     * @fecha 2022-08-02 17:32
     * @fecha 2022-08-02 17:32
     * @author mgamboa
     */
    private function rows_select(stdClass $keys, modelo $modelo, array $extra_params_keys = array(),
                                 array $filtro = array(), array $not_in = array()): array
    {
        $keys_val = array('id','descripcion_select');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys_val,registro:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;

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
        $registros = $modelo->filtro_and(columnas: $columnas, filtro: $filtro, not_in: $not_in);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }
        return $registros->registros;
    }

    /**
     * Genera los values para ser utilizados en los selects options
     * @param bool $con_registros si con registros muestra todos los registros
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo para asignacion de datos
     * @param array $extra_params_keys Keys para asignacion de extra params para ser utilizado en javascript
     * @param array $filtro Filtro para obtencion de datos del select
     * @param array $not_in Omite resultados para options
     * @return array
     * @version 0.49.31
     * @version 0.54.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:04
     * @fecha 2022-08-03 14:50
     * @author mgamboa
     */
    private function values_selects( bool $con_registros, stdClass $keys, modelo $modelo,
                                     array $extra_params_keys = array(), array $filtro = array(),
                                     array $not_in = array()): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $registros = array();
        if($con_registros) {
            $registros = $this->rows_select(keys: $keys, modelo: $modelo, extra_params_keys: $extra_params_keys,
                filtro:$filtro, not_in: $not_in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
            }
        }

        $values = $this->genera_values_selects(keys: $keys,registros: $registros, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valores',data:  $values);
        }

        return $values;
    }
}
