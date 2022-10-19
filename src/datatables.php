<?php
namespace gamboamartin\system;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use SebastianBergmann\CodeUnit\FunctionUnit;
use stdClass;

class datatables{
    private errores $error;
    private validacion $valida;
    public function __construct(){
        $this->error = new errores();
        $this->valida = new validacion();
    }

    /**
     * Inicializa una columna
     * @param array|string $column Columna a inicializar
     * @param string $indice Indice de row
     * @return stdClass|array
     * @version 0.144.33
     */
    private function column_init(array|string $column, string $indice): stdClass|array
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = new stdClass();
        $column_obj->title = is_string($column)? $column:$indice;
        $column_obj->data = $indice;
        return $column_obj;
    }

    /**
     * Integra el titulo de columna
     * @param array $column Columna a integrar titulo
     * @param stdClass $column_obj Conjunto de datos de retorno
     * @param string $indice Key del elemento a integrar
     * @return stdClass|array
     * @version 0.146.34
     */
    private function column_titulo(array $column, stdClass $column_obj, string $indice): stdClass|array
    {
        $keys = array('titulo');
        $valida = $this->valida->valida_existencia_keys(keys: $keys,registro:  $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar column', data:  $valida);
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }
        $column_obj->title = $indice;
        if(is_string($column["titulo"])){
            $column_obj->title = $column["titulo"];
        }
        return$column_obj;
    }

    /**
     * Genera columnas para datatable
     * @param array $columns Columnas
     * @param array $datatable Objeto inicializado
     * @return array
     * @version 0.150.33
     */
    public function columns(array $columns, array $datatable): array
    {

        $index_button = -1;

        foreach ($columns as $indice => $column){

            $valida = $this->valida_base(column: $column,indice:  $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
            }

            $data = $this->genera_column(column: $column,columns:  $columns,datatable:  $datatable,
                indice:  $indice, index_button: $index_button);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column', data:  $data);
            }
            $datatable = $data->datatable;
            $index_button = $data->index_button;

        }
        return $datatable;
    }

    /**
     * Genera las columnas para datatables
     * @param array|string $column Columna
     * @param string $indice Indice o key
     * @param int $targets n columna
     * @param string $type Typo button or text
     * @return stdClass|array
     * @version 0.143.33
     */
    private function columns_defs(array|string $column, string $indice, int $targets, string $type): stdClass|array
    {

        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $rendered = $this->rendered(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener rendered', data:  $rendered);
        }
        $type = trim($type);
        if($type === ''){
            $type = 'text';
        }


        $columns_defs_obj = new stdClass();
        $columns_defs_obj->targets = $targets;
        $columns_defs_obj->data = null;
        $columns_defs_obj->type = $type;
        $columns_defs_obj->rendered = $rendered;

        array_unshift($columns_defs_obj->rendered,$indice);

        return $columns_defs_obj;
    }

    /**
     * Genera una columna para datatable
     * @param array|string $column Columna a integrar
     * @param array $columns Columnas
     * @param array $datatable obj inicializado de controler
     * @param string $indice indice de columna
     * @param int $index_button Index o posicion
     * @return array|stdClass
     * @version 0.149.33
     */
    private function genera_column(array|string $column, array $columns, array $datatable, string $indice, int $index_button): array|stdClass
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->maqueta_column_obj(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }

        $datatable["columns"][] = $column_obj;

        $indice_columna = array_search($indice, array_keys($columns));

        $type = $this->type(column: $column);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar type', data:  $type);
        }

        $targets = $indice_columna === count($columns) ? $index_button:$indice_columna;

        $columnDefs_obj = $this->columns_defs(column: $column, indice: $indice, targets: $targets, type: $type);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnDefs', data:  $columnDefs_obj);
        }

        $datatable["columnDefs"][] = $columnDefs_obj;

        if ($type === 'button'){
            $index_button -= 1;
        }

        $data = new stdClass();
        $data->datatable = $datatable;
        $data->index_button = $index_button;
        return $data;
    }

    /**
     * Integra el titulo en ele objeto de columna a generar
     * @param array|string $column Columna data
     * @param stdClass $column_obj Columnas de retorno inicializadas
     * @param string $indice Key del row de datos
     * @return array|stdClass
     * @version 0.146.33
     */
    private function integra_titulo(array|string $column, stdClass $column_obj, string $indice): array|stdClass
    {
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice esta vacio', data:  $indice);
        }

        if (is_array($column) && array_key_exists("titulo",$column)){

            $column_obj = $this->column_titulo(column: $column, column_obj: $column_obj, indice: $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
            }
        }
        return $column_obj;
    }

    /**
     * Maqueta una columna a integrar
     * @param array|string $column Columna
     * @param string $indice Key
     * @return array|stdClass
     * @version 0.147.33
     */
    private function maqueta_column_obj(array|string $column, string $indice): array|stdClass
    {
        $valida = $this->valida_base(column: $column,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data:  $valida);
        }

        $column_obj = $this->column_init(column: $column, indice: $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column', data:  $column_obj);
        }

        $column_obj = $this->integra_titulo(column: $column, column_obj: $column_obj,indice:  $indice);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar column title', data:  $column_obj);
        }
        return $column_obj;
    }

    private function rendered(string|array $column): array
    {
        $rendered = [];
        if(is_array($column)){
            if(array_key_exists('campos', $column)){

                if(!is_array($column['campos'])){
                    return $this->error->error(mensaje: 'Error $column[campos] debe seer un array', data:  $column);
                }

                $rendered = array_values($column["campos"]);
            }
        }
        return $rendered;
    }

    /**
     * Obtiene el type data
     * @param array|string $column Columna a validar
     * @return string
     * @version 0.148.33
     */
    private function type(array|string $column): string
    {
        $type = 'text';
        if(is_array($column) && array_key_exists("type",$column) && $column["type"] === "button"){
            $type = $column["type"];
        }
        return $type;
    }

    private function valida_base(array|string $column, string $indice): bool|array
    {
        if(is_string($column)){
            $column = trim($column);
            if($column === ''){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        if(is_array($column)){
            if(count($column) === 0){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice no puede venir vacia', data:  $indice);
        }
        return true;
    }
}
