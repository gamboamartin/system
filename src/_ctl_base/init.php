<?php
namespace gamboamartin\system\_ctl_base;
use base\controller\controler;
use gamboamartin\errores\errores;
use stdClass;

class init{
    private errores $error;
    public function __construct()
    {
        $this->error = new errores();
    }


    private function asigna_data_param(stdClass $data_init, string $key, array $params): array
    {
        $params[$key] = $data_init->$key;
        return $params;
    }

    private function asigna_datas_param(stdClass $data_init, array $keys_params, array $params): array
    {
        foreach ($keys_params as $key){
            $params = $this->asigna_data_param(data_init: $data_init,key:  $key,params:  $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar param', data: $params);
            }
        }
        return $params;
    }

    private function data_init(controler $controler): array|stdClass
    {
        $data_init = $this->init_data_retornos(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar', data: $data_init);
        }

        $data_init = $this->init_keys_get_data(data_init: $data_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }
        return $data_init;
    }

    private function init_data_param_get(string $compare, stdClass $data_init, string $key): array|stdClass
    {

        if($compare !== ''){
            $data_init = $this->init_param_get(data_init: $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }

    /**
     * Inicializa loe elementos para un retorno
     * @param controler $controler Controlador en ejecucion
     * @return stdClass
     * @version 0.97.4
     */
    private function init_data_retornos(controler $controler): stdClass
    {
        $next_seccion = $controler->tabla;
        $next_accion = $controler->accion;
        $id_retorno = $controler->registro_id;

        $data = new stdClass();
        $data->next_seccion = $next_seccion;
        $data->next_accion = $next_accion;
        $data->id_retorno = $id_retorno;

        return $data;

    }

    private function init_get_param(stdClass $data_init, string $key): array|stdClass
    {
        if(isset($_GET[$key])){
            $compare = trim($_GET[$key]);
            $data_init = $this->init_data_param_get(compare: $compare,data_init:  $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }

        }
        return $data_init;
    }

    private function init_keys_get(stdClass $data_init, array $keys_init): array|stdClass
    {
        foreach ($keys_init as $key){
            $data_init = $this->init_get_param(data_init: $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }

    private function init_keys_get_data(stdClass $data_init): array|stdClass
    {
        $keys_init = array('next_seccion','next_accion','id_retorno');

        $data_init = $this->init_keys_get(data_init: $data_init,keys_init:  $keys_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }
        return $data_init;
    }

    private function init_param_get(stdClass $data_init, string $key): stdClass
    {
        $data_init->$key = $_GET[$key];
        return $data_init;
    }

    private function init_params(stdClass $data_init, array $params): array
    {
        $keys_params = array('next_seccion','next_accion','id_retorno');

        $params = $this->asigna_datas_param(data_init: $data_init,keys_params:  $keys_params,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar param', data: $params);
        }
        return $params;
    }

    public function params(controler $controler, array $params): array
    {
        $data_init = $this->data_init(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }

        $params = $this->init_params(data_init: $data_init,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar param', data: $params);
        }
        return $params;
    }
}
