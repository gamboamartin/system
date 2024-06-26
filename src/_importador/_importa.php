<?php
namespace gamboamartin\system\_importador;
use base\controller\controler;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\plugins\Importador;
use stdClass;

class _importa
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function headers(controler $controler, string $ruta_absoluta): array
    {
        $columnas_doc = (new Importador())->primer_row(celda_inicio: 'A1', ruta_absoluta: $ruta_absoluta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas_doc',data:  $columnas_doc);
        }
        $adm_campos = (new _xls())->adm_campos_inputs(columnas_doc: $columnas_doc,link:  $controler->link,tabla:  $controler->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos',data:  $adm_campos);
        }
        $headers = array();
        foreach ($adm_campos as $adm_campo){
            $headers[] = $adm_campo['adm_campo_descripcion'];
        }
        $headers[] = 'Selecciona';

        return $headers;

    }

    private function genera_rows_importar_db(stdClass $datos): array
    {
        $params = $this->params_legibles();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener params',data:  $params);
        }

        $rows_a_importar = $this->rows_a_importar(datos: $datos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rows_a_importar',data:  $rows_a_importar);
        }

        $rows_a_importar_db = $this->rows_a_importar_db(params: $params,rows_a_importar:  $rows_a_importar);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rows_a_importar_db',data:  $rows_a_importar_db);
        }
        return $rows_a_importar_db;

    }

    final public function importa_registros_xls(modelo $modelo, string $ruta_absoluta): array|stdClass
    {
        $datos = (new Importador())->leer(ruta_absoluta: $ruta_absoluta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos',data:  $datos);
        }

        $rows_a_importar_db = $this->genera_rows_importar_db(datos: $datos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rows_a_importar_db',data:  $rows_a_importar_db);
        }

        $altas = $this->importa_rows(modelo: $modelo, rows_a_importar_db: $rows_a_importar_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inserta registro', data: $altas);
        }

        $rs = new stdClass();
        $rs->datos = $datos;
        $rs->rows_a_importar_db = $rows_a_importar_db;
        $rs->altas = $altas;
        return $rs;

    }

    private function importa_rows(modelo $modelo, array $rows_a_importar_db): array
    {
        $altas = array();

        foreach ($rows_a_importar_db as $row){
            $r_alta = $modelo->alta_registro(registro: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inserta registro', data: $r_alta);
            }
            $altas[] = $r_alta;
        }
        return $altas;

    }

    private function params_legibles()
    {
        $params = $_POST['params_importa'];
        $params = base64_decode($params);
        return unserialize($params);

    }

    private function row_importar(array $params, stdClass $row_xls): array
    {
        $row_importar = array();
        foreach ($params as $campo_bd=>$campo_xls){
            $row_importar[$campo_bd] = $row_xls->$campo_xls;
        }
        return $row_importar;

    }

    private function rows_a_importar(stdClass $datos): array
    {
        $indices_rows = $_POST['row'];
        $rows_a_importar = array();
        foreach ($indices_rows as $indice=>$status){
            if($status === 'on'){
                $rows_a_importar[] = $datos->rows[$indice];
            }
        }
        return $rows_a_importar;

    }

    private function rows_a_importar_db(array $params, array $rows_a_importar): array
    {
        $rows_a_importar_db = array();
        foreach ($rows_a_importar as $row_xls){
            $row_importar = $this->row_importar(params: $params,row_xls:  $row_xls);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener row_importar',data:  $row_importar);
            }
            $rows_a_importar_db[] = $row_importar;
        }
        return $rows_a_importar_db;


    }



}
