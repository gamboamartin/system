<?php
namespace gamboamartin\system;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class datatables{
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
    private function acciones_columnas(array $columns, PDO $link, string $seccion, array $not_actions = array()): array
    {

        $valida = $this->valida_data_column(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $acciones_grupo = $this->acciones_permitidas(link: $link,seccion: $seccion, not_actions: $not_actions);
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
     * Obtiene las acciones permitidas de un grupo de usuario
     * @param PDO $link Conexion a la base de datos
     * @param string $seccion Seccion de controlador
     * @param array $not_actions
     * @return array
     * @version 0.153.33
     */
    public function acciones_permitidas(PDO $link, string $seccion, array $not_actions = array()): array
    {
        $valida = $this->valida_data_column(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $filtro = $this->filtro_accion_permitida(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener not in', data: $filtro);
        }

        $not_in = $this->not_in_accion(not_actions: $not_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener not in', data: $not_in);
        }

        $r_accion_grupo = (new adm_accion_grupo($link))->filtro_and(filtro: $filtro, not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones', data: $r_accion_grupo);

        }

        return $r_accion_grupo->registros;
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

    private function column_datable_init(stdClass $datatables, array $rows_lista, string $seccion): array
    {
        if(isset($datatables->columns)){
            $columns = $datatables->columns;
        }
        else{
            $columns = $this->columns_datatable(rows_lista: $rows_lista, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);
            }
        }
        return $columns;
    }

    /**
     * Inicializa una columna
     * @param array|string $column Columna a inicializar
     * @param string $indice Indice de row
     * @return stdClass|array
     * @version 0.144.33
     */
    private function column_init(array|string $column, string $indice): stdClass|array
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = new stdClass();
        $column_obj->title = is_string($column)? $column:$indice;
        $column_obj->data = $indice;
        return $column_obj;
    }

    /**
     * Integra el titulo de columna
     * @param array $column Columna a integrar titulo
     * @param stdClass $column_obj Conjunto de datos de retorno
     * @param string $indice Key del elemento a integrar
     * @return stdClass|array
     * @version 0.146.34
     */
    private function column_titulo(array $column, stdClass $column_obj, string $indice): stdClass|array
    {
        $keys = array('titulo');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar column', data:  $valida);
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }
        $column_obj->title = $indice;
        if(is_string($column["titulo"])){
            $column_obj->title = $column["titulo"];
        }
        return$column_obj;
    }

    /**
     * Genera columnas para datatable
     * @param array $columns Columnas
     * @param array $datatable Objeto inicializado
     * @return array
     * @version 0.150.33
     */
    private function columns(array $columns, array $datatable): array
    {

        $index_button = -1;

        foreach ($columns as $indice => $column){

            $valida = $this->valida_base(column: $column,indice:  $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
            }

            $data = $this->genera_column(column: $column,columns:  $columns,datatable:  $datatable,
                indice:  $indice, index_button: $index_button);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column', data:  $data);
            }
            $datatable = $data->datatable;
            $index_button = $data->index_button;

        }
        return $datatable;
    }


    private function columns_datatable(array $rows_lista, string $seccion): array
    {
        $columns = array();
        foreach ($rows_lista as $key_row_lista){
            $columns = $this->titulo_column_datatable(columns: $columns,key_row_lista:  $key_row_lista, seccion: $seccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar column titulo ', data: $columns);
            }
        }
        return $columns;
    }

    /**
     * Genera las columnas para datatables
     * @param array|string $column Columna
     * @param string $indice Indice o key
     * @param int $targets n columna
     * @param string $type Typo button or text
     * @return stdClass|array
     * @version 0.143.33
     */
    private function columns_defs(array|string $column, string $indice, int $targets, string $type): stdClass|array
    {

        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $rendered = $this->rendered(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rendered', data:  $rendered);
        }
        $type = trim($type);
        if($type === ''){
            $type = 'text';
        }


        $columns_defs_obj = new stdClass();
        $columns_defs_obj->targets = $targets;
        $columns_defs_obj->data = null;
        $columns_defs_obj->type = $type;
        $columns_defs_obj->rendered = $rendered;

        array_unshift($columns_defs_obj->rendered,$indice);

        return $columns_defs_obj;
    }

    private function columns_dt(stdClass $datatables, PDO $link, array $rows_lista, string $seccion): array
    {
        $columns = $this->column_datable_init(datatables: $datatables,rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);

        }

        $columns = $this->acciones_columnas(columns: $columns, link: $link, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar acciones ', data: $columns);

        }
        return $columns;
    }

    public function database_link(array $adm_accion_grupo, html_controler $html, int $registro_id, string $style): array|stdClass
    {
        $link_con_id = $html->button_href(accion: $adm_accion_grupo['adm_accion_descripcion'],
            etiqueta:   $adm_accion_grupo['adm_accion_titulo'],registro_id:  $registro_id,
            seccion:  $adm_accion_grupo['adm_seccion_descripcion'],style:  $style, cols: 3 );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar button', data: $link_con_id);
        }
        $accion = $adm_accion_grupo['adm_accion_descripcion'];

        $data = new stdClass();
        $data->link_con_id = $link_con_id;
        $data->accion = $accion;

        return $data;

    }

    /**
     * Genera la estructura para datatables
     * @param array $columns Columnas
     * @param array $filtro Filtros
     * @param string $identificador
     * @param array $data
     * @return array
     * @version 0.152.33
     */
    public function datatable(array $columns, array $filtro = array(),string $identificador = ".datatable",
                              array $data = array()): array
    {
        $datatable = $this->init_datatable(filtro:$filtro,identificador: $identificador, data: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datatable', data:  $datatable);
        }

        $datatable = $this->columns(columns: $columns, datatable: $datatable);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columns', data:  $datatable);
        }
        return $datatable;
    }

    public function datatable_base_init(stdClass $datatables, PDO $link, array $rows_lista, string $seccion): array|stdClass
    {
        $filtro = $this->init_filtro_datatables(datatables: $datatables, rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar filtro', data: $filtro);
        }


        $columns = $this->columns_dt(datatables: $datatables, link: $link, rows_lista: $rows_lista, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);
        }

        $data = new stdClass();
        $data->filtro = $filtro;
        $data->columns = $columns;

        return $data;
    }

    /**
     * Obtiene draw para datatable
     * @return int|array
     * @version 0.239.37
     */
    private function draw(): int|array
    {
        $draw = mt_rand(1,999);
        if (isset ( $_GET['draw'] )) {
            $draw = $_GET['draw'];
        }
        if(!is_numeric($draw)){
            return $this->error->error(mensaje: 'Error draw debe ser un numero', data: $draw);
        }

        return $draw;
    }

    private function filtro(){
        $filtro  = array();
        if (isset($_GET['data'])){
            $filtro = $_GET['data'];
        }
        return $filtro;
    }


    private function filtro_accion_permitida(string $seccion): array
    {
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
     * Genera una columna para datatable
     * @param array|string $column Columna a integrar
     * @param array $columns Columnas
     * @param array $datatable obj inicializado de controler
     * @param string $indice indice de columna
     * @param int $index_button Index o posicion
     * @return array|stdClass
     * @version 0.149.33
     */
    private function genera_column(array|string $column, array $columns, array $datatable, string $indice, int $index_button): array|stdClass
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->maqueta_column_obj(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }

        $datatable["columns"][] = $column_obj;

        $indice_columna = array_search($indice, array_keys($columns));

        $type = $this->type(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar type', data:  $type);
        }

        $targets = $indice_columna === count($columns) ? $index_button:$indice_columna;

        $columnDefs_obj = $this->columns_defs(column: $column, indice: $indice, targets: $targets, type: $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnDefs', data:  $columnDefs_obj);
        }

        $datatable["columnDefs"][] = $columnDefs_obj;

        if ($type === 'button'){
            $index_button -= 1;
        }

        $data = new stdClass();
        $data->datatable = $datatable;
        $data->index_button = $index_button;
        return $data;
    }

    private function genera_filtro_especial_datatable(array $datatable): array
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

    /**
     * Inicializa datatables
     * @param array $filtro Filtro
     * @param string $identificador
     * @param array $data
     * @return array
     * @version 0.151.33
     */
    private function init_datatable(array $filtro,string $identificador = ".datatable", array $data = array()): array
    {
        $datatable["columns"] = array();
        $datatable["columnDefs"] = array();
        $datatable['filtro'] = $filtro;
        $datatable['identificador'] = $identificador;
        $datatable['data'] = $data;
        return $datatable;
    }

    private function init_filtro_datatables(stdClass $datatables, array $rows_lista, string $seccion): array
    {
        $filtro = array();
        if(!isset($datatables->filtro)){
            foreach ($rows_lista as $key_row_lista){
                $filtro[] = $seccion.'.'.$key_row_lista;
            }
        }
        else{
            $filtro = $datatables->filtro;
        }
        return $filtro;
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
     * Integra el titulo en ele objeto de columna a generar
     * @param array|string $column Columna data
     * @param stdClass $column_obj Columnas de retorno inicializadas
     * @param string $indice Key del row de datos
     * @return array|stdClass
     * @version 0.146.33
     */
    private function integra_titulo(array|string $column, stdClass $column_obj, string $indice): array|stdClass
    {
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }

        if (is_array($column) && array_key_exists("titulo",$column)){

            $column_obj = $this->column_titulo(column: $column, column_obj: $column_obj, indice: $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
            }
        }
        return $column_obj;
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

    /**
     * Maqueta una columna a integrar
     * @param array|string $column Columna
     * @param string $indice Key
     * @return array|stdClass
     * @version 0.147.33
     */
    private function maqueta_column_obj(array|string $column, string $indice): array|stdClass
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->column_init(column: $column, indice: $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column', data:  $column_obj);
        }

        $column_obj = $this->integra_titulo(column: $column, column_obj: $column_obj,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }
        return $column_obj;
    }

    private function not_in_accion(array $not_actions): array
    {
        $not_in = array();
        if(count($not_actions) > 0){
            $not_in['llave'] = 'adm_accion.descripcion';
            $not_in['values'] = $not_actions;
        }
        return $not_in;
    }

    private function n_rows_for_page(){
        $n_rows_for_page = 10;
        if (isset ( $_GET['length'] )) {
            $n_rows_for_page = $_GET['length'];
        }
        return $n_rows_for_page;
    }

    private function pagina(int $n_rows_for_page): int
    {
        $pagina = 1;
        if (isset ( $_GET['start'] )) {
            $pagina = (int)($_GET['start'] /  $n_rows_for_page) + 1;
        }
        if($pagina <= 0){
            $pagina = 1;
        }
        return $pagina;
    }

    public function params(array $datatable): array|stdClass
    {
        $draw = (new datatables())->draw();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener draw', data: $draw);
        }
        $n_rows_for_page = $this->n_rows_for_page();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener n_rows_for_page', data: $n_rows_for_page);
        }

        $pagina = $this->pagina(n_rows_for_page: $n_rows_for_page);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener pagina', data: $pagina);
        }
        $filtro = $this->filtro();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro);
        }

        $filtro_especial = $this->genera_filtro_especial_datatable(datatable: $datatable);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
        }

        $data = new stdClass();
        $data->draw = $draw;
        $data->n_rows_for_page = $n_rows_for_page;
        $data->pagina = $pagina;
        $data->filtro = $filtro;
        $data->filtro_especial = $filtro_especial;
        return $data;
    }

    private function rendered(string|array $column): array
    {
        $rendered = [];
        if(is_array($column)){
            if(array_key_exists('campos', $column)){

                if(!is_array($column['campos'])){
                    return $this->error->error(mensaje: 'Error $column[campos] debe seer un array', data:  $column);
                }

                $rendered = array_values($column["campos"]);
            }
        }
        return $rendered;
    }

    private function titulo_column_datatable(array $columns, string $key_row_lista, string $seccion): array
    {
        $titulo = str_replace('_', ' ', $key_row_lista);
        $titulo = ucwords( $titulo);
        $columns[$seccion."_$key_row_lista"]["titulo"] = $titulo;
        return $columns;
    }

    /**
     * Obtiene el type data
     * @param array|string $column Columna a validar
     * @return string
     * @version 0.148.33
     */
    private function type(array|string $column): string
    {
        $type = 'text';
        if(is_array($column) && array_key_exists("type",$column) && $column["type"] === "button"){
            $type = $column["type"];
        }
        return $type;
    }

    private function valida_base(array|string $column, string $indice): bool|array
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

    private function valida_data_column(string $seccion): bool|array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay SESSION iniciada', data: array());
        }
        $keys = array('grupo_id');
        $valida = $this->valida->valida_ids(keys: $keys,registro:  $_SESSION);
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
