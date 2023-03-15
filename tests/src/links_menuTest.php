<?php
namespace tests\src;


use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JetBrains\PhpStorm\NoReturn;

use JsonException;
use stdClass;


class links_menuTest extends test {
    public errores $errores;
    private stdClass $paths_conf;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/system/config/generales.php';
        $this->paths_conf->database = '/var/www/html/system/config/database.php';
        $this->paths_conf->views = '/var/www/html/system/config/views.php';


    }

    #[NoReturn] public function test_adm_menu_id(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        errores::$error = false;

        $resultado = $html->adm_menu_id();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1, $resultado);

        errores::$error = false;
        $_GET['adm_menu_id'] = 5;
        $resultado = $html->adm_menu_id();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(5, $resultado);
        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_alta(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $seccion = 'a';
        $resultado = $html->alta($this->link, $seccion);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("", $resultado);


        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_alta_bd(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $seccion = 'a';
        $resultado = $html->alta_bd($this->link, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;
    }

    #[NoReturn] public function test_asigna_seccion(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        errores::$error = false;

        $html_ = new html();
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->asigna_seccion($controler);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("adm_accion", $resultado);
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
        $html = new links_menu($this->link, -1);
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

    #[NoReturn] public function test_init_tabla(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        errores::$error = false;

        $html_ = new html();
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->init_tabla($controler);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("adm_accion", $resultado);
        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_link_con_id(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        //$html = new liberator($html);


        $seccion = 'a';
        $registro_id = '-1';
        $accion = 'b';

        $resultado = $html->link_con_id($accion, $this->link, $registro_id, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
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
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        //$html = new liberator($html);

        $html_ = new html();
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->init_link_controller($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString( $resultado->adm_accion->lista);
        $this->assertIsString( $resultado->adm_accion->modifica);
        $this->assertIsString( $resultado->adm_accion->alta);
        $this->assertIsString( $resultado->adm_accion->alta_bd);
        $this->assertIsString( $resultado->adm_accion->modifica_bd);
        $this->assertIsString( $resultado->adm_accion->elimina_bd);

        errores::$error = false;
    }

    /**
     */
    #[NoReturn] public function test_link_alta(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $seccion = 'a';
        $resultado = $html->link_alta($this->link, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;
    }



    /**
     */
    #[NoReturn] public function test_links_sin_id(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $accion = 'lista';
        $resultado = $html->links_sin_id($accion, $this->link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString( $resultado->adm_accion->modifica);
        errores::$error = false;
    }

    #[NoReturn] public function test_lista(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        $link = $this->link;
        $seccion = 'a';
        $resultado = $html->lista($link, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
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
        $html = new links_menu($this->link,-1);
        $html = new liberator($html);


        $resultado = $html->sin_id(accion: 'lista', link: $this->link, seccion: 'a');
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

}

