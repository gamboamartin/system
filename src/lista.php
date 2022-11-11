<?php
namespace gamboamartin\system;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;
use stdClass;

class lista{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    public function columnas_lista(array $keys_row_lista): array
    {
        $columnas = array();
        foreach ($keys_row_lista as $key_row_lista){
            $valida = $this->valida_key_rows_lista(key_row_lista: $key_row_lista);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
            }

            $columnas[] = $key_row_lista->campo;
        }
        return $columnas;
    }

    /**
     * Valida que los keys rows lista sean validos
     * @param mixed $key_row_lista Key a validar
     * @return bool|array
     * @version 0.125.33
     */
    private function valida_key_rows_lista(mixed $key_row_lista): bool|array
    {
        if(!is_object($key_row_lista)){
            return $this->error->error(mensaje: 'Error el key_row_lista debe ser un objeto', data:  $key_row_lista);
        }
        $keys = array('campo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $key_row_lista,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar key_row_lista', data:  $valida);
        }

        return true;
    }



}
