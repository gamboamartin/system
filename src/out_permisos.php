<?php
namespace gamboamartin\system;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class out_permisos{

    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    public function buttons_permitidos(array $acciones_permitidas, int $cols, html_controler $html, array $registro, int $registro_id): array
    {
        $buttons = array();
        foreach ($acciones_permitidas as $accion_permitida){
            $link = $this->link_btn_action(accion_permitida: $accion_permitida,cols:  $cols,
                html:  $html, registro:  $registro, registro_id: $registro_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar link',data:  $link);
            }
            $buttons[] = $link;
        }
        return $buttons;
    }

    public function cols_btn_action(array $acciones_permitidas): int
    {
        $n_acciones = count($acciones_permitidas);

        $cols = (int)($n_acciones / 4);
        if($cols < 3){
            $cols = 3;
        }
        return $cols;
    }

    /**
     * Genera el conjunto de botones
     * @param array $acciones_permitidas Acciones permitidas
     * @param html_controler $html template
     * @param string $key_id Key de row
     * @param array $rows conjunto de registros
     * @return array
     * @version 0.172.34
     */
    public function genera_buttons_permiso(
        array $acciones_permitidas, html_controler$html, string $key_id, array $rows): array
    {
        foreach ($rows as $indice=>$row){
            $rows = $this->integra_acciones_permitidas(acciones_permitidas: $acciones_permitidas, html: $html,
                indice:  $indice,key_id:  $key_id, row: $row,rows:  $rows);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }

    /**
     * Integra las acciones permitidas a un row para lista
     * @param array $acciones_permitidas Conjunto de acciones
     * @param html_controler $html Template html
     * @param int $indice Indice de la matriz de los registros a mostrar
     * @param string $key_id key de valor para registro id
     * @param array $row registro en proceso
     * @param array $rows conjunto de registros
     * @return array
     * @version 0.167.34
     */
    private function integra_acciones_permitidas(
        array $acciones_permitidas, html_controler $html, int $indice, string $key_id, array $row, array $rows): array
    {

        if($indice < 0){
            return $this->error->error(mensaje: 'Error indice debe ser mayor o igual a 0',data:  $indice);
        }
        $key_id = trim($key_id);
        if($key_id ===''){
            return $this->error->error(mensaje: 'Error key_id esta vacio',data:  $key_id);
        }
        if(is_numeric($key_id)){
            return $this->error->error(mensaje: 'Error key_id debe ser un campo con texto',data:  $key_id);
        }
        if(!isset($rows[$indice])){
            return $this->error->error(mensaje: 'Error no existe el registro en proceso',data:  $rows);
        }

        if(!isset($rows[$indice]['acciones'])){
            $rows[$indice]['acciones'] = array();
        }

        foreach ($acciones_permitidas as $accion_permitida){

            $valida = $this->valida_data_btn(
                accion_permitida: $accion_permitida,key_id:  $key_id, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
            }

            $rows = $html->boton_link_permitido(
                accion_permitida: $accion_permitida,indice:  $indice,registro_id:  $row[$key_id],rows:  $rows);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar link',data:  $rows);
            }
        }
        return $rows;
    }

    private function link_btn_action(array $accion_permitida, int $cols, html_controler $html, array $registro, int $registro_id): array|string
    {
        $valida = $this->valida_data_action(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion_permitida',data:  $valida);
        }

        $style = $html->style_btn(accion_permitida: $accion_permitida, row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener style',data:  $style);
        }

        $link = $html->button_href(accion: $accion_permitida['adm_accion_descripcion'],
            etiqueta: $accion_permitida['adm_accion_titulo'], registro_id:  $registro_id,
            seccion: $accion_permitida['adm_seccion_descripcion'], style:  $style, cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar link',data:  $link);
        }
        return $link;
    }

    private function valida_data_action(array $accion_permitida): bool|array
    {
        $keys = array('adm_accion_descripcion','adm_accion_titulo','adm_seccion_descripcion','adm_accion_css',
            'adm_accion_es_status');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }
        return true;
    }

    private function valida_data_btn(mixed $accion_permitida, string $key_id, array|stdClass $row): bool|array
    {
        $keys = array($key_id);
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row',data:  $valida);
        }
        if(!is_array($accion_permitida)){
            return $this->error->error(mensaje: 'Error accion_permitida debe ser array',data:  $accion_permitida);
        }

        $valida = $this->valida_data_action(accion_permitida: $accion_permitida);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar  accion_permitida',data:  $valida);
        }
        return true;
    }




}
