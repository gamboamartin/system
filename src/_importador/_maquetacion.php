<?php
namespace gamboamartin\system\_importador;

use base\controller\controler;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\plugins\Importador;
use gamboamartin\validacion\validacion;

class _maquetacion
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    private function checked(bool $existe_error): string
    {
        $checked = '';
        if(!$existe_error){
            $checked = 'checked';
        }
        return $checked;

    }

    private function existe_error(array $row_a_importar): bool
    {
        $existe_error = false;
        foreach ($row_a_importar as $dato){
            $keys = array('contexto');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $dato);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar dato', data: $valida);
            }
            if($dato['contexto'] === 'danger'){
                $existe_error = true;
                break;
            }
        }
        return $existe_error;

    }

    private function genera_checked(array $row_a_importar): array|string
    {
        $existe_error = $this->existe_error(row_a_importar: $row_a_importar);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe error', data: $existe_error);
        }

        $checked = $this->checked(existe_error: $existe_error);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar checked', data: $checked);
        }
        return $checked;

    }

    final public function genera_rows(controler $controler, string $ruta_absoluta)
    {
        $datos_calc = (new Importador())->leer(ruta_absoluta: $ruta_absoluta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al leer archivo',data:  $datos_calc);
        }
        $columnas_doc = (new Importador())->primer_row(celda_inicio: 'A1', ruta_absoluta: $ruta_absoluta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas_doc',data:  $columnas_doc);
        }
        $adm_campos = (new _xls())->adm_campos_inputs(columnas_doc: $columnas_doc,
            link:  $controler->link,tabla:  $controler->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos',data:  $adm_campos);
        }

        $rows_importa = (new _campos())->rows_importa(controler: $controler, rows_xls: $datos_calc->rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tipos de doc ',data:  $rows_importa);
        }

        $rows_importa_final = (new _maquetacion())->init_rows(adm_campos: $adm_campos,
            modelo_imp:  $controler->modelo,rows:  $rows_importa);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tipos de doc ',data:  $rows_importa_final);
        }

        return $rows_importa_final;
    }

    private function init_rows(array $adm_campos, modelo $modelo_imp, array $rows): array
    {
        $rows_final = array();
        foreach ($rows as $key=>$row){
            $rows_final = $this->integra_row_final(adm_campos: $adm_campos, key: $key, modelo_imp: $modelo_imp,
                row: $row, rows_final: $rows_final);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar rows_final',data:  $rows_final);
            }
        }
        return $rows_final;

    }

    private function input_checked(int $indice, array $row_a_importar): array|string
    {
        $checked = $this->genera_checked(row_a_importar: $row_a_importar);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar checked', data: $checked);
        }
        return "<input type='checkbox' name=row[$indice] $checked>";

    }

    final public function integra_chks(array $rows_final): array
    {

        foreach ($rows_final as $indice=>$row_a_importar){
            $input = $this->input_checked(indice: $indice,row_a_importar:  $row_a_importar);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar input', data: $input);
            }
            $rows_final[$indice]['selecciona'] = $input;
        }
        return $rows_final;

    }

    private function integra_row_final(array $adm_campos, string $key, modelo $modelo_imp,
                                       array $row, array $rows_final): array
    {

        $rows_final[$key] = array();
        foreach ($row as $campo_db=>$value) {
            $rows_final = $this->integra_row(adm_campos: $adm_campos, campo_db: $campo_db, key: $key,
                modelo_imp: $modelo_imp, rows_final: $rows_final, value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }
        return $rows_final;

    }

    private function integra_row(array $adm_campos, string $campo_db, string $key, modelo $modelo_imp,
                                 array $rows_final, string $value): array
    {
        $rows_final[$key][$campo_db]['value'] = $value;
        $tipo_dato = (new _campos())->tipo_dato_valida(adm_campos: $adm_campos,campo_db:  $campo_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tipo_dato',data:  $tipo_dato);
        }

        $rows_final = $this->por_tipo_dato(campo_db: $campo_db, key: $key, rows_final: $rows_final,
            tipo_dato: $tipo_dato, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
        }

        $rows_final = $this->por_duplicate(campo_db: $campo_db, key: $key, modelo_imp: $modelo_imp,
            rows_final: $rows_final, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
        }

        return $rows_final;

    }

    private function por_duplicate(string $campo_db, string $key, modelo $modelo_imp, array $rows_final,
                                   string $value): array
    {
        if($campo_db === 'id'){
            $existe = $modelo_imp->existe_by_id(registro_id: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe elemento',data:  $existe);
            }
            $valida = !$existe;

            $mensaje_error = 'Identificador duplicado';

            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db, contexto_error: 'danger', key: $key,
                mensaje: $mensaje_error, rows_finals: $rows_final, valida: $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar rows_final',data:  $rows_final);
            }

        }

        if($campo_db === 'codigo'){
            $existe = $modelo_imp->existe_by_codigo(codigo: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe elemento',data:  $existe);
            }
            $valida = !$existe;

            $mensaje_error = 'Codigo duplicado';

            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db, contexto_error: 'danger', key: $key,
                mensaje: $mensaje_error, rows_finals: $rows_final, valida: $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar rows_final',data:  $rows_final);
            }

        }

        if($campo_db === 'codigo_bis'){
            $filtro = array();
            $filtro[$modelo_imp->tabla.'.codigo_bis'] = $value;
            $existe = $modelo_imp->existe(filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe elemento',data:  $existe);
            }
            $valida = !$existe;

            $mensaje_error = 'Codigo Bis Duplicado';

            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db, contexto_error: 'danger', key: $key,
                mensaje: $mensaje_error, rows_finals: $rows_final, valida: $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }

        }
        return $rows_final;

    }

    private function por_tipo_dato(string $campo_db, string $key,  array $rows_final, string $tipo_dato, string $value): array
    {
        if($tipo_dato === 'BIGINT'){

            $valida = (new validacion())->id(txt: $value);
            $mensaje_error = 'Critico debe ser un entero positivo 1-999999999';

            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db, contexto_error: 'danger', key: $key,
                mensaje: $mensaje_error, rows_finals: $rows_final, valida: $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }

        if($tipo_dato === 'INT'){
            $valida = (new validacion())->id(txt: $value);
            $mensaje_error = 'Critico debe ser un entero positivo 1-999999999';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'danger',
                key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }

        }
        if($tipo_dato === 'VARCHAR'){
            $valida = $value!=='';
            $mensaje_error = 'Posible error po campo vacio';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'warning',key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }

        }
        if($tipo_dato === 'TIMESTAMP'){
            $valida = $value!=='';
            $mensaje_error = 'Posible error po campo vacio';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'warning',
                key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }
        if($tipo_dato === 'TIMESTAMP'){
            $valida = (new validacion())->valida_pattern(key: 'fecha', txt: $value);

            $mensaje_error = 'Error formato fecha';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'danger',
                key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }

        if($tipo_dato === 'DOUBLE'){
            $valida = (new validacion())->valida_pattern(key: 'double', txt: $value);

            $mensaje_error = 'Error formato double';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'danger',
                key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }

        if($tipo_dato === 'FLOAT'){
            $valida = (new validacion())->valida_pattern(key: 'double', txt: $value);

            $mensaje_error = 'Error formato double';
            $rows_final = (new _campos())->integra_row_final(campo_db: $campo_db,contexto_error: 'danger',
                key:  $key,mensaje:  $mensaje_error,
                rows_finals:  $rows_final,valida:  $valida);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar tipos_doc_final',data:  $rows_final);
            }
        }

        return $rows_final;

    }
}
