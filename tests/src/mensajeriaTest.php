<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\controllers\controlador_adm_sistema;
use gamboamartin\controllers\controlador_sistema;
use gamboamartin\errores\errores;
use gamboamartin\system\mensajeria;
use gamboamartin\template\html;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class mensajeriaTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/system/config/generales.php';
        $this->paths_conf->database = '/var/www/html/system/config/database.php';
        $this->paths_conf->views = '/var/www/html/system/config/views.php';
    }

    /**
     * @throws JsonException
     */
    public function test_init_mensajes(): void
    {
        errores::$error = false;
        $msj = new mensajeria();

        $_GET['seccion'] = 'adm_seccion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $_GET['accion'] = 'lista';
        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_seccion',descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        $controler = new controlador_adm_seccion(link: $this->link, paths_conf: $this->paths_conf);
        //$inicializacion = new liberator($inicializacion);
        $html = new html();
        $resultado = $msj->init_mensajes($controler,$html);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $msj = new mensajeria();

        $_GET['seccion'] = 'adm_seccion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $_SESSION['exito'][]['mensaje'] = 'a';

        $_GET['accion'] = 'lista';
        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_seccion',descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        $controler = new controlador_adm_seccion(link: $this->link, paths_conf: $this->paths_conf);
        //$inicializacion = new liberator($inicializacion);

        $resultado = $msj->init_mensajes($controler,$html);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='alert alert-success' role='alert'><strong>Muy bien!</strong>", $resultado->exito);


        errores::$error = false;
    }







}

