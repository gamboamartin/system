<?php
namespace gamboamartin\system;
use base\orm\modelo;
use base\orm\modelo_base;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;


use PDO;
use stdClass;

class html_controler{
    public directivas $directivas;
    protected errores $error;
    public html $html_base;

    public function __construct(html $html){
        $this->directivas = new directivas(html: $html);
        $this->error = new errores();
        $this->html_base = $html;
    }

    /**
     * Genera los inputs base de un alta de cualquier controller que herede
     * @param system $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.16.5
     */
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

    /**
     * Asigna los values de un select
     * @param stdClass $keys Keys para asignacion basica
     * @param array $registros Conjunto de registros a integrar
     * @return array
     * @version 0.48.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-02 18:12
     * @author mgamboa
     */
    private function genera_values_selects(stdClass $keys, array $registros): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }
        $values = array();
        foreach ($registros as $registro){
            if(!is_array($registro)){
                return $this->error->error(mensaje: 'Error registro debe ser un array',data:  $registro);
            }
            $keys_valida = array($keys->id,$keys->descripcion_select);
            $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
            }

            $values[$registro[$keys->id]] = $registro;
            $values[$registro[$keys->id]]['descripcion_select'] = $registro[$keys->descripcion_select];
        }
        return $values;
    }

    /**
     * Inicializa los datos de un select
     * @param bool $con_registros Si no con registros integra el select vacio para ser llenado posterior con ajax
     * @param modelo $modelo Modelo en ejecucion para la asignacion de datos
     * @param array $extra_params_keys Keys de extra params para ser cargados en un select
     * @param array $filtro Filtro para obtencion de datos para options
     * @param string $key_descripcion_select key del registro para mostrar en un select
     * @param string $key_id key Id de value para option
     * @param string $label Etiqueta a mostrar
     * @param string $name
     * @return array|stdClass
     * @version 0.52.32
     * @version 0.55.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:55
     * @author mgamboa
     */
    private function init_data_select(bool $con_registros, modelo $modelo, array $extra_params_keys = array(),
                                      array $filtro = array(), string $key_descripcion_select= '', string $key_id = '',
                                      string $label = '', string $name = ''): array|stdClass
    {

        $keys = $this->keys_base(tabla: $modelo->tabla, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, name: $name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar keys',data:  $keys);
        }

        $values = $this->values_selects(con_registros: $con_registros, keys: $keys,modelo: $modelo,
            extra_params_keys: $extra_params_keys, filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener valores',data:  $values);
        }

        $label_ =$label;
        if($label_ === '') {
            $label_ = $this->label(tabla: $modelo->tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener label', data: $label_);
            }
        }

        $keys->values = $values;
        $keys->label = $label_;
        return $keys;
    }

    /**
     * Genera un input de tipo codigo
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.72.32
     */
    public function input_codigo(int $cols, stdClass $row_upd, bool $value_vacio,bool $disabled = false,
                                 string $place_holder = 'Código'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disable: $disabled,name: 'codigo',
            place_holder: $place_holder,row_upd: $row_upd,
            value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo codigo bis
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.73.32
     */
    public function input_codigo_bis(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                 string $place_holder = 'Código BIS'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disable: $disabled,name: 'codigo_bis',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera iun input de tipo descripcion
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param string $place_holder Etiqueta a mostrar
     * @return array|string
     * @version 0.74.32
     */
    public function input_descripcion(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                                      string $place_holder = 'Descripcion'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cold debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cold debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disable: $disabled,name: 'descripcion',place_holder: $place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * Genera un input de tipo ID
     * @param int $cols Columnas en css
     * @param stdClass $row_upd Registro en proceso
     * @param bool $value_vacio is vacio no muestra datos
     * @param bool $disabled si disabled deshabilita input
     * @param string $place_holder etiqueta a mostrar
     * @return array|string
     * @version 0.75.32
     */
    public function input_id(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false,
                             string $place_holder = 'Id'): array|string
    {

        if($cols<=0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0', data: $cols);
        }
        if($cols>=13){
            return $this->error->error(mensaje: 'Error cols debe ser menor o igual a  12', data: $cols);
        }

        $html =$this->directivas->input_text_required(disable: $disabled,name: 'id',place_holder:$place_holder,
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    /**
     * @param stdClass $cols Objeto con la definicion del numero de columnas a integrar en un input base
     * @version 0.11.5
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
        if(empty($controler->inputs)){
            $controler->inputs = new stdClass();
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
     * @param string $tabla Tabla del select
     * @param string $key_descripcion_select base de descripcion
     * @param string $key_id identificador key
     * @param string $name Name del input
     * @return stdClass|array obj->id, obj->descripcion_select
     * @version 0.2.5
     * @verfuncion 0.2.0 Se carga name
     */
    private function keys_base(string $tabla, string $key_descripcion_select = '', string $key_id = '',
                               string $name = ''): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia',data:  $tabla);
        }
        if($key_id === '') {
            $key_id = $tabla . '_id';
        }
        $name = trim($name);
        if($name === ''){
            $name = $key_id;
        }
        if($key_descripcion_select === '') {
            $key_descripcion_select = $tabla.'_descripcion_select';
        }


        $data = new stdClass();
        $data->id = $key_id;
        $data->descripcion_select = $key_descripcion_select;
        $data->name = $name;

        return $data;
    }

    /**
     * Genera un label valido para se mostrado en front
     * @param string $tabla Tabla o estructura para generar etiqueta
     * @return string|array
     * @version 0.50.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-03 09:22
     * @author mgamboa
     */
    private function label(string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }
        $label = str_replace('_', ' ', $tabla);

        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error $label esta vacio', data: $label);
        }


        return ucwords($label);
    }

    /**
     * Genera un menu lateral con titulo
     * @param string $etiqueta Etiqueta del menu
     * @return array|string
     * @version 0.93.32
     */
    public function menu_lateral_title(string $etiqueta): array|string
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return $this->error->error(mensaje: 'Error la etiqueta esta vacia', data: $etiqueta);
        }
        $menu_lateral = $this->html_base->menu_lateral($etiqueta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar menu lateral texto', data: $menu_lateral);
        }
        return $menu_lateral;

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

    /**
     * Inicializa los parametros de un select
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros inicializados
     * @return stdClass|array
     * @version 0.95.32
     */
    private function params_select(string $name_model, stdClass $params): stdClass|array
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }
        $data = new stdClass();

        $data->cols = $params->cols ?? 12;
        $data->con_registros = $params->con_registros ?? true;
        $data->id_selected = $params->id_selected ?? -1;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($name_model));
        $data->required = $params->required ?? true;

        return $data;
    }

    /**
     * Obtiene los registros para un select
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo del select
     * @param array $filtro Filtro de datos para filtro and
     * @param array $extra_params_keys Datos a integrar para extra params
     * @return array
     * @version 0.47.32
     * @version 0.53.32
     * @verfuncion 0.1.0 UT fin
     * @verfuncion 0.2.0 Se integra param filtro
     * @fecha 2022-08-02 17:32
     * @fecha 2022-08-02 17:32
     * @author mgamboa
     */
    private function rows_select(stdClass $keys, modelo $modelo, array $extra_params_keys = array(),
                                 array $filtro = array()): array
    {
        $keys_val = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_val,registro:  $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $columnas[] = $keys->id;
        $columnas[] = $keys->descripcion_select;

        foreach ($extra_params_keys as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error el key de extra params esta vacio',data:  $extra_params_keys);
            }
            $columnas[] = $key;
        }

        $filtro[$modelo->tabla.'.status'] = 'activo';
        $registros = $modelo->filtro_and(columnas: $columnas, filtro: $filtro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data:  $registros);
        }
        return $registros->registros;
    }

    /**
     * Genera un select automatico conforme a params
     * @param PDO $link Conexion a la BD
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros a ejecutar para select
     * @param stdClass $selects Selects precargados
     * @return array|stdClass
     * @version 0.96.32
     */
    protected function select_aut(PDO $link, string $name_model, stdClass $params, stdClass $selects): array|stdClass
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }

        $params_select = $this->params_select(name_model: $name_model, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar params', data: $params_select);
        }

        $name_select_id = $name_model.'_id';
        $modelo = (new modelo_base($link))->genera_modelo(modelo: $name_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $select  = $this->select_catalogo(cols:$params_select->cols,con_registros:$params_select->con_registros,
            id_selected:$params_select->id_selected, modelo: $modelo,label:$params_select->label,
            required: $params_select->required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }

        $selects->$name_select_id = $select;

        return $selects;
    }

    /**
     * Genera el input de tipo select
     * @param int $cols Numero de columnas boostrap
     * @param bool $con_registros Si con registros , obtiene todos los registros activos del modelo en ejecucion
     *  para la asignacion de options, Si no, deja el select en blanco o vacio
     * @param int $id_selected Identificador de un registro y cargado utilizado para modifica, aplica selected
     * @param modelo $modelo Modelo de datos ejecucion
     * @param bool $disabled Si disabled el input queda deshabilitado
     * @param array $extra_params_keys Extraparams datos a obtener para integrar en data-extra
     * @param array $filtro Filtro para obtencion de datos
     * @param string $key_descripcion_select Key para mostrar en options
     * @param string $key_id Key para integrar el value
     * @param string $label Etiqueta a mostrar en select
     * @param string $name Nombre del input
     * @param bool $required si required agrega el atributo required a input
     * @return array|string Un string con options en forma de html
     * @version 0.56.32
     * @verfuncion 0.1.0
     * @fecha 2022-08-03 16:27
     * @author mgamboa
     */
    protected function select_catalogo(int $cols, bool $con_registros, int $id_selected, modelo $modelo,
                                       bool $disabled = false, array $extra_params_keys = array(),
                                       array $filtro=array(), string $key_descripcion_select = '', string $key_id = '',
                                       string $label = '', string $name = '', bool $required = false): array|string
    {

        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        $init = $this->init_data_select(con_registros: $con_registros, modelo: $modelo,
            extra_params_keys: $extra_params_keys, filtro:$filtro, key_descripcion_select: $key_descripcion_select,
            key_id: $key_id, label: $label, name: $name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos', data: $init);
        }

        $select = $this->html_base->select(cols:$cols, id_selected:$id_selected, label: $init->label,name:$init->name,
            values: $init->values, disabled: $disabled, extra_params_key: $extra_params_keys,required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        return new stdClass();
    }

    /**
     * Genera los values para ser utilizados en los selects options
     * @param bool $con_registros si con registros muestra todos los registros
     * @param stdClass $keys Keys para obtencion de campos
     * @param modelo $modelo Modelo para asignacion de datos
     * @param array $extra_params_keys Keys para asignacion de extra params para ser utilizado en javascript
     * @param array $filtro Filtro para obtencion de datos del select
     * @return array
     * @version 0.49.31
     * @version 0.54.32
     * @verfuncion 0.1.0
     * @verfuncion 0.2.0 Se integra filtro
     * @fecha 2022-08-03 09:04
     * @fecha 2022-08-03 14:50
     * @author mgamboa
     */
    private function values_selects( bool $con_registros, stdClass $keys, modelo $modelo,
                                     array $extra_params_keys = array(), array $filtro = array()): array
    {
        $keys_valida = array('id','descripcion_select');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys_valida, registro: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar keys',data:  $valida);
        }

        $registros = array();
        if($con_registros) {
            $registros = $this->rows_select(keys: $keys, modelo: $modelo, extra_params_keys: $extra_params_keys,
                filtro:$filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
            }
        }

        $values = $this->genera_values_selects(keys: $keys,registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar valores',data:  $values);
        }

        return $values;
    }


}
