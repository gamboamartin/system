<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\system\datatables;
use gamboamartin\validacion\validacion;
use PDO;


class acciones{
    private errores $error;
    private validacion $valida;

    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();
    }

    /**
     * Asigna la primer accion de un datatable
     * @param array $acciones_grupo Conjunto de permisos
     * @return string|array
     * @version 0.154.33
     */
    private function accion_base(array $acciones_grupo): string|array
    {
        $adm_accion_base = '';
        foreach ($acciones_grupo as $adm_accion_grupo){
            if(!is_array($adm_accion_grupo)){
                return $this->error->error(mensaje: 'Error adm_accion_grupo debe ser un array', data: $adm_accion_grupo);
            }
            $keys = array('adm_accion_descripcion');
            $valida = $this->valida->valida_existencia_keys(keys:$keys,registro:  $adm_accion_grupo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar accion', data: $valida);
            }
            $adm_accion_base = $adm_accion_grupo['adm_accion_descripcion'];
            break;
        }
        return $adm_accion_base;
    }

    /**
     * Genera los datos para datatables
     * @param array $columns columnas de tipo controller
     * @param PDO $link Conexion a la base de datos
     * @param string $seccion Seccion en ejecucion
     * @param array $not_actions Acciones para exclusion
     * @return array
     * @version 0.226.37
     */
    final public function acciones_columnas(array $columns, PDO $link, string $seccion, array $not_actions = array()): array
    {

        $valida = (new validacion_dt())->valida_data_column(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $acciones_grupo = (new datatables())->acciones_permitidas(link: $link,seccion: $seccion, not_actions: $not_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones', data: $acciones_grupo);
        }

        $adm_accion_base = $this->accion_base(acciones_grupo: $acciones_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener accion base', data: $adm_accion_base);
        }

        $columns = $this->maqueta_accion_base_column(
            acciones_grupo: $acciones_grupo,adm_accion_base:  $adm_accion_base,columns:  $columns);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar accion base', data: $columns);

        }

        $columns = $this->columnas_accion(
            acciones_grupo: $acciones_grupo,adm_accion_base:  $adm_accion_base,columns:  $columns);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar acciones ', data: $columns);

        }
        return $columns;
    }

    /**
     * Integra las columnas para datatables
     * @param array $acciones_grupo Acciones
     * @param string $adm_accion_base Accion
     * @param array $columns Columnas datatables
     * @return array
     * @version 0.224.37
     */
    private function columnas_accion(array $acciones_grupo, string $adm_accion_base, array $columns): array
    {
        $i = 0;
        foreach ($acciones_grupo as $adm_accion_grupo){
            if(!is_array($adm_accion_grupo)){
                return $this->error->error(
                    mensaje: 'Error adm_accion_grupo debe ser un array', data: $adm_accion_grupo);
            }

            $keys = array('adm_accion_descripcion');
            $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $adm_accion_grupo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar adm_accion_grupo ', data: $valida);
            }

            $columns = $this->genera_accion(
                adm_accion_base: $adm_accion_base,adm_accion_grupo:  $adm_accion_grupo,columns:  $columns,i:  $i);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar accion ', data: $columns);
            }
            $i++;
        }
        return $columns;
    }

    /**
     * Integra una accion para columnas datatable
     * @param string $adm_accion_base Accion a integrar
     * @param array $adm_accion_grupo Permiso
     * @param array $columns Columnas para datatables
     * @param int $i Indice de registros
     * @return array
     * @version 0.221.37
     */
    private function genera_accion(string $adm_accion_base, array $adm_accion_grupo, array $columns, int $i): array
    {
        $keys = array('adm_accion_descripcion');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo ', data: $valida);
        }
        if($i<0){
            return $this->error->error(mensaje: 'Error i debe ser mayor o igual a 0 ', data: $i);
        }

        $adm_accion = $adm_accion_grupo['adm_accion_descripcion'];
        if($i > 0){

            $adm_accion_base = trim($adm_accion_base);
            if($adm_accion_base === ''){
                return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
            }
            $adm_accion = trim($adm_accion);
            if($adm_accion === ''){
                return $this->error->error(mensaje: 'Error adm_accion esta vacia', data:  $adm_accion);
            }

            $columns = $this->integra_accion(adm_accion: $adm_accion,adm_accion_base:  $adm_accion_base,columns:  $columns);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar accion ', data: $columns);
            }

        }
        return $columns;
    }

    /**
     * Integra una columna de accion a datatable
     * @param string $adm_accion Accion
     * @param string $adm_accion_base Accion base
     * @param array $columns Columnas de datatable
     * @return array
     * @version 0.198.36
     */
    private function integra_accion(string $adm_accion, string $adm_accion_base, array $columns): array
    {
        $adm_accion_base = trim($adm_accion_base);
        if($adm_accion_base === ''){
            return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
        }
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia', data:  $adm_accion);
        }
        $columns[$adm_accion_base]['campos'][] = $adm_accion;
        return $columns;
    }

    /**
     * Maqueta los elementos para un row
     * @param array $acciones_grupo Acciones permitidas
     * @param string $adm_accion_base accion
     * @param array $columns Columnas precargadas
     * @return array
     * @version 0.170.34
     */
    private function maqueta_accion_base_column(array $acciones_grupo, string $adm_accion_base, array $columns): array
    {

        if(count($acciones_grupo) > 0){
            $adm_accion_base = trim($adm_accion_base);
            if($adm_accion_base === ''){
                return $this->error->error(mensaje: 'Error adm_accion_base esta vacia', data:  $adm_accion_base);
            }
            $columns[$adm_accion_base]['titulo'] = 'Acciones';
            $columns[$adm_accion_base]['type'] = 'button';
            $columns[$adm_accion_base]['campos'] = array();
        }

        return $columns;
    }

}
