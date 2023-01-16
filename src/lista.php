<?php
namespace gamboamartin\system;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class lista{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Integra las columnas para una view de lista
     * @param array $keys_row_lista Datos desde controller
     * @return array
     * @version 0.258.38
     */
    private function columnas_lista(array $keys_row_lista): array
    {
        $columnas = array();
        foreach ($keys_row_lista as $key_row_lista){
            $valida = $this->valida_key_rows_lista(key_row_lista: $key_row_lista);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
            }

            $columnas[] = $key_row_lista->campo;
        }
        return $columnas;
    }

    /**
     * Integra los registros para una lista
     * @param array $keys_row_lista Datos desde controller
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     * @version 0.258.38
     */
    private function rows_lista(array $keys_row_lista, modelo $modelo): array|stdClass
    {
        $columnas = $this->columnas_lista(keys_row_lista: $keys_row_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas para lista en '.$modelo->tabla,
                data:  $columnas);
        }

        $registros = $modelo->registros(columnas:$columnas,return_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros en '.$modelo->tabla, data:  $registros);
        }
        return $registros;
    }

    public function rows_view_lista(system $controler): array
    {
        $registros = $this->rows_lista(keys_row_lista: $controler->keys_row_lista, modelo: $controler->modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros en '.$controler->tabla, data:  $registros);
        }

        $registros_view = (new actions())->registros_view_actions(acciones: $controler->acciones, link: $controler->link,
            obj_link: $controler->obj_link,registros:  $registros, seccion:  $controler->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar link en '.$controler->tabla, data:  $registros_view);
        }
        return $registros_view;
    }

    /**
     * Valida que los keys rows lista sean validos
     * @param mixed $key_row_lista Key a validar
     * @return bool|array
     * @version 0.125.33
     */
    private function valida_key_rows_lista(mixed $key_row_lista): bool|array
    {
        if(!is_object($key_row_lista)){
            return $this->error->error(mensaje: 'Error el key_row_lista debe ser un objeto', data:  $key_row_lista);
        }
        $keys = array('campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $key_row_lista,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
        }

        return true;
    }



}
