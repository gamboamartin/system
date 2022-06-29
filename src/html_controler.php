<?php
namespace gamboamartin\system;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use html\directivas;
use html\html;
use stdClass;

class html_controler{
    protected directivas $directivas;
    protected errores $error;

    public function __construct(){
        $this->directivas = new directivas();
        $this->error = new errores();
    }

    public function alta(system $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        $cols = new stdClass();
        $cols->codigo = 6;
        $cols->codigo_bis = 6;
        $inputs_base = $this->inputs_base(cols:$cols, controler: $controler, value_vacio: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }

    private function genera_values_selects(stdClass $keys, array $registros): array
    {
        $values = array();
        foreach ($registros as $registro){
            $values[$registro[$keys->id]] = $registro[$keys->descripcion_select];
        }
        return $values;
    }

    private function init_data_select(modelo $modelo): array|stdClass
    {
        $keys = $this->keys_base(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(keys: $keys,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valores',data:  $values);
        }

        $label = $this->label(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener label',data:  $label);
        }

        $keys->values = $values;
        $keys->label = $label;
        return $keys;
    }

    /**
     * @param stdClass $cols Objeto con la definicion del numero de columnas a integrar en un input base
     * @param system $controler
     * @param bool $value_vacio
     * @return array|stdClass
     */
    protected function inputs_base(stdClass $cols, system $controler, bool $value_vacio): array|stdClass
    {

        $keys = array('codigo','codigo_bis');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        $valida = (new validacion())->valida_numerics(keys: $keys, row: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }

        $html_codigo = $this->directivas->input_codigo(cols: $cols->codigo,row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }
        $controler->inputs->codigo = $html_codigo;

        $html_codigo_bis = $this->directivas->input_codigo_bis(cols: $cols->codigo_bis,
            row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_codigo);
        }

        $controler->inputs->codigo_bis = $html_codigo_bis;

        $html_descripcion = $this->directivas->input_descripcion(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion);
        }
        $controler->inputs->descripcion = $html_descripcion;

        $html_alias = $this->directivas->input_alias(row_upd: $controler->row_upd,value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_alias);
        }
        $controler->inputs->alias = $html_alias;

        $html_descripcion_select = $this->directivas->input_descripcion_select(row_upd: $controler->row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_descripcion_select);
        }
        $controler->inputs->descripcion_select = $html_descripcion_select;

        return $controler->inputs;
    }

    /**
     * Asigna los keys necesarios para un select
     * @param string $tabla Tabla o nombre del modelo en ejecucion
     * @return stdClass|array obj->id, obj->descripcion_select
     * @version 0.2.5
     */
    private function keys_base(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        $key_id = $tabla.'_id';
        $key_descripcion_select = $tabla.'_descripcion_select';

        $data = new stdClass();
        $data->id = $key_id;
        $data->descripcion_select = $key_descripcion_select;

        return $data;
    }

    private function label(string $tabla): string
    {
        $label = str_replace('_', ' ', $tabla);
        return ucwords($label);
    }

    public function modifica(system $controler): array|stdClass
    {
        $controler->inputs = new stdClass();

        $html_id = $this->directivas->input_id(cols:4,row_upd: $controler->row_upd,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html_id);
        }
        $controler->inputs->id = $html_id;

        $cols = new stdClass();
        $cols->codigo = 4;
        $cols->codigo_bis = 4;
        $inputs_base = $this->inputs_base(cols:$cols,controler: $controler,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs_base);
        }

        return $controler->inputs;
    }

    private function rows_select(stdClass $keys, modelo $modelo): array
    {
        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;

        $registros = $modelo->registros_activos(columnas: $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }
        return $registros;
    }

    protected function select_catalogo(int $id_selected, modelo $modelo): array|string
    {

        $init = $this->init_data_select(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $init);
        }

        $select = (new html())->select(cols:12, id_selected:$id_selected, label: $init->label,name:$init->id,
            values: $init->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    private function values_selects(stdClass $keys, modelo $modelo): array
    {
        $registros = $this->rows_select(keys: $keys,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }

        $values = $this->genera_values_selects(keys: $keys,registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valores',data:  $values);
        }

        return $values;
    }


}
