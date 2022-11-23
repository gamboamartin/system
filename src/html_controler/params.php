<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use stdClass;

class params{
    protected errores $error;
    protected validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Inicializa los parametros para un input
     * @param stdClass $data Data precargado
     * @param string $name Nombre del input
     * @param stdClass $params Parametros
     * @return stdClass|array
     * @version 0.185.34
     */
    private function params_base(stdClass $data, string $name, stdClass $params): stdClass|array
    {
        $data->disabled = $params->disabled ?? false;
        $data->con_registros = $params->con_registros ?? true;
        $data->id_selected = $params->id_selected ?? -1;
        $data->required = $params->required ?? true;
        $data->row_upd = $params->row_upd ?? new stdClass();
        $data->value_vacio = $params->value_vacio ?? false;
        $data->filtro = $params->filtro ?? array();
        $data->not_in = $params->not_in ?? array();
        $data->name = $params->name ?? $name;

        $data->extra_params_keys = array();
        if(isset($params->extra_params_keys) ){
            $data->extra_params_keys = $params->extra_params_keys;
        }


        return $data;
    }

    /**
     * Inicializa los parametros para un input
     * @param stdClass $params Parametros precargados
     * @param string $name Name input
     * @param string $place_holder Label del input
     * @return stdClass|array
     * @version 0.228.37
     */
    private function params_input2(stdClass $params, string $name,string $place_holder): stdClass|array
    {

        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->place_holder = $params->place_holder ?? $place_holder;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($place_holder));

        $data = $this->params_base(data: $data, name: $name ,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Inicializa los parametros de un select
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros inicializados
     * @return stdClass|array
     * @version 0.95.32
     */
    public function params_select(string $name_model, stdClass $params): stdClass|array
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }
        $data = new stdClass();

        $data->cols = $params->cols ?? 12;
        $data->place_holder = $params->place_holder ?? $name_model;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($name_model));

        $data = $this->params_base(data: $data, name : $name_model,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Ajusta los parametros
     * @param stdClass $params Parametros precargados
     * @param string $label Etiqueta a mostrar en input
     * @return stdClass|array
     * @version 0.212.37
     */
    public function params_select_col_6(stdClass $params, string $label): stdClass|array
    {
        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error label esta vacio', data: $label);
        }
        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->label = $params->label ?? str_replace('_',' ', $label);

        $data->label = str_replace('  ', ' ', $data->label);

        $data = $this->params_base(data: $data,name:  $label,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }


        return $data;
    }

    /**
     * Integra los parametros para inputs
     * @param string $item Row
     * @param array $keys_selects keys con datos de inputs
     * @return array|stdClass
     * @version 0.245.37
     */
    public function params_select_init(string $item, array $keys_selects): array|stdClass
    {

        $params_select = new stdClass();
        if (array_key_exists($item, $keys_selects) ){
            $params_select = $keys_selects[$item];
        }
        $params_select = $this->params_input2(params: $params_select,name: $item,place_holder: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }

        return $params_select;
    }

    public function valida_params(directivas $directivas, stdClass $params_select): bool|array
    {
        $keys = array('cols','disabled','name','place_holder','required','value_vacio');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = $this->validacion->valida_numerics(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $valida = $directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $keys = array('disabled','required','value_vacio');
        $valida = $this->validacion->valida_bools(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        return true;
    }
}
