<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_seccion;
use stdClass;


class actionsTest extends test {
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

    public function test_asigna_link_row(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);

        $accion = 'a';
        $indice = '0';
        $link = 'a';
        $registros_view = array();
        $row = new stdClass();
        $style = 'a';
        $resultado = $act->asigna_link_row($accion, $indice, $link, $registros_view, $row, $style);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado[0]->link_a);
        errores::$error = false;
    }

    public function test_asigna_link_rows(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);
        $_GET['session_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $seccion = 'adm_seccion';
        $obj_link = new links_menu($this->link, -1);
        $row = new stdClass();
        $row->adm_seccion_id = '1';

        $indice = 0;
        $accion = 'elimina_bd';
        $style = 'a';
        $registros_view = array();



        $resultado = $act->asigna_link_rows(accion: $accion,indice:  $indice, link: $this->link,
            obj_link:  $obj_link,registros_view:  $registros_view,row:  $row,seccion:  $seccion, style: $style);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]->adm_seccion_id);
        $this->assertEquals('./index.php?seccion=adm_seccion&accion=elimina_bd&registro_id=1&session_id=1', $resultado[0]->link_elimina_bd);
        $this->assertEquals('a', $resultado[0]->elimina_bd_style);
        errores::$error = false;

    }

    public function test_init_alta_bd(): void
    {
        errores::$error = false;
        $act = new actions();
        //$act = new liberator($act);
        $_GET['session_id'] = 1;


        $resultado = $act->init_alta_bd();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('modifica', $resultado);
        errores::$error = false;
    }

    public function test_key_id(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);

        $seccion = 'a';
        $resultado = $act->key_id($seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado);
        errores::$error = false;
    }

    public function test_limpia_butons(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);
        $_GET['session_id'] = 1;

        $resultado = $act->limpia_butons();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
        $_POST['guarda'] = 'X';
        $resultado = $act->limpia_butons();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
        $_POST['guarda_otro'] = 'X';
        $resultado = $act->limpia_butons();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_link_accion(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $seccion = 'adm_menu';
        $obj_link = new links_menu($this->link, -1);
        $row = new stdClass();
        $row->a = '1';
        $key_id = 'a';
        $accion = 'elimina_bd';

        $resultado = $act->link_accion($accion, $key_id, $this->link, $obj_link, $row, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('./index.php?seccion=adm_menu&accion=elimina_bd&registro_id=1&session_id=1', $resultado);
        errores::$error = false;
    }

    /**
     */
    public function test_retorno_alta_bd(): void
    {
        errores::$error = false;
        $act = new actions();
        //$act = new liberator($act);
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $registro_id = -1;
        $seccion = 'adm_accion_grupo';
        $siguiente_view = '';

       // $links = new links_menu($this->link, $registro_id);

        $resultado = $act->retorno_alta_bd($this->link, -1, $seccion, $siguiente_view);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('./index.php?seccion=adm_accion_grupo&accion=modifica&registro_id=-1&session_id=1', $resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_siguiente_view(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);

        $resultado = $act->siguiente_view();

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('modifica', $resultado);

        errores::$error = false;
        $_POST['guarda_otro'] = '';
        $resultado = $act->siguiente_view();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('alta', $resultado);
        errores::$error = false;


    }

    public function test_style(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);
        $_GET['session_id'] = 1;
        $row = new stdClass();

        $accion = 'a';
        $seccion = 'v';
        $row->v_a = 'x';

        $resultado = $act->style($accion, $row, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('danger', $resultado);
        errores::$error = false;
    }







}

