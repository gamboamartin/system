<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\system;



use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class _ctl_parent_sin_codigo extends _ctl_parent {

    protected _ctl_parent_sin_codigo $parent_ctl;

    public function __construct(html_controler $html, PDO $link, modelo $modelo, links_menu $obj_link,
                                stdClass $datatables = new stdClass(), array $filtro_boton_lista = array(),
                                string $campo_busca = 'registro_id', string $valor_busca_fault = '',
                                stdClass $paths_conf = new stdClass())
    {
        parent::__construct(html: $html,link:  $link,modelo:  $modelo,obj_link:  $obj_link,datatables:  $datatables,
            filtro_boton_lista:  $filtro_boton_lista,campo_busca:  $campo_busca,valor_busca_fault:  $valor_busca_fault,
            paths_conf:  $paths_conf);


        $this->parent_ctl = $this;

    }


    public function alta(bool $header, bool $ws = false): array|string
    {

        $r_alta = $this->init_alta();
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al inicializar alta',data:  $r_alta, header: $header,ws:  $ws);
        }

        $keys_selects['descripcion'] = new stdClass();
        $keys_selects['descripcion']->cols = 12;

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs',data:  $inputs, header: $header,ws:  $ws);
        }

        return $r_alta;
    }

    public function modifica(bool $header, bool $ws = false, array $keys_selects = array()): array|stdClass
    {
        $keys_selects = array();
        $keys_selects['codigo'] = new stdClass();
        $keys_selects['codigo']->disabled = true;
        $r_modifica = parent::modifica(header: false, keys_selects: $keys_selects); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar template',data:  $r_modifica,header: $header,ws: $ws);
        }
        return $r_modifica;

    }


}
