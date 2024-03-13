<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class actionsTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $del = (new adm_sistema($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion_basica($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion_basica['id'] = 1;
        $adm_accion_basica['descripcion'] = 'elimina_bd';
        $adm_accion_basica['muestra_icono_btn'] = 'inactivo';
        $adm_accion_basica['muestra_titulo_btn'] = 'activo';
        $adm_accion_basica['es_lista'] = 'activo';

        $alta = (new adm_accion_basica($this->link))->alta_registro($adm_accion_basica);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 13;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['id'] = 2;
        $registro['descripcion'] = 'system';
        $registro['codigo'] = 'system';
        $r_alta = (new adm_sistema($this->link))->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r_alta);
            print_r($error);
            exit;
        }


        $registro = array();
        $registro['id'] = 1;
        $registro['adm_sistema_id'] = 2;
        $registro['adm_seccion_id'] = 13;
        $r_alta = (new adm_seccion_pertenece($this->link))->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r_alta);
            print_r($error);
            exit;
        }

        $_SESSION['permite'][2]['adm_seccion']['elimina_bd'] = 1;

        $resultado = $act->asigna_link_rows(accion: $accion,indice:  $indice, link: $this->link,
            obj_link:  $obj_link,registros_view:  $registros_view,row:  $row,seccion:  $seccion, style: $style);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]->adm_seccion_id);
        $this->assertEquals('', $resultado[0]->link_elimina_bd);
        $this->assertEquals('a', $resultado[0]->elimina_bd_style);
        errores::$error = false;

    }

    public function test_genera_link_row(): void
    {
        errores::$error = false;
        $act = new actions();
        $act = new liberator($act);

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;

        $accion = 'b';
        $link = $this->link;
        $obj_link = new links_menu($this->link, -1);
        $registros = array();
        $registros_view = array();
        $seccion = 'a';
        $style = 'a';
        $style_status = '';

        $registros[0] = new stdClass();
        $registros[0]->a_id = 1;


        $resultado = $act->genera_link_row($accion, $link, $obj_link, $registros, $registros_view, $seccion,
            $style, $style_status);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

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

        foreach ($_POST AS $key=>$val){
            unset($_POST[$key]);
        }


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
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $seccion = 'adm_menu';
        $obj_link = new links_menu($this->link, -1);
        $row = new stdClass();
        $row->a = '1';
        $key_id = 'a';
        $accion = 'elimina_bd';

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_menu';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion_pertenece_ins['id'] = 1;
        $adm_seccion_pertenece_ins['adm_seccion_id'] = 10;
        $adm_seccion_pertenece_ins['adm_sistema_id'] = 2;

        $r_alta = (new adm_seccion_pertenece($this->link))->alta_registro($adm_seccion_pertenece_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r_alta);
            print_r($error);
            exit;
        }

        $resultado = $act->link_accion($accion, $key_id, $this->link, $obj_link, $row, $seccion);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_registros_view_actions(): void
    {
        errores::$error = false;
        $act = new actions();
        //$act = new liberator($act);
        $_GET['session_id'] = 1;

        foreach ($_POST AS $key=>$val){
            unset($_POST[$key]);
        }

        $acciones = new stdClass();
        $link = $this->link;
        $obj_link  = new links_menu($link, -1);
        $registros = array();
        $seccion = 'a';
        $acciones->a = '';
        $registros[0] = new stdClass();
        $registros[0]->a_a = 'a';
        $registros[0]->a_id = '1';
        $resultado = $act->registros_view_actions($acciones, $link, $obj_link, $registros, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado[0]->a_a);
        $this->assertEquals('1', $resultado[0]->a_id);
        $this->assertEquals('', $resultado[0]->link_a);
        $this->assertEquals('info', $resultado[0]->a_style);
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
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $registro_id = -1;
        $seccion = 'adm_accion_grupo';
        $siguiente_view = 'modifica';

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_accion_grupo';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


       // $links = new links_menu($this->link, $registro_id);

        $resultado = $act->retorno_alta_bd($this->link, -1, $seccion, $siguiente_view);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
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

