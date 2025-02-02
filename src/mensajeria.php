<?php
/**
 * Inicializa los mensajes de error warning etc definido sen variable SESSION[mensajes]
 * @author Martin Gamboa Vazquez
 * @version 0.21.2
 */
namespace gamboamartin\system;
use base\controller\controlador_base;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use gamboamartin\template\html;

use stdClass;

class mensajeria{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * REG
     * Inicializa los mensajes de éxito y advertencia para ser mostrados en las vistas.
     *
     * Este método se encarga de generar los mensajes de éxito y advertencia a partir de los valores proporcionados en el controlador
     * (`$controler->mensaje_exito` y `$controler->mensaje_warning`). Utiliza la clase `directivas` para generar los mensajes correspondientes
     * y luego los almacena en la sesión o en el controlador para que puedan ser utilizados en las vistas.
     *
     * Si ocurre un error al generar los mensajes, la función devuelve un mensaje de error con detalles sobre el problema.
     *
     * @param controlador_base $controler El controlador que está en ejecución. Este parámetro se utiliza para acceder a los mensajes de éxito
     *                                     y advertencia que se desean inicializar.
     *                                     - `mensaje_exito` contiene el mensaje de éxito.
     *                                     - `mensaje_warning` contiene el mensaje de advertencia.
     *
     * @param html $html Instancia de la clase `html` que se utiliza para generar el HTML para los mensajes de éxito y advertencia.
     *                   Esta clase maneja la creación de los elementos de alerta en el HTML.
     *
     * @return array|stdClass Retorna un objeto de tipo `stdClass` que contiene los mensajes de éxito y advertencia generados.
     *                        En caso de error, se retorna un array con un mensaje de error.
     *
     * @throws errores Si ocurre un error durante el proceso de generación de los mensajes o al utilizar la clase `directivas`.
     *
     * @example
     * // Ejemplo de uso en un controlador:
     * $controler->mensaje_exito = "Operación realizada con éxito";
     * $controler->mensaje_warning = "Advertencia: La operación fue parcialmente exitosa";
     *
     * $mensajes = $mensajeria->init_mensajes($controler, $html);
     * echo $mensajes->exito;   // Resultado: "<div class='alert alert-success'>Operación realizada con éxito</div>"
     * echo $mensajes->warning; // Resultado: "<div class='alert alert-warning'>Advertencia: La operación fue parcialmente exitosa</div>"
     *
     * @example
     * // Caso con error:
     * $controler->mensaje_exito = "";
     * $mensajes = $mensajeria->init_mensajes($controler, $html);
     * echo $mensajes['mensaje']; // Resultado: "Error al generar alerta"
     *
     * @version 1.0.0
     */
    final public function init_mensajes(controlador_base $controler, html $html): array|stdClass
    {
        // Generación del mensaje de éxito utilizando la clase `directivas`
        $mensaje_exito = (new directivas(html: $html))->mensaje_exito(mensaje_exito: $controler->mensaje_exito);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar alerta', data: $mensaje_exito);
        }

        // Generación del mensaje de advertencia utilizando la clase `directivas`
        $mensaje_warning = (new directivas(html: $html))->mensaje_warning(mensaje_warning: $controler->mensaje_warning);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar alerta', data: $mensaje_warning);
        }

        // Se crea un objeto stdClass para almacenar los mensajes generados
        $data = new stdClass();

        // Asignación de los mensajes generados a las propiedades del objeto
        $data->exito = $mensaje_exito;
        $data->warning = $mensaje_warning;

        // Se asignan los mensajes al controlador para ser utilizados en la vista
        $controler->mensaje_exito = $mensaje_exito;
        $controler->mensaje_warning = $mensaje_warning;

        // Retorna el objeto con los mensajes
        return $data;
    }


}
