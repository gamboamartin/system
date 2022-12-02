<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;

use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use stdClass;


class texts{
    protected errores $error;
    protected validacion_html $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion_html();
    }

    /**
     * Integra los inputs de tipo text para una view
     * @param directivas $directivas Directivas html
     * @param string $item Name input
     * @param array $keys_selects Params inputs
     * @param stdClass $row_upd Registro en proceso
     * @param stdClass $texts inputs precargados
     * @return array|stdClass
     * @version 0.251.37
     */
    private function text_input_integra(
        directivas $directivas, string $item, array $keys_selects, stdClass $row_upd, stdClass $texts): array|stdClass
    {

        $item = trim($item);
        if($item === ''){
            return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
        }
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un texto', data: $item);
        }

        $params_select = (new params())->params_select_init(item:$item,keys_selects:  $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }

        $valida = $this->validacion->valida_params(directivas: $directivas, params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $input = (new template())->input_template(
            directivas: $directivas, params_select: $params_select,row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $input);
        }
        $texts->$item = $input;
        return $texts;
    }

    /**
     * Integra los inputs de tipo text
     * @param array $campos_view Campos de modelo
     * @param directivas $directivas Directivas html
     * @param array $keys_selects Parametros
     * @param stdClass $row_upd Registro en proceso
     * @return array|stdClass
     * @version 0.254.37
     */
    public function texts_integra(array $campos_view, directivas $directivas, array $keys_selects, stdClass $row_upd): array|stdClass
    {

        $keys = array('inputs');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $campos_view);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campos_view', data: $valida);
        }



        $texts = new stdClass();

        foreach ($campos_view['inputs'] as $item){

            $item = trim($item);
            if($item === ''){
                return $this->error->error(mensaje: 'Error item esta vacio', data: $item);
            }
            if(is_numeric($item)){
                return $this->error->error(mensaje: 'Error item debe ser un texto', data: $item);
            }

            $texts = $this->text_input_integra(
                directivas: $directivas, item: $item,keys_selects:  $keys_selects,row_upd:  $row_upd,texts:  $texts);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $texts);
            }

        }
        return $texts;
    }



}
