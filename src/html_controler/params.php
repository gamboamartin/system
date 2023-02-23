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
     * Carga los elementos de un icono
     * @param array $adm_accion Accion a integrar
     * @return stdClass|array
     * @version 2.8.0
     */
    final public function data_icon(array $adm_accion): stdClass|array
    {
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $adm_accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion', data: $valida);
        }

        $valida = $this->validacion->valida_statuses(keys: $keys, registro: $adm_accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion', data: $valida);
        }

        $muestra_icono_btn = false;
        if($adm_accion['adm_accion_muestra_icono_btn'] === 'activo'){
            $muestra_icono_btn = true;
        }

        $muestra_titulo_btn = false;
        if($adm_accion['adm_accion_muestra_titulo_btn'] === 'activo'){
            $muestra_titulo_btn = true;
        }
        $data = new stdClass();
        $data->muestra_icono_btn = $muestra_icono_btn;
        $data->muestra_titulo_btn = $muestra_titulo_btn;
        return $data;
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
        $name = trim($name);
        if(is_numeric($name)){
            return $this->error->error(mensaje: 'Error name debe ser un string no un numero', data: $name);
        }
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

        $name = trim($name);
        if(is_numeric($name)){
            return $this->error->error(mensaje: 'Error name debe ser un string no un numero', data: $name);
        }

        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->place_holder = $params->place_holder ?? $place_holder;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($place_holder));
        $data->required = $params->required ?? true;

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
        $data->key_descripcion_select = $params->key_descripcion_select ?? '';

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
    final public function params_select_init(string $item, array $keys_selects): array|stdClass
    {

        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }

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


}
