<?php
namespace gamboamartin\system\_importador;
use base\controller\controler;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\errores\errores;
use PDO;

class _campos
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function adm_campos(PDO $link, string $tabla): array
    {
        $modelo_adm_campo = new adm_campo(link: $link);

        $adm_campos = $modelo_adm_campo->campos_by_seccion(adm_seccion_descripcion: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos',data:  $adm_campos);
        }

        $adm_campos = $this->limpia_adm_campos_full(adm_campos: $adm_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar adm_campos',data:  $adm_campos);
        }
        return $adm_campos;

    }

    private function campo_valida(array $adm_campos, string $campo_db): array
    {
        $campo_db = trim($campo_db);
        if($campo_db === ''){
            return $this->error->error(mensaje: 'Error campo_db esta vacio',data:  $campo_db,es_final: true);
        }

        $campo_valida = array();
        foreach ($adm_campos as $adm_campo){
            if(!is_array($adm_campo)){
                return $this->error->error(mensaje: 'Error adm_campo debe ser un array',data:  $adm_campo,
                    es_final: true);
            }
            if(!isset($adm_campo['adm_campo_descripcion'])){
                return $this->error->error(mensaje: 'Error adm_campo_descripcion debe existir',data:  $adm_campos,
                    es_final: true);
            }
            if($adm_campo['adm_campo_descripcion'] === $campo_db){
                $campo_valida = $adm_campo;
                break;
            }
        }
        return $campo_valida;

    }

    private function init_row_final(string $campo_db, string $key, array $rows_finals, bool $valida): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data:  $key, es_final: true);
        }
        $campo_db = trim($campo_db);
        if($campo_db === ''){
            return $this->error->error(mensaje: 'Error campo_db esta vacio',data:  $campo_db, es_final: true);
        }

        $rows_finals[$key][$campo_db]['exito'] = $valida;
        $rows_finals[$key][$campo_db]['mensaje'] = 'valido';
        $rows_finals[$key][$campo_db]['contexto'] = 'success';
        return $rows_finals;

    }

    final public function integra_row_final(string $campo_db, string $contexto_error, string $key, string $mensaje,
                                            array $rows_finals, bool $valida): array
    {
        $rows_finals = $this->init_row_final(campo_db: $campo_db,key:  $key,rows_finals:  $rows_finals,valida:  $valida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar rows_finals',data:  $rows_finals);
        }
        if(!$valida){
            $rows_finals[$key][$campo_db]['mensaje'] = $mensaje;
            $rows_finals[$key][$campo_db]['contexto'] = $contexto_error;
        }
        return $rows_finals;

    }

    private function limpia_adm_campos_full(array $adm_campos): array
    {
        foreach ($adm_campos as $indice=>$adm_campo){
            $adm_campos = $this->limpia_campos(adm_campo: $adm_campo,adm_campos:  $adm_campos,indice:  $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar adm_campos',data:  $adm_campos);
            }
        }

        return $adm_campos;

    }
    private function limpia_campos(array $adm_campo, array $adm_campos, int $indice): array
    {
        if($adm_campo['adm_campo_descripcion'] === 'usuario_alta_id'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'usuario_update_id'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'fecha_alta'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'fecha_update'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'predeterminado'){
            unset($adm_campos[$indice]);
        }

        return $adm_campos;

    }

    final public function rows_importa(controler $controler, array $rows_xls): array
    {
        $rows = array();
        foreach ($rows_xls as $row){
            $row_importa = array();
            foreach ($_POST as $campo_db=>$campo_xls) {
                $row_importa[$campo_db] = $row->$campo_xls;
            }
            $rows[] = $row_importa;
        }

        $params = serialize($_POST);
        $params = base64_encode($params);
        $controler->params_importa = $params;

        return $rows;

    }

    final public function tipo_dato_valida(array $adm_campos, string $campo_db): array|string
    {
        $campo_valida = $this->campo_valida(adm_campos: $adm_campos,campo_db:  $campo_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campo_valida',data:  $campo_valida);
        }

        return trim($campo_valida['adm_tipo_dato_codigo']);

    }

    final public function valida_doc_importa(string $extension): true|array
    {
        $extensiones_permitidas = array('csv','ods','xls','xlsx');

        if(!in_array($extension, $extensiones_permitidas)){
            return $this->error->error(mensaje: 'Error el documento no tiene una extension permitida',data: $extension
                , es_final: true);
        }
        return true;

    }

}
