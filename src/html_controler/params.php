<?php
namespace gamboamartin\system\html_controler;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use stdClass;

class params{
    protected errores $error;
    protected validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Verifica si los botones asociados a una acción deben mostrar un ícono y/o un título según los datos proporcionados.
     *
     * Esta función valida que los campos necesarios estén presentes y sean correctos. Luego, determina si los botones
     * asociados a la acción deben mostrar un ícono y/o un título basándose en los valores de los campos correspondientes.
     * Los valores de los campos `adm_accion_muestra_icono_btn` y `adm_accion_muestra_titulo_btn` son evaluados,
     * y si su valor es `'activo'`, se establece a `true`, lo que indica que el ícono o el título deben mostrarse.
     *
     * **Pasos de validación y lógica:**
     * 1. Verifica que los campos `adm_accion_muestra_icono_btn` y `adm_accion_muestra_titulo_btn` existan en el arreglo `$adm_accion`.
     * 2. Valida que los valores de estos campos sean correctos (usando el método `valida_statuses`).
     * 3. Si `adm_accion_muestra_icono_btn` es `'activo'`, se establece que el botón debe mostrar un ícono.
     * 4. Si `adm_accion_muestra_titulo_btn` es `'activo'`, se establece que el botón debe mostrar un título.
     * 5. Retorna un objeto `stdClass` que contiene dos propiedades: `muestra_icono_btn` y `muestra_titulo_btn`, que indican si el ícono y el título deben mostrarse respectivamente.
     *
     * **Parámetros:**
     *
     * @param array $adm_accion Datos de la acción permitida, que debe contener los siguientes campos:
     *  - `adm_accion_muestra_icono_btn`: Define si el botón asociado a la acción debe mostrar un ícono. El valor esperado es `'activo'` o `'inactivo'`.
     *  - `adm_accion_muestra_titulo_btn`: Define si el botón asociado a la acción debe mostrar un título. El valor esperado es `'activo'` o `'inactivo'`.
     *
     * **Retorno:**
     * - Devuelve un objeto `stdClass` con las propiedades `muestra_icono_btn` y `muestra_titulo_btn`, ambas de tipo `bool`.
     * - Si los valores de los campos de acción son `'activo'`, las propiedades correspondientes se establecen en `true`, indicando que el ícono y/o el título deben mostrarse.
     * - Si los valores de los campos son `'inactivo'`, las propiedades se establecerán en `false`.
     *
     * **Ejemplos de uso:**
     *
     * **Ejemplo 1: Acción con ícono y título visibles**
     * ```php
     * $adm_accion = [
     *     'adm_accion_muestra_icono_btn' => 'activo',
     *     'adm_accion_muestra_titulo_btn' => 'activo'
     * ];
     *
     * $data = $this->data_icon($adm_accion);
     * echo $data->muestra_icono_btn; // Imprime 'true'
     * echo $data->muestra_titulo_btn; // Imprime 'true'
     * ```
     *
     * **Ejemplo 2: Acción sin ícono y título visibles**
     * ```php
     * $adm_accion = [
     *     'adm_accion_muestra_icono_btn' => 'inactivo',
     *     'adm_accion_muestra_titulo_btn' => 'inactivo'
     * ];
     *
     * $data = $this->data_icon($adm_accion);
     * echo $data->muestra_icono_btn; // Imprime 'false'
     * echo $data->muestra_titulo_btn; // Imprime 'false'
     * ```
     *
     * **Ejemplo 3: Error de validación**
     * ```php
     * $adm_accion = [
     *     'adm_accion_muestra_icono_btn' => 'actvo', // Error en el valor
     *     'adm_accion_muestra_titulo_btn' => 'activo'
     * ];
     *
     * $data = $this->data_icon($adm_accion);
     * // Retorna un arreglo de error debido a la invalidación de 'actvo'.
     * ```
     *
     * @version 1.0.0
     */
    final public function data_icon(array $adm_accion): stdClass|array
    {
        // Verificar la existencia de las claves necesarias
        $keys = array('adm_accion_muestra_icono_btn','adm_accion_muestra_titulo_btn');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $adm_accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion', data: $valida);
        }

        // Validar que los estados sean correctos
        $valida = $this->validacion->valida_statuses(keys: $keys, registro: $adm_accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar adm_accion', data: $valida);
        }

        // Determinar si se debe mostrar el ícono
        $muestra_icono_btn = false;
        if($adm_accion['adm_accion_muestra_icono_btn'] === 'activo'){
            $muestra_icono_btn = true;
        }

        // Determinar si se debe mostrar el título
        $muestra_titulo_btn = false;
        if($adm_accion['adm_accion_muestra_titulo_btn'] === 'activo'){
            $muestra_titulo_btn = true;
        }

        // Crear el objeto de resultado
        $data = new stdClass();
        $data->muestra_icono_btn = $muestra_icono_btn;
        $data->muestra_titulo_btn = $muestra_titulo_btn;

        return $data;
    }


    /**
     * Inicializa los parametros para un input
     * @param stdClass $data Data precargado
     * @param string $name Nombre del input
     * @param stdClass $params Parametros
     * @return stdClass|array
     */
    private function params_base(stdClass $data, string $name, stdClass $params): stdClass|array
    {
        $name = trim($name);
        if(is_numeric($name)){
            return $this->error->error(mensaje: 'Error name debe ser un string no un numero', data: $name);
        }
        $data->disabled = $params->disabled ?? false;
        $data->con_registros = $params->con_registros ?? true;
        $data->id_selected = $params->id_selected ?? -1;
        $data->required = $params->required ?? true;
        $data->row_upd = $params->row_upd ?? new stdClass();
        $data->value_vacio = $params->value_vacio ?? false;
        $data->filtro = $params->filtro ?? array();
        $data->not_in = $params->not_in ?? array();
        $data->in = $params->in ?? array();
        $data->name = $params->name ?? $name;
        $data->registros = $params->registros ?? array();
        $data->modelo_preferido = $params->modelo_preferido ?? true;

        $data->extra_params_keys = array();
        if(isset($params->extra_params_keys) ){
            $data->extra_params_keys = $params->extra_params_keys;
        }


        return $data;
    }

    /**
     * Inicializa los parametros para un input
     * @param stdClass $params Parametros precargados
     * @param string $name Name input
     * @param string $place_holder Label del input
     * @return stdClass|array
     */
    private function params_input2(stdClass $params, string $name,string $place_holder): stdClass|array
    {

        $name = trim($name);
        if(is_numeric($name)){
            return $this->error->error(mensaje: 'Error name debe ser un string no un numero', data: $name);
        }

        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->place_holder = $params->place_holder ?? $place_holder;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($place_holder));
        $data->required = $params->required ?? true;
        $data->regex = $params->regex ?? '';

        $data = $this->params_base(data: $data, name: $name ,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Inicializa los parametros de un select
     * @param string $name_model Nombre del modelo
     * @param stdClass $params Parametros inicializados
     * @return stdClass|array
     * @version 0.95.32
     */
    public function params_select(string $name_model, stdClass $params): stdClass|array
    {
        $name_model = trim($name_model);
        if($name_model === ''){
            return $this->error->error(mensaje: 'Error $name_model esta vacio', data: $name_model);
        }
        $data = new stdClass();

        $data->cols = $params->cols ?? 12;
        $data->place_holder = $params->place_holder ?? $name_model;
        $data->label = $params->label ?? str_replace('_',' ', strtoupper($name_model));
        $data->columns_ds = $params->columns_ds ?? array();

        $data = $this->params_base(data: $data, name : $name_model,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }

        return $data;
    }

    /**
     * Ajusta los parametros
     * @param stdClass $params Parametros precargados
     * @param string $label Etiqueta a mostrar en input
     * @return stdClass|array
     * @version 0.212.37
     */
    public function params_select_col_6(stdClass $params, string $label): stdClass|array
    {
        $label = trim($label);
        if($label === ''){
            return $this->error->error(mensaje: 'Error label esta vacio', data: $label);
        }
        $data = new stdClass();
        $data->cols = $params->cols ?? 6;
        $data->label = $params->label ?? str_replace('_',' ', $label);
        $data->label = str_replace('  ', ' ', $data->label);
        $data->key_descripcion_select = $params->key_descripcion_select ?? '';
        $data->columns_ds = $params->columns_ds ?? array();


        $data = $this->params_base(data: $data,name:  $label,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar params', data: $data);
        }


        return $data;
    }

    /**
     * Integra los parametros para inputs
     * @param string $item Row
     * @param array $keys_selects keys con datos de inputs
     * @return array|stdClass
     */
    final public function params_select_init(string $item, array $keys_selects): array|stdClass
    {

        $item = trim($item);
        if(is_numeric($item)){
            return $this->error->error(mensaje: 'Error item debe ser un string no un numero', data: $item);
        }

        $params_select = new stdClass();
        if (array_key_exists($item, $keys_selects) ){
            $params_select = $keys_selects[$item];
        }
        $params_select = $this->params_input2(params: $params_select,name: $item,place_holder: $item);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $params_select);
        }

        return $params_select;
    }


}
