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
    public function draw(): int|array
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

    /**
     * Inicializa datatables
     * @param array $filtro Filtro
     * @param string $identificador
     * @param array $data
     * @return array
     * @version 0.151.33
     */
    public function init_datatable(array $filtro,string $identificador = ".datatable", array $data = array()): array
    {
        $datatable["columns"] = array();
        $datatable["columnDefs"] = array();
        $datatable['filtro'] = $filtro;
        $datatable['identificador'] = $identificador;
        $datatable['data'] = $data;
        return $datatable;
    }

    public function init_filtro_datatables(stdClass $datatables, array $rows_lista, string $seccion): array
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

    public function n_rows_for_page(){
        $n_rows_for_page = 10;
        if (isset ( $_GET['length'] )) {
            $n_rows_for_page = $_GET['length'];
        }
        return $n_rows_for_page;
    }

    public function pagina(int $n_rows_for_page): int
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
