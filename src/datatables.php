<?php
namespace gamboamartin\system;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\system\datatables\acciones;
use gamboamartin\system\datatables\filtros;
use gamboamartin\system\datatables\validacion_dt;
use gamboamartin\system\html_controler\params;
use gamboamartin\template\html;
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
     * Obtiene las acciones permitidas de un grupo de usuario
     * @param PDO $link Conexion a la base de datos
     * @param string $seccion Seccion de controlador
     * @param array $not_actions Acciones que no seran mostradas
     * @param array $columnas Columnas a mostrar en la lista
     * @return array
     * @version 0.153.33
     */
    final public function acciones_permitidas(PDO $link, string $seccion, array $not_actions = array(),
                                              array $columnas = array()): array
    {
        $valida = (new validacion_dt())->valida_data_column(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $filtro = (new filtros())->filtro_accion_permitida(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener not in', data: $filtro);
        }


        $not_in = $this->not_in_accion(not_actions: $not_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener not in', data: $not_in);
        }

        $r_accion_grupo = (new adm_accion_grupo($link))->filtro_and(columnas: $columnas, filtro: $filtro, not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones', data: $r_accion_grupo);

        }

        return $r_accion_grupo->registros;
    }


    /**
     * Inicializa datatables para get data
     * @param stdClass $datatables datatables precargadas desde controler
     * @param array $rows_lista Registros
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 7.2.0
     *
     */
    private function column_datable_init(stdClass $datatables, array $rows_lista, string $seccion): array
    {
        if($seccion === ''){
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }
        if(isset($datatables->columns)){
            if(!is_array($datatables->columns)){
                return $this->error->error(mensaje: 'Error $datatables->columns debe se run array ', data: $datatables);
            }
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
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
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
    private function columns(array $columns, array $datatable, bool $multi_selects = false): array
    {
        $index_button = -1;

        if ($multi_selects === true){
            $check = array("check" => array("titulo" => " "));
            $columns = array_merge($check, $columns);
        }

        foreach ($columns as $indice => $column){

            $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
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

    /**
     * Integra las columnas para datatables
     * @param array $rows_lista Registros de view
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 5.7.0
     */
    private function columns_datatable(array $rows_lista, string $seccion): array
    {
        if($seccion === ''){
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }
        $columns = array();
        foreach ($rows_lista as $key_row_lista){
            $key_row_lista = trim($key_row_lista);
            if($key_row_lista === ''){
                return $this->error->error(
                    mensaje: 'Error $key_row_lista debe ser un string con datos', data:  $key_row_lista);
            }

            $columns = $this->columns_title(columns: $columns,key_row_lista:  $key_row_lista, seccion: $seccion);
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

        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
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

        if ($indice === "check"){
            $columns_defs_obj->defaultContent = '';
            $columns_defs_obj->orderable = false;
            $columns_defs_obj->className = 'select-checkbox';
        }

        array_unshift($columns_defs_obj->rendered,$indice);

        return $columns_defs_obj;
    }

    /**
     * Integra las columnas para listas
     * @param stdClass $datatables objeto de controller
     * @param PDO $link Conexion a base de datos
     * @param array $not_actions Acciones no mostrables
     * @param array $rows_lista Registros de la lista
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 7.58.3
     */
    private function columns_dt(stdClass $datatables, PDO $link, array $not_actions, array $rows_lista,
                                string $seccion, string $type = "datatable"): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }

        $columns = $this->column_datable_init(datatables: $datatables,rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);

        }

        if ($type === "datatable"){
            $columns = (new acciones())->acciones_columnas(columns: $columns, link: $link, seccion: $seccion,
                not_actions: $not_actions);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar acciones ', data: $columns);
            }
        }

        return $columns;
    }

    /**
     * Integra las columnas de tipo titulo en datatables
     * @param array $columns Columnas precargadas datatables
     * @param string $key_row_lista key de campos a mostrar en lista
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 4.54.2
     */
    private function columns_title(array $columns, string $key_row_lista, string $seccion): array
    {
        $key_row_lista = trim($key_row_lista);
        if($key_row_lista === ''){
            return $this->error->error(
                mensaje: 'Error $key_row_lista debe ser un string con datos', data:  $key_row_lista);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }

        $columns = $this->titulo_column_datatable(
            columns: $columns,key_row_lista:  $key_row_lista, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar column titulo ', data: $columns);
        }
        return $columns;
    }



    final public function data_link(array $adm_accion_grupo, array $data_result, html $html_base, string $key,
                                    int $registro_id): array|stdClass
    {
        $style = (new html_controler(html: $html_base))->style_btn(
            accion_permitida: $adm_accion_grupo, row: $data_result['registros'][$key]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }


        $data_link = $this->database_link(adm_accion_grupo: $adm_accion_grupo,
            html: (new html_controler(html: $html_base)),registro_id:  $registro_id, style: $style);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data para link', data: $data_link);
        }

        return $data_link;
    }

    /**
     * Obtiene el link de accion
     * @param array $adm_accion_grupo Permiso
     * @param html_controler $html Base html
     * @param int $registro_id Identificador de registro en row
     * @param string $style Estilo de boton
     * @param array $styles Estilos
     * @return array|stdClass
     * @version 13.62.0
     */
    private function database_link(array $adm_accion_grupo, html_controler $html, int $registro_id, string $style,
                                  array $styles = array('margin-left'=>'2px', 'margin-bottom'=>'2px') ): array|stdClass
    {
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn','adm_accion_descripcion',
            'adm_accion_titulo','adm_seccion_descripcion');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn');
        $valida = (new validacion())->valida_statuses(keys: $keys, registro: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
        }
        $style = trim($style);
        if($style === ''){
            return $this->error->error(mensaje: 'Error la $style esta vacia', data: $style);
        }

        if(!isset($adm_accion_grupo['adm_accion_icono'])){
            $adm_accion_grupo['adm_accion_icono']  = '';
        }

        $icon = trim($adm_accion_grupo['adm_accion_icono']);


        $data_icon = (new params())->data_icon(adm_accion: $adm_accion_grupo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data_icon', data: $data_icon);
        }

        $link_con_id = $html->button_href(accion: $adm_accion_grupo['adm_accion_descripcion'],
            etiqueta:   $adm_accion_grupo['adm_accion_titulo'],registro_id:  $registro_id,
            seccion:  $adm_accion_grupo['adm_seccion_descripcion'],style:  $style, cols: -1,icon: $icon,
            muestra_icono_btn: $data_icon->muestra_icono_btn,muestra_titulo_btn: $data_icon->muestra_titulo_btn,
            styles: $styles );
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
     * @param array $in
     * @param bool $multi_selects
     * @param bool $menu_active
     * @param string $type
     * @return array
     * @version 0.152.33
     */
    final public function datatable(array $columns, array $filtro = array(),string $identificador = ".datatable",
                                    array $data = array(), array $in = array(), bool $multi_selects = false,
                                    bool $menu_active = false, string $type = "datatable"): array
    {
        $datatable = (new \gamboamartin\system\datatables\init())->init_datatable(filtro:$filtro, identificador: $identificador,
            data: $data,in: $in, multi_selects: $multi_selects, type : $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datatable', data:  $datatable);
        }

        $datatable = $this->columns(columns: $columns, datatable: $datatable, multi_selects: $multi_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columns', data:  $datatable);
        }

        if ($menu_active){
            if ($datatable['columnDefs'][count($datatable['columnDefs']) - 1]->type === 'button') {
                $datatable['columnDefs'][count($datatable['columnDefs']) - 1]->type = 'menu';
            }
        }

        return $datatable;
    }

    /**
     * Inicializa datatables
     * @param stdClass $datatables Objeto de controller
     * @param PDO $link Conexion a base de datos
     * @param array $rows_lista Registros de lista
     * @param string $seccion Seccion en ejecucion
     * @param array $not_actions Acciones a omitir
     * @return array|stdClass
     * @version 7.85.3
     *
     */
    final public function datatable_base_init(stdClass $datatables, PDO $link, array $rows_lista, string $seccion,
                                        array $not_actions = array()): array|stdClass
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }

        $filtro = (new \gamboamartin\system\datatables\init())->init_filtro_datatables(datatables: $datatables,
            rows_lista: $rows_lista,seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar filtro', data: $filtro);
        }

        $type = "datatable";

        if (property_exists($datatables,"type")){
            if (strcasecmp($datatables->type, "scroll") == 0) {
                $type = $datatables->type;
            }
        }

        $columns = $this->columns_dt(datatables: $datatables, link: $link, not_actions: $not_actions,
            rows_lista: $rows_lista, seccion: $seccion, type: $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columns ', data: $columns);
        }

        $multi_selects = false;

        if (property_exists($datatables,"multi_selects")){
            if (!is_bool($datatables->multi_selects)){
                return $this->error->error(mensaje: 'Error multi_selects tiene que ser de tipo bool', data: $datatables);
            }
            $multi_selects = $datatables->multi_selects;
        }

        $menu_active = false;

        if (property_exists($datatables,"menu_active")){
            if (!is_bool($datatables->menu_active)){
                return $this->error->error(mensaje: 'Error menu_active tiene que ser de tipo bool', data: $datatables);
            }
            $menu_active = $datatables->menu_active;
        }

        $data = new stdClass();
        $data->filtro = $filtro;
        $data->columns = $columns;
        $data->multi_selects = $multi_selects;
        $data->menu_active = $menu_active;
        $data->type = $type;

        return $data;
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
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
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

        if ($indice === "check"){
            $type = "check";
        }

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
     * Maqueta una columna a integrar
     * @param array|string $column Columna
     * @param string $indice Key
     * @return array|stdClass
     * @version 0.147.33
     */
    private function maqueta_column_obj(array|string $column, string $indice): array|stdClass
    {
        $valida = (new validacion_dt())->valida_base(column: $column,indice:  $indice);
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

    final public function params(array $datatable): array|stdClass
    {
        $draw = (new \gamboamartin\system\datatables\init())->draw();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener draw', data: $draw);
        }
        $n_rows_for_page = (new \gamboamartin\system\datatables\init())->n_rows_for_page();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener n_rows_for_page', data: $n_rows_for_page);
        }

        $pagina = (new \gamboamartin\system\datatables\init())->pagina(n_rows_for_page: $n_rows_for_page);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener pagina', data: $pagina);
        }
        $filtro = (new filtros())->filtro();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro);
        }

        $filtro_especial = (new filtros())->genera_filtro_especial_datatable(datatable: $datatable);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro_especial', data: $filtro_especial);
        }

        $in  = array();
        if (isset($_GET['in'])){
            $in = $_GET['in'];
        }

        $order  = array();
        if (isset($_GET['order'])){
            if (count($datatable['columns']) - 1 > $_GET['order'][0]['column']){
                $campo = $datatable['columns'][$_GET['order'][0]['column']]->data;
                $order = array($campo => $_GET['order'][0]['dir'] );
            }
        }

        $data = new stdClass();
        $data->draw = $draw;
        $data->n_rows_for_page = $n_rows_for_page;
        $data->pagina = $pagina;
        $data->filtro = $filtro;
        $data->filtro_especial = $filtro_especial;
        $data->in = $in;
        $data->order = $order;

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

    /**
     * Genera los titulos para datatables
     * @param array $columns Columnas a mostrar
     * @param string $key_row_lista Keys de campos a mostrar
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 0.304.39
     */
    private function titulo_column_datatable(array $columns, string $key_row_lista, string $seccion): array
    {
        $key_row_lista = trim($key_row_lista);
        if($key_row_lista === ''){
            return $this->error->error(
                mensaje: 'Error $key_row_lista debe ser un string con datos', data:  $key_row_lista);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(
                mensaje: 'Error seccion debe ser un string con datos', data:  $seccion);
        }
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




}
