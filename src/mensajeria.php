<?php
/**
 * Inicializa los mensajes de error warning etc definido sen variable SESSION[mensajes]
 * @author Martin Gamboa Vazquez
 * @version 0.21.2
 */
namespace gamboamartin\system;
use base\controller\controlador_base;
use gamboamartin\errores\errores;
use html\directivas;
use stdClass;

class mensajeria{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Inicializa los mensajes a mostrar en views
     * @param controlador_base $controler Controlador en ejecucion
     * @return array|stdClass
     * @version 0.20.1
     */
    public function init_mensajes(controlador_base $controler): array|stdClass
    {
        $mensaje_exito = (new directivas())->mensaje_exito(mensaje_exito: $controler->mensaje_exito);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar alerta', data: $mensaje_exito);
        }

        $mensaje_warning = (new directivas())->mensaje_warning(mensaje_warning: $controler->mensaje_warning);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar alerta', data: $mensaje_warning);
        }

        $data = new stdClass();

        $data->exito = $mensaje_exito;
        $data->warning = $mensaje_warning;

        $controler->mensaje_exito = $mensaje_exito;
        $controler->mensaje_warning = $mensaje_warning;

        return $data;
    }

}
