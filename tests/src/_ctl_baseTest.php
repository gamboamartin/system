<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\actions;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class _ctl_baseTest extends test {
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

    public function test_alta_bd_base(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 2;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'test';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $_POST['adm_seccion_id'] = 1;
        $_POST['descripcion'] = 'test';
        $resultado = $ctl->alta_bd_base();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_base(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion(link: $this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar ', $del);
            print_r($error);
            exit;
        }

        $seccion_ins['id'] = 1;
        $seccion_ins['descripcion'] = 'adm_accion';
        $seccion_ins['adm_menu_id'] = '1';
        $seccion_ins['adm_namespace_id'] = '1';

        $alta = (new adm_seccion(link: $this->link))->alta_registro($seccion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar ', $alta);
            print_r($error);
            exit;
        }



        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;
        $resultado = $ctl->base();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_campos_view(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;
        $resultado = $ctl->campos_view();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_campos_view_base(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        //$ctl = new liberator($ctl);


        errores::$error = false;
        $init_data = array();
        $keys = new stdClass();
        $resultado = $ctl->campos_view_base($init_data, $keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_childrens(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 2;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;



        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'test';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $namespace_model = 'gamboamartin\\administrador\\models';
        $name_model_children = 'adm_accion';
        $params = array();
        $registro_id = 1;

        $resultado = $ctl->childrens($namespace_model, $name_model_children, array(), $params, $registro_id);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado[0]['adm_accion_id']);
        errores::$error = false;

    }

    public function test_children_base(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $adm_seccion['id'] = 2;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;



        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'test';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $ctl->children_base();

        $this->assertIsObject($ctl->inputs);
        $this->assertIsObject($resultado);
        $this->assertFalse(errores::$error);
        errores::$error = false;
    }

    public function test_data_retorno(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 2;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $resultado = $ctl->data_retorno();
        errores::$error = false;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado->id_retorno);
        $this->assertEquals('adm_accion',$resultado->seccion_retorno);
        errores::$error = false;
    }

    public function test_data_retorno_base(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $resultado = $ctl->data_retorno_base();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado->id_retorno);
        $this->assertEquals('modifica',$resultado->siguiente_view);
        $this->assertEquals('adm_accion',$resultado->seccion_retorno);
        errores::$error = false;
    }

    public function test_filtro_children(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);
        $ctl->key_id_filter = 'x';
        $ctl->registro_id = 1;
        //$ctl = new liberator($ctl);

        errores::$error = false;

        $registro_id = 1;

        $resultado = $ctl->filtro_children($registro_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['x']);
        errores::$error = false;
    }

    public function test_genera_filtro_children(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);


        errores::$error = false;

        $registro_id = 1;
        $resultado = $ctl->genera_filtro_children($registro_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['adm_accion.id']);
        errores::$error = false;
    }

    public function test_id_retorno(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $resultado = $ctl->id_retorno();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado);
        errores::$error = false;

    }

    public function test_init_alta(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);


        errores::$error = false;

        $resultado = $ctl->init_alta();
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("codigo' value='' |class| required id='codigo' placeholder='Codigo'",$resultado);
        errores::$error = false;
    }

    public function test_init_data_children(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);
        errores::$error = false;

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'test';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $ctl->registro_id = 1;
        $resultado = $ctl->init_data_children();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado->adm_accion_id);
        errores::$error = false;

    }

    public function test_init_modifica(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }



        $adm_seccion['id'] = 2;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);


        errores::$error = false;

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_test';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $ctl->init_modifica();

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado->registro['adm_accion_id']);

        errores::$error = false;

    }

    public function test_input_retornos(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $del = (new adm_sistema($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }


        $seccion_ins['id'] = 1;
        $seccion_ins['descripcion'] = 'adm_accion';
        $seccion_ins['adm_menu_id'] = 1;
        $seccion_ins['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($seccion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $sistema_ins['id'] = 1;
        $sistema_ins['descripcion'] = 'system';

        $alta = (new adm_sistema($this->link))->alta_registro($sistema_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $accion_ins['id'] = 1;
        $accion_ins['adm_seccion_id'] = 1;
        $accion_ins['adm_sistema_id'] = 1;
        $alta = (new adm_seccion_pertenece($this->link))->alta_registro($accion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;
        $resultado = $ctl->input_retornos();

       //print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<input type='hidden' name='adm_accion_id' value='-1'>",$resultado->hidden_row_id);
        $this->assertEquals("<input type='hidden' name='seccion_retorno' value='adm_accion'>",$resultado->hidden_seccion_retorno);
        $this->assertEquals("<input type='hidden' name='id_retorno' value='-1'>",$resultado->hidden_id_retorno);

        errores::$error = false;
    }

    public function test_inputs_children(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;
        $registro = new stdClass();
        $resultado = $ctl->inputs_children($registro);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_integra_key_to_select(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $key = 'a';
        $key_val = 'y';
        $keys_selects = array();
        $value = '';
        $resultado = $ctl->integra_key_to_select($key, $key_val, $keys_selects, $value);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado['a']->y);
        errores::$error = false;
    }

    public function test_key_id_filter(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;

        $resultado = $ctl->key_id_filter();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion.id',$resultado);
        errores::$error = false;
    }

    public function test_key_select(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $cols = 1;
        $con_registros = true;
        $filtro = array();
        $key = 'a';
        $keys_selects = array();
        $id_selected =- 1 ;
        $label = 'xxx' ;

        $resultado = $ctl->key_select($cols, $con_registros, $filtro, $key, $keys_selects, $id_selected, $label);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('xxx',$resultado['a']->label);

        errores::$error = false;
    }

    public function test_label(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);


        errores::$error = false;

        $key = 'a';
        $resultado = $ctl->label($key);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);

        errores::$error = false;
    }

    public function test_label_init(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);


        errores::$error = false;

        $key = 'a';
        $label = '';
        $resultado = $ctl->label_init($key, $label);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);

        errores::$error = false;



        $key = 'a';
        $label = 'y';
        $resultado = $ctl->label_init($key, $label);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('y',$resultado);

        errores::$error = false;
    }

    public function test_out_alta_bd(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);

        $header = false;
        $data_retorno = new stdClass();
        $result = new stdClass();
        $ws = false;
        $resultado = $ctl->out_alta_bd($header, $data_retorno, $result, $ws);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->siguiente_view);

        errores::$error = false;
    }

    public function test_seccion_retorno(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;
        $ctl = new liberator($ctl);

        errores::$error = false;

        $resultado = $ctl->seccion_retorno();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion',$resultado);

        errores::$error = false;

        errores::$error = false;
        $_POST['seccion_retorno'] = 'x';
        $resultado = $ctl->seccion_retorno();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;

    }

    public function test_valida_data_children(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';


        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        errores::$error = false;

        $ctl = new _ctl_base(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        $ctl = new liberator($ctl);


        errores::$error = false;
        $namespace_model =  'a';
        $name_model_children  = 'v';
        $registro_id  = 1;
        $resultado = $ctl->valida_data_children($namespace_model, $name_model_children, $registro_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

}

