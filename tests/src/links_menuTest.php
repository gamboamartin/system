<?php
namespace tests\controllers;


use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JetBrains\PhpStorm\NoReturn;

use JsonException;
use models\adm_accion;
use stdClass;


class links_menuTest extends test {
    public errores $errores;
    private stdClass $paths_conf;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/cat_sat/config/generales.php';
        $this->paths_conf->database = '/var/www/html/cat_sat/config/database.php';
        $this->paths_conf->views = '/var/www/html/cat_sat/config/views.php';


    }

    /**
     */
    #[NoReturn] public function test_alta(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu(-1);
        $html = new liberator($html);


        $seccion = 'a';
        $resultado = $html->alta($seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("./index.php?seccion=a&accion=alta", $resultado);


        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_init_action(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu(-1);
        $html = new liberator($html);


        $seccion = 'c';
        $link = 'c';
        $accion = 'a';

        $resultado = $html->init_action($accion, $link, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("c", $resultado->c->a);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    #[NoReturn] public function test_init_link_controller(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu(-1);
        //$html = new liberator($html);

        $html_controler = new html_controler();

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->init_link_controller($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=lista&session_id=1", $resultado->adm_accion->lista);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=modifica&registro_id=-1&session_id=1", $resultado->adm_accion->modifica);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=alta&session_id=1", $resultado->adm_accion->alta);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=alta_bd&session_id=1", $resultado->adm_accion->alta_bd);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=modifica_bd&registro_id=-1&session_id=1", $resultado->adm_accion->modifica_bd);
        $this->assertEquals("./index.php?seccion=adm_accion&accion=elimina_bd&registro_id=-1&session_id=1", $resultado->adm_accion->elimina_bd);

        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_link_alta(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu(-1);
        $html = new liberator($html);


        $seccion = 'a';
        $resultado = $html->link_alta($seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=a&accion=alta&session_id=1", $resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    #[NoReturn] public function test_sin_id(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu(-1);
        $html = new liberator($html);


        $resultado = $html->sin_id('a', 'lista');
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

}

