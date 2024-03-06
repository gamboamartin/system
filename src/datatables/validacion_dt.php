<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;


class validacion_dt extends validacion{

    public function valida_base(array|string $column, string $indice): bool|array
    {
        if(is_string($column)){
            $column = trim($column);
            if($column === ''){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        if(is_array($column)){
            if(count($column) === 0){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice no puede venir vacia', data:  $indice);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Valida los datos de una determinada columna.
     *
     * Esta función realiza varias validaciones:
     * - Verifica si una sesión está iniciada. Si no hay ninguna, genera un error.
     * - Valida ciertos campos (en este caso, 'grupo_id') en la sesión actual. Si hay errores, genera un error.
     * - Verifica si el parámetro $seccion está vacío. Si lo está, genera un error.
     *
     * @param string $seccion - El nombre de la sección a validar
     *
     * @return true|array - Retorna verdadero si todas las validaciones pasan exitosamente. Si alguna validación falla,
     * retorna un array con detalles del error.
     * @version 18.13.0
     */
    final public function valida_data_column(string $seccion): true|array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay SESSION iniciada', data: array());
        }
        $keys = array('grupo_id');
        $valida = $this->valida_ids(keys: $keys,registro:  $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION', data: $valida);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }
        return true;

    }

}
