<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;


class filtros{
    private errores $error;


    public function __construct(){
        $this->error = new errores();
   
    }

    public function filtro(){
        $filtro  = array();
        if (isset($_GET['data'])){
            $filtro = $_GET['data'];
        }
        return $filtro;
    }

    /**
     * Integra los filtros de una accion permitida
     * @param string $seccion Seccion en ejecucion
     * @return array
     */
    final public function filtro_accion_permitida(string $seccion): array
    {
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error(mensaje: 'Error $_SESSION[grupo_id] debe existir', data: $seccion);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacio', data: $seccion);
        }
        $filtro = array();
        $filtro['adm_grupo.id'] = $_SESSION['grupo_id'];
        $filtro['adm_seccion.descripcion'] = $seccion;
        $filtro['adm_accion.es_lista'] = 'activo';
        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_grupo.status'] = 'activo';
        return $filtro;
    }

    /**
     * Maqueta un filtro especial para datatables
     * @param array $filtro_especial Filtro precargado
     * @param int $indice Indice de column filtro
     * @param string $column Columna
     * @param string $str dato para filtrar
     * @return array
     * @version 0.155.33
     *
     */
    private function filtro_especial_datatable(array $filtro_especial, int $indice, string $column, string $str): array
    {
        $str = trim($str);
        if($str === ''){
            return $this->error->error(mensaje: 'Error str esta vacio', data: $str);
        }

        if($indice < 0 ){
            return $this->error->error(mensaje: 'Error indice debe ser mayor o igual a 0', data: $indice);
        }
        $column = trim($column);
        if($column === ''){
            return $this->error->error(mensaje: 'Error column esta vacio', data: $column);
        }
        $filtro_especial[$indice][$column]['operador'] = 'LIKE';
        $filtro_especial[$indice][$column]['valor'] = addslashes(trim("%$str%"));
        $filtro_especial[$indice][$column]['comparacion'] = "OR";

        return $filtro_especial;
    }

    /**
     * Genera el filtro especial para ser ejecutado en listas GET
     * @param array $datatable datos de datable
     * @param array $filtro_especial Filtro a integrar
     * @param string $str Datos para filtro
     * @return array
     */
    private function filtros_especiales_datatable(array $datatable, array $filtro_especial, string $str): array
    {
        foreach ($datatable["filtro"] as $indice=>$column) {

            $filtro_especial = $this->filtro_especial_datatable(
                filtro_especial: $filtro_especial,indice:  $indice, column: $column, str: $str);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
            }
        }
        return $filtro_especial;
    }

    /**
     * @param array $datatable
     * @return array
     */
    final public function genera_filtro_especial_datatable(array $datatable): array
    {
        $filtro_especial = array();
        if(isset($_GET['search']) && $_GET['search']['value'] !== '' ) {
            $str = $_GET['search']['value'];
            $filtro_especial = $this->filtros_especiales_datatable(
                datatable: $datatable, filtro_especial: $filtro_especial,str:  $str);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
            }
        }
        return $filtro_especial;
    }


}
