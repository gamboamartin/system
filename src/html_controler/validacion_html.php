<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;

use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use stdClass;


class validacion_html extends validacion{
    protected errores $error;
    protected validacion $validacion;


    /**
     * Valida los elementos basicos de un input para template
     * @param mixed $params_select Parametros de html
     * @return bool|array
     * @version 0.294.39
     */
    private function valida_base_html(mixed $params_select): bool|array
    {
        $es_param_valido = false;
        if(is_array($params_select)){
            $es_param_valido = true;
        }
        if(is_object($params_select)){
            $es_param_valido = true;
        }
        if(!$es_param_valido){
            return $this->error->error(
                mensaje: 'Error params_select debe ser un array u objeto', data: $es_param_valido);
        }
        $keys = array('cols','disabled','name','place_holder','value_vacio');
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }
        return true;
    }

    public function valida_boton_link(array $accion_permitida, int $indice, int $registro_id, array $rows): bool|array
    {
        $keys = array('adm_accion_descripcion','adm_accion_titulo','adm_seccion_descripcion','adm_accion_css',
            'adm_accion_es_status');
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }

        $keys = array('adm_accion_es_status');
        $valida = $this->valida_statuses(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }

        if($indice < 0){
            return $this->error->error(mensaje: 'Error indice debe ser mayor o igual a 0',data:  $indice);
        }

        if($registro_id <= 0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data:  $registro_id);
        }
        if(!isset($rows[$indice])){
            return $this->error->error(mensaje: 'Error no existe el registro en proceso',data:  $rows);
        }
        return true;
    }

    private function valida_input(mixed $params_select): bool|array
    {
        $valida = $this->valida_base_html(params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = $this->valida_numerics(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('disabled','value_vacio');
        $valida = $this->valida_bools(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        return true;
    }

    public function valida_input_base(directivas $directivas, mixed $params_select): bool|array
    {
        $valida = $this->valida_input(params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }


        $valida = $directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        return true;
    }

    public function valida_params(directivas $directivas, stdClass $params_select): bool|array
    {
        $keys = array('cols','disabled','name','place_holder','required','value_vacio');
        $valida = $this->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = $this->valida_numerics(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $valida = $directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $keys = array('disabled','required','value_vacio');
        $valida = $this->valida_bools(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        return true;
    }



}
