<?php
namespace gamboamartin\system\_importador;
use base\orm\modelo;
use config\generales;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\plugins\exportador;
use PDO;
use stdClass;

class _exporta
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    private function campos_hd(array $adm_campo, array $campos_hd, stdClass $foraneas)
    {
        $nombre_tabla_relacion = $this->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre_tabla_relacion', data: $nombre_tabla_relacion);
        }

        $campos_hd[] = $nombre_tabla_relacion.'_codigo';
        $campos_hd[] = $nombre_tabla_relacion;

        return $campos_hd;

    }

    private function celda_busqueda(array $campos_hd, array $letras, string $nombre_tabla_relacion): string
    {
        $celda_busqueda = 'A2';
        foreach ($campos_hd as $indice=>$campo_hd){
            if($campo_hd === $nombre_tabla_relacion.'_id'){
                $letra = $letras[$indice];
                $celda_busqueda = $letra.'2';
                break;
            }
        }
        return $celda_busqueda;

    }

    private function columnas_rows(string $tabla): array
    {
        return array($tabla.'_id',$tabla.'_codigo', $tabla.'_descripcion');

    }

    private function data_hojas_xls(int $contador_hojas, stdClass $data_hojas, array $keys, string $nombre_tabla_relacion,
                                    array $registros): stdClass
    {

        if(isset($data_hojas->nombre_hojas)) {
            $nombre_hojas = $data_hojas->nombre_hojas;
        }
        if(isset($data_hojas->keys_hojas)) {
            $keys_hojas = $data_hojas->keys_hojas;
        }

        $nombre_hojas[$contador_hojas] = $nombre_tabla_relacion;

        $nombre_hojas = array_reverse($nombre_hojas);

        $keys_hojas[$nombre_tabla_relacion] = new stdClass();
        $keys_hojas[$nombre_tabla_relacion]->keys = $keys;
        $keys_hojas[$nombre_tabla_relacion]->registros = $registros;

        $data_hojas->nombre_hojas = $nombre_hojas;
        $data_hojas->keys_hojas = $keys_hojas;

        return $data_hojas;



    }

    PUBLIC function datos_full_xls(modelo $modelo)
    {
        $params = $this->params_xls(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener params', data: $params);
        }

        $data_hojas = $this->datos_para_xls(adm_campos: $params->adm_campos,foraneas:  $params->foraneas,modelo:  $modelo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
        }
        $name = $modelo->tabla;
        $path_base = (new generales())->path_base;

        $data_hojas->name = $name;
        $data_hojas->path_base = $path_base;

        return $data_hojas;

    }

    private function datos_para_xls(array $adm_campos, stdClass $foraneas, modelo $modelo)
    {
        $data_hojas = $this->datos_xls(adm_campos: $adm_campos,foraneas:  $foraneas,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
        }

        $data_hojas = $this->integra_hoja_plantilla(data_hojas: $data_hojas,modelo:  $modelo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
        }
        return $data_hojas;

    }

    private function datos_plantilla(array $adm_campo, array $campos_hd, stdClass $foraneas, array $letras, array $registros_plantilla)
    {

        $nombre_tabla_relacion = $this->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre_tabla_relacion', data: $nombre_tabla_relacion);
        }
        $campos_hd = $this->campos_hd(adm_campo: $adm_campo, campos_hd: $campos_hd, foraneas: $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar campo_hd', data: $campos_hd);
        }

        $registros_plantilla = $this->registros_plantilla(adm_campo: $adm_campo,
            campos_hd:  $campos_hd,foraneas:  $foraneas,letras:  $letras,registros_plantilla:  $registros_plantilla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener vlookup', data: $registros_plantilla);
        }

        $datos = new stdClass();
        $datos->nombre_tabla_relacion = $nombre_tabla_relacion;
        $datos->campos_hd = $campos_hd;
        $datos->registros_plantilla = $registros_plantilla;

        return $datos;

    }

    private function datos_xls(array $adm_campos, stdClass $foraneas, modelo $modelo)
    {
        $contador_hojas = 0;
        $campos_hd = array();
        $registros_plantilla = array();
        $letras = $modelo->letras;
        $data_hojas = new stdClass();

        foreach ($adm_campos as $adm_campo){

            $data_adm_campo = $this->limpia_adm_campo(adm_campo: $adm_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar adm_campo', data: $data_adm_campo);
            }
            $adm_campo = $data_adm_campo->adm_campo;
            if($data_adm_campo->continue){
                continue;
            }

            $data_hojas = $this->integra_catalogo(adm_campo: $adm_campo,campos_hd:  $campos_hd,
                contador_hojas:  $contador_hojas,data_hojas:  $data_hojas,foraneas:  $foraneas,letras:  $letras,
                link:  $modelo->link,registros_plantilla:  $registros_plantilla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
            }

            $campos_hd = $data_hojas->campos_hd;
            $registros_plantilla = $data_hojas->registros_plantilla;
            $contador_hojas = $data_hojas->contador_hojas;
        }
        return $data_hojas;

    }

    private function frm_vlookup(array $campos_hd, array $letras, string $nombre_tabla_relacion)
    {
        $celda_busqueda = $this->celda_busqueda(campos_hd: $campos_hd,letras:  $letras,nombre_tabla_relacion:  $nombre_tabla_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener celda_busqueda', data: $celda_busqueda);
        }

        $vlookup = $this->vlookup(celda_busqueda: $celda_busqueda,nombre_tabla_relacion:  $nombre_tabla_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener vlookup', data: $vlookup);
        }
        return $vlookup;


    }

    private function integra_catalogo(array $adm_campo, array $campos_hd, int $contador_hojas,
                                      stdClass $data_hojas, stdClass $foraneas, array $letras, PDO $link, array $registros_plantilla)
    {
        $campos_hd[] = $adm_campo['adm_campo_descripcion'];

        if($adm_campo['adm_campo_es_foranea'] === 'activo'){

            $data_hojas = $this->integra_data_hoja(adm_campo: $adm_campo,campos_hd:  $campos_hd,
                contador_hojas:  $contador_hojas,data_hojas:  $data_hojas,foraneas:  $foraneas,letras:  $letras,
                link:  $link,registros_plantilla:  $registros_plantilla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
            }
            $campos_hd = $data_hojas->campos_hd;
            $registros_plantilla = $data_hojas->registros_plantilla;
            $contador_hojas++;

        }
        $data_hojas->contador_hojas = $contador_hojas;
        $data_hojas->campos_hd = $campos_hd;
        $data_hojas->registros_plantilla = $registros_plantilla;
        return $data_hojas;

    }

    private function integra_data_hoja(array $adm_campo, array $campos_hd, int $contador_hojas,
                                       stdClass $data_hojas, stdClass $foraneas, array $letras, PDO $link, array $registros_plantilla)
    {
        $nombre_tabla_relacion = $this->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre_tabla_relacion', data: $nombre_tabla_relacion);
        }

        $datos_plantilla = $this->datos_plantilla(adm_campo: $adm_campo,campos_hd:  $campos_hd,
            foraneas:  $foraneas,letras:  $letras,registros_plantilla:  $registros_plantilla);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos_plantilla', data: $datos_plantilla);
        }

        $campos_hd = $datos_plantilla->campos_hd;
        $registros_plantilla = $datos_plantilla->registros_plantilla;

        $registros = $this->registros(adm_campo: $adm_campo,foraneas:  $foraneas,link:  $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }

        $data_hojas = $this->data_hojas_xls(contador_hojas: $contador_hojas, data_hojas: $data_hojas,
            keys: array('id','codigo','descripcion'), nombre_tabla_relacion: $nombre_tabla_relacion, registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
        }

        $data_hojas->campos_hd = $campos_hd;
        $data_hojas->registros_plantilla = $registros_plantilla;

        return $data_hojas;

    }

    private function integra_hoja_plantilla(stdClass $data_hojas, modelo $modelo)
    {
        $data_hojas = $this->integra_registro_plantilla(data_hojas: $data_hojas,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar registro plantilla', data: $data_hojas);
        }

        $data_hojas = $this->data_hojas_xls(contador_hojas: $data_hojas->contador_hojas, data_hojas: $data_hojas,
            keys: $data_hojas->campos_hd, nombre_tabla_relacion: $modelo->tabla, registros: $data_hojas->registros_plantilla
        );

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data_hojas', data: $data_hojas);
        }

        $data_hojas->contador_hojas++;

        return $data_hojas;

    }

    private function integra_registro_plantilla(stdClass $data_hojas, modelo $modelo): stdClass
    {
        $data_hojas->registros_plantilla[0]['descripcion'] = 'Descripcion de '.$modelo->tabla;
        $data_hojas->registros_plantilla[0]['codigo'] = "Codigo de $modelo->tabla(Debe ser único)";
        $data_hojas->registros_plantilla[0]['status'] = 'activo';
        $data_hojas->registros_plantilla[0]['alias'] = '=A2';
        $data_hojas->registros_plantilla[0]['descripcion_select'] = '=B2&" "&A2';
        $data_hojas->registros_plantilla[0]['codigo_bis'] = '=B2';

        return $data_hojas;

    }

    final public function layout(bool $header, modelo $modelo)
    {
        $params = $this->datos_full_xls(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al params data_hojas', data: $params);
        }

        $xls = (new exportador(num_hojas: $params->contador_hojas))->genera_xls(header: $header,name:  $params->name,nombre_hojas:  $params->nombre_hojas,
            keys_hojas:  $params->keys_hojas,path_base:  $params->path_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al exportar', data: $xls);
        }
        return $xls;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Limpia el campo del administrador.
     *
     * Esta función acepta un array de campos del administrador, verifica si la descripción del campo se encuentra
     * en la lista de campos que deben ser limpiados y devuelve un objeto.
     * Los campos que se buscan son 'id', 'usuario_alta_id', 'usuario_update_id', 'fecha_alta', 'fecha_update'.
     *
     * @param   array   $adm_campo Array de campos del administrador que deben ser limpiados.
     *
     * @return  stdClass Retorna un objeto con dos propiedades.
     * $continue se establece en true si el campo del administrador se encuentra en la lista de campos para limpiar, y false si no.
     * $adm_campo es el campo original del administrador recibido como parámetro.
     *
     * @version 21.3.0
     */
    private function limpia_adm_campo(array $adm_campo): stdClass
    {
        $continue = false;
        $keys_limpia = array('id','usuario_alta_id','usuario_update_id','fecha_alta','fecha_update');
        if(in_array($adm_campo['adm_campo_descripcion'], $keys_limpia)) {
            $continue = true;
        }
        $data = new stdClass();
        $data->continue = $continue;
        $data->adm_campo = $adm_campo;

        return $data;

    }

    private function nombre_tabla_relacion(array $adm_campo, stdClass $foraneas)
    {
        $campo_name = $adm_campo['adm_campo_descripcion'];
        $fk_info = $foraneas->$campo_name;
        return $fk_info->nombre_tabla_relacion;

    }

    private function params_xls(modelo $modelo)
    {
        $adm_campos = (new adm_campo(link: $modelo->link))->campos_by_seccion(adm_seccion_descripcion: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos', data: $adm_campos);
        }

        $foraneas = $modelo->get_foraneas();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener foraneas', data: $foraneas);
        }

        $data = new stdClass();
        $data->adm_campos = $adm_campos;
        $data->foraneas = $foraneas;

        return $data;

    }

    private function registros(array $adm_campo, stdClass $foraneas, PDO $link)
    {
        $nombre_tabla_relacion = $this->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre_tabla_relacion', data: $nombre_tabla_relacion);
        }

        $registros = $this->rows_rel(link: $link,nombre_tabla_relacion:  $nombre_tabla_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }
        return $registros;

    }

    private function registros_plantilla(array $adm_campo, array $campos_hd, stdClass $foraneas, array $letras, array $registros_plantilla)
    {
        $nombre_tabla_relacion = $this->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre_tabla_relacion', data: $nombre_tabla_relacion);
        }


        $vlookup = $this->frm_vlookup(campos_hd: $campos_hd,letras:  $letras,nombre_tabla_relacion:  $nombre_tabla_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener vlookup', data: $vlookup);
        }

        $registros_plantilla[0][$nombre_tabla_relacion] = $vlookup;
        $registros_plantilla[0][$nombre_tabla_relacion.'_id'] = "Identificador de entidad, (entero positivo)";

        return $registros_plantilla;

    }

    private function registros_rel(modelo $modelo_relacion)
    {
        $columnas = $this->columnas_rows(tabla: $modelo_relacion->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas);
        }

        $registros = $modelo_relacion->registros(columnas: $columnas,columnas_en_bruto: true, order: array('id'=>'ASC'));
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }
        return $registros;

    }

    private function rows_rel(PDO $link, string $nombre_tabla_relacion)
    {
        $modelo_relacion = (new adm_seccion(link: $link))->crea_modelo(adm_seccion_descricpion: $nombre_tabla_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener modelo_relacion', data: $modelo_relacion);
        }

        $registros = $this->registros_rel(modelo_relacion: $modelo_relacion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $registros);
        }
        return $registros;

    }

    private function vlookup(string $celda_busqueda, string $nombre_tabla_relacion): string
    {
        return "VLOOKUP($celda_busqueda,$nombre_tabla_relacion.A:C,3,0)";

    }




}
