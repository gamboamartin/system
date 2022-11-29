<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use stdClass;


class template{
    protected errores $error;
    protected validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    public function emails_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $valida = $this->valida_input_base(directivas: $directivas,params_select:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $html =$directivas->email_required(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder,  row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Integra los datos para un template
     * @param directivas $directivas Directiva de html
     * @param stdClass $params_select Parametros de select
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 0.233.37
     */
    public function dates_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {
        $valida = $this->valida_input_base(directivas: $directivas,params_select:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }


        $html =$directivas->fecha_required(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder,  row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera un input de tipo template
     * @param directivas $directivas Directivas html
     * @param stdClass $params_select Parametros de input
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 0.243.37
     */
    public function input_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $valida = (new params())->valida_params(directivas: $directivas, params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $html =$directivas->input_text(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder, required: $params_select->required, row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function passwords_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {
        $valida = $this->valida_input_base(directivas: $directivas,params_select:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $html =$directivas->input_password(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder,  row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function telefonos_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $valida = $this->valida_input_base(directivas: $directivas,params_select:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $html =$directivas->input_telefono(disabled: $params_select->disabled, name: $params_select->name,
            place_holder: $params_select->place_holder,  row_upd: $row_upd,
            value_vacio: $params_select->value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $directivas->html->div_group(cols: $params_select->cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    private function valida_base(mixed $params_select): bool|array
    {
        $keys = array('cols','disabled','name','place_holder','value_vacio');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }
        return true;
    }

    private function valida_input(mixed $params_select): bool|array
    {
        $valida = $this->valida_base(params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('cols');
        $valida = $this->validacion->valida_numerics(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        $keys = array('disabled','value_vacio');
        $valida = $this->validacion->valida_bools(keys: $keys,row:  $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }

        return true;
    }

    private function valida_input_base(directivas $directivas, mixed $params_select): bool|array
    {
        $valida = $this->valida_input(params_select: $params_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar params_select', data: $valida);
        }


        $valida = $directivas->valida_cols(cols: $params_select->cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }
        return true;
    }

}
