<?php
namespace tests\src;


use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\controllers\controlador_adm_session;
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

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

        $_GET['accion'] = 'lista';
        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_accion',descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

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

    #[NoReturn] public function test_get_link(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $_GET['adm_menu_id'] = -1;
        $html = new links_menu($this->link, -1);
        //$html = new liberator($html);



        $seccion = '';
        $accion = '';
        $valida_error = false;
        $resultado = $html->get_link($seccion,$accion,$valida_error);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;

        $seccion = 'adm_session';
        $accion = 'inicio';
        $valida_error = true;
        $resultado = $html->get_link($seccion,$accion,$valida_error);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=inicio&adm_menu_id=-1&session_id=1", $resultado);

        errores::$error = false;



    }

    #[NoReturn] public function test_con_id(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $_GET['adm_menu_id'] = 5;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        errores::$error = false;

        $accion = 'modifica';
        $registro_id = '-1';
        $seccion = 'c';
        $resultado = $html->con_id($accion,$this->link,$registro_id,$seccion);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=inicio&adm_menu_id=5&session_id=1", $resultado->adm_session->inicio);
        errores::$error = false;
    }

    #[NoReturn] public function test_genera_links(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $_GET['adm_menu_id'] = -1;
        $html = new links_menu($this->link, -1);
        //$html = new liberator($html);


        $controler = new controlador_adm_session($this->link,$this->paths_conf);


        $resultado = $html->genera_links($controler);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=inicio&adm_menu_id=-1&session_id=1", $resultado->adm_session->inicio);

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

    #[NoReturn] public function test_init_data_link(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $controler = new controlador_adm_session($this->link,$this->paths_conf);
        $registro = array();
        $registro['adm_accion_descripcion'] = 'x';

        $resultado = $html->init_data_link($controler,$registro);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=inicio&adm_menu_id=-1&session_id=1", $resultado->adm_session->inicio);
        errores::$error = false;
    }

    #[NoReturn] public function test_init_links(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $controler = new controlador_adm_session($this->link,$this->paths_conf);
        $acciones = new stdClass();


        $resultado = $html->init_links($acciones,$controler);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

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


    #[NoReturn] public function test_integra_links(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $controler = new controlador_adm_session($this->link,$this->paths_conf);
        $acciones = new stdClass();



        $resultado = $html->integra_links($acciones,$controler);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    #[NoReturn] public function test_link_alta_bd(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        //$html = new liberator($html);


        $seccion = 'adm_seccion';
        $resultado = $html->link_alta_bd($this->link,$seccion);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
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

    #[NoReturn] public function test_links_con_id(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $registro_id = '-1';
        $accion = '';

        $resultado = $html->links_con_id($accion,$this->link,$registro_id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=logout&session_id=1", $resultado->adm_session->logout);
        errores::$error = false;
    }


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

    #[NoReturn] public function test_liga(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = '1';
        $_GET['adm_menu_id'] = 5;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        $accion = '';
        $registro_id = -1;
        $seccion = '';
        $tengo_permiso = false;
        $resultado = $html->liga($accion,$registro_id,$seccion,$tengo_permiso);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;

        $accion = 'a';
        $registro_id = -1;
        $seccion = 's';
        $tengo_permiso = true;
        $resultado = $html->liga($accion,$registro_id,$seccion,$tengo_permiso);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=s&accion=a&registro_id=-1&session_id=1&adm_menu_id=5", $resultado);
        errores::$error = false;
    }

    #[NoReturn] public function test_liga_completa(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        $accion = 'b';
        $adm_menu_id = -1;
        $registro_id = -1;
        $seccion = 'a';

        $resultado = $html->liga_completa($accion, $adm_menu_id, $registro_id, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=a&accion=b&registro_id=-1&session_id=1&adm_menu_id=-1", $resultado);
        errores::$error = false;
    }

    #[NoReturn] public function test_liga_con_permiso(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = '1';
        $_GET['adm_menu_id'] = 5;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        $accion = 'b';
        $registro_id = -1;
        $seccion = 'a';

        $resultado = $html->liga_con_permiso($accion,$registro_id,$seccion);
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=a&accion=b&registro_id=-1&session_id=1&adm_menu_id=5", $resultado);
        errores::$error = false;
    }

    #[NoReturn] public function test_link(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $accion = 'a';
        $registro_id = '-1';
        $seccion = 'a';
        $resultado = $html->link($accion,$this->link,$registro_id,$seccion);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;
    }

    #[NoReturn] public function test_link_init(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $seccion = 'adm_session';
        $accion = 'inicio';
        $registro_id = -1;
        $resultado = $html->link_init($this->link,$seccion,$accion,$registro_id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("./index.php?seccion=adm_session&accion=logout&session_id=1", $resultado->adm_session->logout);
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
        $_GET['seccion'] = 'adm_accion';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);


        $accion = 'lista';
        $resultado = $html->links_sin_id($accion, $this->link);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString( $resultado->adm_session->inicio);
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

    #[NoReturn] public function test_seccion(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link, -1);
        $html = new liberator($html);

        $html_ = new html();
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->seccion($controler);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion',$resultado);
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

    public function test_valida_link(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link,-1);
        $html = new liberator($html);

        $accion = 'a';
        $seccion = 'b';
        $resultado = $html->valida_link($accion, $seccion);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_var_gets(): void
    {
        errores::$error = false;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $html = new links_menu($this->link,-1);
        $html = new liberator($html);

        $params_get = array();
        $resultado = $html->var_gets($params_get);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);
        errores::$error = false;

        $params_get = array();
        $params_get[] = '';
        $resultado = $html->var_gets($params_get);
        //print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error value esta vacio',$resultado['mensaje_limpio']);
        errores::$error = false;

        errores::$error = false;

        $params_get = array();
        $params_get[] = 'x';
        $resultado = $html->var_gets($params_get);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('&0=x',$resultado);
        errores::$error = false;
    }

}

