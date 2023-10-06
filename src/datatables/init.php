<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class init{
    private errores $error;


    public function __construct(){
        $this->error = new errores();

    }

    /**
     * Obtiene draw para datatable
     * @return int|array
     * @version 0.239.37
     */
    final public function draw(): int|array
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

    final public function in(){
        $in  = array();
        if (isset($_GET['in'])){
            $in = $_GET['in'];
        }
        return $in;
    }

    /**
     * Inicializa datatables
     * @param array $filtro Filtro
     * @param string $identificador
     * @param array $data
     * @param array $in
     * @param bool $multi_selects
     * @param string $type
     * @return array
     * @version 0.151.33
     */
    final public function init_datatable(array $filtro,string $identificador = ".datatable", array $data = array(),
                                         array $in = array(), bool $multi_selects = false, string $type = "datatable"): array
    {
        $datatable["columns"] = array();
        $datatable["columnDefs"] = array();
        $datatable['filtro'] = $filtro;
        $datatable['identificador'] = $identificador;
        $datatable['data'] = $data;
        $datatable['in'] = $in;
        $datatable['multi_selects'] = $multi_selects;
        $datatable['type'] = $type;

        return $datatable;
    }

    /**
     * Inicializa los filtros para datatables de listas
     * @param stdClass $datatables Estructura de datatables
     * @param array $rows_lista Registros de lista
     * @param string $seccion Seccion en ejecucion
     * @return array
     * @version 0.284.38
     */
    final public function init_filtro_datatables(stdClass $datatables, array $rows_lista, string $seccion): array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia', data: $seccion);
        }
        $filtro = array();
        if(!isset($datatables->filtro)){
            foreach ($rows_lista as $key_row_lista){
                $key_row_lista = trim($key_row_lista);
                if($key_row_lista === ''){
                    return $this->error->error(mensaje: 'Error key_row_lista esta vacia', data: $key_row_lista);
                }

                $filtro[] = $seccion.'.'.$key_row_lista;
            }
        }
        else{
            $filtro = $datatables->filtro;
        }
        return $filtro;
    }

    final public function order(array $datatable): array
    {
        $order  = array();
        if (isset($_GET['order'])){
            if (count($datatable['columns']) - 1 > $_GET['order'][0]['column']){
                $campo = $datatable['columns'][$_GET['order'][0]['column']]->data;
                $order = array($campo => $_GET['order'][0]['dir'] );
            }
        }
        return $order;
    }

    /**
     * Integra el numero de registros por pagina en el get data
     * @return int
     * @version 13.63.0
     */
    final public function n_rows_for_page(): int
    {
        $n_rows_for_page = 10;
        if (isset ( $_GET['length'] )) {
            $n_rows_for_page = (int)$_GET['length'];
        }
        return $n_rows_for_page;
    }

    /**
     * Obtiene la pagina de ejecucion
     * @param int $n_rows_for_page Registros por pagina
     * @return int
     */
    final public function pagina(int $n_rows_for_page): int
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


}
