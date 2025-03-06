<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;

class filtros{
    private errores $error;


    public function __construct(){
        $this->error = new errores();
   
    }

    public function filtro()
    {

        $filtro = array();
        if (isset($_GET['data'])) {
            $filtro = $_GET['data'];
        }

        if (isset($_GET['filtros_avanzados']) && isset($_GET['filtros_avanzados']['filtro'])) {
            $filtro = array_merge($filtro, $_GET['filtros_avanzados']['filtro']);
        }

        return $filtro;
    }

    public function filtro_rango()
    {
        $filtro = array();

        if (isset($_GET['filtros_avanzados']) && isset($_GET['filtros_avanzados']['rango-fechas'])) {
            $campo = $_GET['filtros_avanzados']['rango-fechas']['filtro_tabla'];

            $fecha_inicio = $_GET['filtros_avanzados']['rango-fechas']['campo1'] ?? '2000-01-01';
            $fecha_fin = $_GET['filtros_avanzados']['rango-fechas']['campo2'] ?? date('Y-m-d');

            $filtro[$campo] = ['valor1' => $fecha_inicio, 'valor2' => $fecha_fin];
        }

        return $filtro;
    }

    /**
     * REG
     * Genera un arreglo `$filtro` que permite filtrar las acciones permitidas para un grupo específico en una sección dada.
     *
     * Pasos clave:
     *  1. Verifica que `$_SESSION['grupo_id']` exista.
     *     - Si no existe, se retorna un arreglo de error.
     *  2. Valida que `$seccion` no sea una cadena vacía.
     *     - Si está vacía, se retorna un arreglo de error.
     *  3. Construye el arreglo `$filtro` con los siguientes criterios:
     *     - 'adm_grupo.id'              => ID del grupo tomado de `$_SESSION['grupo_id']`
     *     - 'adm_seccion.descripcion'   => El nombre de la sección (cadena `$seccion`)
     *     - 'adm_accion.es_lista'       => 'activo'
     *     - 'adm_accion.status'         => 'activo'
     *     - 'adm_grupo.status'          => 'activo'
     *  4. Retorna `$filtro` para su uso en consultas o validaciones posteriores.
     *
     * @param string $seccion Sección que se desea filtrar. No debe estar vacía.
     *
     * @return array Un arreglo de filtro con la información necesaria para restringir acciones y grupos activos.
     *               En caso de error (falta de grupo_id o sección vacía), se retorna el arreglo de error
     *               generado por `$this->error->error()`.
     *
     * @example
     *  Ejemplo 1: Uso exitoso con datos de sesión
     *  ----------------------------------------------------------------------------
     *  $_SESSION['grupo_id'] = 2;
     *  $seccion = "facturacion";
     *
     *  $filtro = $this->filtro_accion_permitida($seccion);
     *  // Retorna un arreglo similar a:
     *  // [
     *  //   'adm_grupo.id'             => 2,
     *  //   'adm_seccion.descripcion'  => 'facturacion',
     *  //   'adm_accion.es_lista'      => 'activo',
     *  //   'adm_accion.status'        => 'activo',
     *  //   'adm_grupo.status'         => 'activo'
     *  // ]
     *
     * @example
     *  Ejemplo 2: Falta grupo_id en la sesión
     *  ----------------------------------------------------------------------------
     *  unset($_SESSION['grupo_id']);
     *  $seccion = "facturacion";
     *
     *  $filtro = $this->filtro_accion_permitida($seccion);
     *  // Se retornará un arreglo de error indicando:
     *  // "Error $_SESSION[grupo_id] debe existir"
     *
     * @example
     *  Ejemplo 3: Sección vacía
     *  ----------------------------------------------------------------------------
     *  $_SESSION['grupo_id'] = 2;
     *  $seccion = "";
     *
     *  $filtro = $this->filtro_accion_permitida($seccion);
     *  // Se retornará un arreglo de error indicando que la sección está vacía.
     */
    final public function filtro_accion_permitida(string $seccion): array
    {
        // 1. Verificar que $_SESSION['grupo_id'] exista
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error(
                mensaje: 'Error $_SESSION[grupo_id] debe existir',
                data: $seccion
            );
        }

        // 2. Validar que la sección no esté vacía
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(
                mensaje: 'Error seccion esta vacio',
                data: $seccion
            );
        }

        // 3. Construir el arreglo filtro
        $filtro = [];
        $filtro['adm_grupo.id']            = $_SESSION['grupo_id'];
        $filtro['adm_seccion.descripcion'] = $seccion;
        $filtro['adm_accion.es_lista']     = 'activo';
        $filtro['adm_accion.status']       = 'activo';
        $filtro['adm_grupo.status']        = 'activo';

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
