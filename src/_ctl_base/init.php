<?php
/**+
 * PRUEBAS COMPLETADAS
 * POR DOCUMENTAR
 */
namespace gamboamartin\system\_ctl_base;
use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

final class init{
    private errores $error;
    public function __construct()
    {
        $this->error = new errores();
    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Asigna valor a una clave en un arreglo de parametros.
     * @param stdClass $data_init Datos iniciales donde se busca la clave y su valor.
     * @param string $key Key de parametro a ser agregada al array `$params`.
     * @param array $params Parametros previos cargados.
     * @return array Retorna el array de parametros actualizado con la nueva clave y su valor.
     * @throws errores Si la clave proporcionada es invalida o no es encontrada en `$data_init`.
     *
     * @version 18.0.0
     */
    private function asigna_data_param(stdClass $data_init, string $key, array $params): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }
        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $data_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data_init', data: $valida);
        }
        $params[$key] = $data_init->$key;
        return $params;
    }

    /**
     * Asigna los parametros base de un boton
     * @param stdClass $data_init Datos default inicializados
     * @param array $keys_params Key de parametros a integrar
     * @param array $params Parametros previamente cargados
     * @return array
     */
    private function asigna_datas_param(stdClass $data_init, array $keys_params, array $params): array
    {
        foreach ($keys_params as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
            }
            $keys = array($key);
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $data_init);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar data_init', data: $valida);
            }

            $params = $this->asigna_data_param(data_init: $data_init,key:  $key,params:  $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar param', data: $params);
            }
        }
        return $params;
    }

    /**
     * Inicializa los parametros por get
     * @param controler $controler Controlador en ejecucion
     * @return array|stdClass
     */
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

    /** Inicializa los parametros provenientes por get para template base
     * @param string $compare Variable GET a verificar
     * @param stdClass $data_init datos previamente inicializados
     * @param string $key Key de GET
     * @return array|stdClass
     */
    private function init_data_param_get(string $compare, stdClass $data_init, string $key): array|stdClass
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }
        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys:$keys,registro:  $_GET);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar GET', data: $valida);
        }
        $compare = trim($compare);
        if($compare !== ''){
            $data_init = $this->init_param_get(data_init: $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método que inicializa datos de retorno.
     *
     * Este método recibe un objeto de controlador y realiza las siguientes operaciones:
     * 1. Limpia los valores de la tabla y la acción del controlador.
     * 2. Comprueba si los valores de la tabla y la acción están vacíos. Si están vacíos, llama al método de error y
     *      pasa un mensaje de error y los datos.
     * 3. Inicializa las variables next_seccion, next_accion e id_retorno con los valores de la tabla, la acción
     *      y el registro_id del controlador respectivamente.
     * 4. Crea un nuevo objeto stdClass y le asigna las variables next_seccion, next_accion e id_retorno.
     * 5. Retorna el objeto.
     *
     * @param controler $controler El objeto de controlador.
     * @return stdClass|array Si no hay errores, retorna un objeto stdClass con la sección siguiente,
     *  la acción siguiente y el id de retorno. En caso de errores, se retorna un array.
     *
     * @version 17.0.0
     */
    private function init_data_retornos(controler $controler): stdClass|array
    {
        $controler->tabla = trim($controler->tabla);
        if($controler->tabla === ''){
            return $this->error->error(mensaje: 'Error $controler->tabla esta vacio', data: $controler->tabla);
        }
        $controler->accion = trim($controler->accion);
        if($controler->accion === ''){
            return $this->error->error(mensaje: 'Error $controler->accion esta vacio', data: $controler->accion);
        }
        $next_seccion = $controler->tabla;
        $next_accion = $controler->accion;
        $id_retorno = $controler->registro_id;

        $data = new stdClass();
        $data->next_seccion = $next_seccion;
        $data->next_accion = $next_accion;
        $data->id_retorno = $id_retorno;

        return $data;

    }

    /**
     * Inicializa los parametros de retorno por GET
     * @param stdClass $data_init Datos previos cargados
     * @param string $key Key a integrar
     * @return array|stdClass
     */
    private function init_get_param(stdClass $data_init, string $key): array|stdClass
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key no puede venir vacio', data: $key);
        }
        if(isset($_GET[$key])){
            $compare = trim($_GET[$key]);
            $data_init = $this->init_data_param_get(compare: $compare,data_init:  $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }

        }
        return $data_init;
    }

    /**
     * Inicializa los keys de un GET de retornos
     * @param stdClass $data_init Datos precargados
     * @param array $keys_init Keys  inicializar
     * @return array|stdClass
     */
    private function init_keys_get(stdClass $data_init, array $keys_init): array|stdClass
    {
        foreach ($keys_init as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error key no puede venir vacio', data: $key);
            }

            $data_init = $this->init_get_param(data_init: $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }

    /**
     * Inicializa los parametros para envio por GET
     * @param stdClass $data_init Datos a inicializar
     * @return array|stdClass
     */
    private function init_keys_get_data(stdClass $data_init): array|stdClass
    {
        $keys_init = array('next_seccion','next_accion','id_retorno');

        $data_init = $this->init_keys_get(data_init: $data_init,keys_init:  $keys_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }
        return $data_init;
    }

    /**
     * Inicializa un objeto conforme el key recibido en GET
     * @param stdClass $data_init Datos de controller para view
     * @param string $key Key a verificar e integrar
     * @return stdClass|array
     */
    private function init_param_get(stdClass $data_init, string $key): stdClass|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }

        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys:$keys,registro:  $_GET);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar GET', data: $valida);
        }

        $data_init->$key = $_GET[$key];
        return $data_init;
    }

    /**
     * Asigna parametros para enviar por GET
     * @param stdClass $data_init Datos previos cargados
     * @param array $params Parametros precargados
     * @return array
     */
    private function init_params(stdClass $data_init, array $params): array
    {
        $keys_params = array('next_seccion','next_accion','id_retorno');

        $params = $this->asigna_datas_param(data_init: $data_init,keys_params:  $keys_params,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar param', data: $params);
        }
        return $params;
    }

    /**
     * Integra parametros para enviar por GET
     * @param controler $controler Controlador en ejecucion
     * @param array $params Parametros GET previos cargados
     * @return array
     */
    final public function params(controler $controler, array $params): array
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
