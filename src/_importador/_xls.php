<?php
namespace gamboamartin\system\_importador;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\errores\errores;
use gamboamartin\plugins\Importador;
use gamboamartin\system\html_controler;
use PDO;

class _xls
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    /**
     * POR DOCUMENTAR EN WIKI ERROR FIN
     * Función columna_calc_def
     *
     * Esta función toma un valor de string de entrada '$columna_cal' de una hoja de calculo es el nombre de la columna.
     * Primero, elimina los espacios en blanco al principio y al final del string usando la función trim() de PHP.
     * Si '$columna_cal' está vacío después de quitar todo el espacio en blanco, la función devolverá un error utilizando la función 'error' del objeto 'error'.
     *
     * Si '$columna_cal' no está vacío, la función continua para definir un array '$columna_calc_def' que contendrá dos elementos:
     * 'value' que almacena el valor de '$columna_cal' y 'descripcion_select' que también almacena el valor de '$columna_cal'.
     *
     * Finalmente, la función devuelve el array '$columna_calc_def'.
     *
     * @param string $columna_cal El valor de la columna que se va a integrar proveniente de una hoja de calculo de la primer fila.
     * @return array $columna_calc_def Array que contiene el valor y la descripción select de la columna que se va a calcular.
     * @version 20.14.0
     */
    private function columna_calc_def(string $columna_cal): array
    {
        $columna_cal = trim($columna_cal);
        if($columna_cal === ''){
            return $this->error->error(mensaje: 'Error columna_cal esta vacia',data:  $columna_cal, es_final: true);
        }
        $columna_calc_def = array();
        $columna_calc_def['value'] = $columna_cal;
        $columna_calc_def['descripcion_select'] = $columna_cal;

        return $columna_calc_def;
    }

    private function columnas_calc_def(array $columnas_calc): array
    {
        $columnas_calc_def = array();
        foreach ($columnas_calc as $columna_cal){
            $columnas_calc_def = $this->integra_columna_calc_def(columna_cal: $columna_cal,columnas_calc_def:  $columnas_calc_def);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columna',data:  $columnas_calc_def);
            }
        }
        return $columnas_calc_def;

    }

    final public function columnas_xls(string $ruta, html_controler $html_controler,PDO $link, string $tabla): array
    {
        $columnas_calc_def = $this->genera_columnas_calc_def(ruta: $ruta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columna',data:  $columnas_calc_def);
        }

        $columnas_xls = $this->genera_inputs_importa(columnas_calc_def: $columnas_calc_def,html_controler: $html_controler,link: $link,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas_xls', data: $columnas_xls);
        }
        return $columnas_xls;
    }

    private function genera_columnas_calc_def(string $ruta): array
    {
        $columnas_calc = (new Importador())->primer_row(celda_inicio: 'A1',ruta_absoluta: $ruta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas_calc',data:  $columnas_calc);
        }

        $columnas_calc_def = $this->columnas_calc_def(columnas_calc: $columnas_calc);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columna',data:  $columnas_calc_def);
        }
        return $columnas_calc_def;

    }

    private function genera_inputs_importa(array $columnas_calc_def, html_controler $html_controler, PDO $link, string $tabla): array
    {
        $adm_campos = (new _campos())->adm_campos(link: $link, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos',data:  $adm_campos);
        }

        $columnas_xls = $this->inputs_selects_importa(adm_campos: $adm_campos, columnas_calc_def: $columnas_calc_def, html_controler: $html_controler, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas_xls', data: $columnas_xls);
        }
        return $columnas_xls;

    }

    private function inputs_selects_importa(array $adm_campos, array $columnas_calc_def, html_controler $html_controler, PDO $link): array
    {
        $columnas_xls = array();
        $modelo_adm_campo = new adm_campo(link: $link);

        foreach ($adm_campos as $adm_campo){

            $input = $html_controler->select_catalogo(cols: 12, con_registros: false, id_selected: $adm_campo['adm_campo_descripcion'],
                modelo: $modelo_adm_campo, aplica_default: false, key_descripcion_select: 'descripcion_select',
                key_value_custom: 'value', label: $adm_campo['adm_campo_descripcion'], name: $adm_campo['adm_campo_descripcion'], registros: $columnas_calc_def);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar input', data: $input);
            }
            $columnas_xls[$adm_campo['adm_campo_descripcion']] = $input;

        }
        return $columnas_xls;

    }

    private function integra_columna_calc_def(string $columna_cal, array $columnas_calc_def): array
    {
        $columna_calc_def = $this->columna_calc_def(columna_cal: $columna_cal);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columna',data:  $columna_calc_def);
        }
        $columnas_calc_def[] = $columna_calc_def;

        return $columnas_calc_def;

    }

}
