<?php
namespace gamboamartin\system;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class row{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Integra el elemento a modificar en cambio de estatus
     * @param string $key Key a integrar
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id Registro en proceso
     * @return array
     * @version 0.182.34
     */
    public function integra_row_upd(string $key, modelo $modelo, int $registro_id): array
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error this->registro_id debe ser mayor a 0', data:  $registro_id);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data:  $key);
        }

        $registro = $modelo->registro(registro_id: $registro_id, columnas_en_bruto: true, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_accion',data:  $registro);
        }

        $row_upd = $this->row_upd_status(key: $key,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener row upd',data:  $row_upd);
        }
        return $row_upd;
    }

    /**
     * Integra el valor a modificar de tipo status
     * @param string $key Key del valor a ajustar
     * @param stdClass $registro Registro en proceso
     * @return array
     * @version 0.181.34
     */
    private function row_upd_status(string $key, stdClass $registro): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data:  $key);
        }
        $keys = array($key);
        $valida = $this->validacion->valida_statuses(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data:  $valida);
        }

        $row_upd[$key] = 'inactivo';
        if($registro->$key === 'inactivo'){
            $row_upd[$key] = 'activo';
        }
        return $row_upd;
    }




}
