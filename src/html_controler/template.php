<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use stdClass;


class template{
    protected errores $error;
    protected validacion_html $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion_html();
    }

    /**
     * Integra base template inputs
     * @param directivas $directivas html
     * @param stdClass $params_select parametros de inputs
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 7.27.0
     */
    private function base_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {
        $valida = $this->validacion->valida_input_base(directivas: $directivas,params_select:  $params_select);
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
     * Genera los inputs de tipo email
     * @param directivas $directivas Directivas
     * @param stdClass $params_select Parametros de inputs
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 4.49.2
     *
     */
    final public function emails_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $valida = $this->validacion->valida_input_base(directivas: $directivas,params_select:  $params_select);
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
    final public function dates_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {
        $div = $this->base_template(directivas: $directivas,params_select:  $params_select, row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    final public function fechas_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $div = $this->base_template(directivas: $directivas,params_select:  $params_select, row_upd: $row_upd);
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

        $valida = $this->validacion->valida_params(directivas: $directivas, params_select: $params_select);
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


    /**
     * Genera los inputs dentro de un password template
     * @param directivas $directivas Directiva de html
     * @param stdClass $params_select Parametros
     * @param stdClass $row_upd registro en proceso
     * @return array|string
     * @version 0.321.40
     */
    public function passwords_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {
        $valida = $this->validacion->valida_input_base(directivas: $directivas,params_select:  $params_select);
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

    /**
     * Genera los input de tipo telefono
     * @param directivas $directivas Directiva de template
     * @param stdClass $params_select Parametros para front
     * @param stdClass $row_upd Registro en proceso
     * @return array|string
     * @version 4.35.1
     */
    final public function telefonos_template(directivas $directivas, stdClass $params_select, stdClass $row_upd): array|string
    {

        $valida = $this->validacion->valida_input_base(directivas: $directivas,params_select:  $params_select);
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







}
