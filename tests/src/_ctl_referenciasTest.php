<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\controllers\controlador_adm_grupo;
use gamboamartin\controllers\controlador_adm_menu;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\_ctl_referencias;
use gamboamartin\system\actions;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class _ctl_referenciasTest extends test {
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

    public function test_buttons_alta(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $controler->row_upd = new stdClass();
        $controler->row_upd->a = 'a';

        $ctl = (new _ctl_referencias());

        $ctl = new liberator($ctl);


        $buttons = new stdClass();
        $params = new stdClass();

        $params->model_parent = new stdClass();
        $params->model_parent->tabla = 'a';


        $resultado = $ctl->buttons_alta($buttons, $controler, $params);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

    }

    public function test_genera_botones_parent(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }
        $modelo = new adm_mes(link: $this->link);

        $ctl = new _ctl_referencias();
        $ctl = new liberator($ctl);

        $resultado = $ctl->genera_botones_parent(controler: $controler,etiqueta: 'Nueva adm_mes',model_parent: $modelo);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' title='Nueva adm_mes' href='index.php?seccion=adm_mes&accion=alta&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-warning col-sm-12'>Nueva adm_mes</a>",$resultado->adm_mes);
        $this->assertIsObject($resultado);
        errores::$error = false;


        $adm_mes['id'] = mt_rand(1,999999);
        $adm_mes['descripcion'] = 'adm_accion';
        $alta = (new adm_mes($this->link))->alta_registro($adm_mes);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $ctl->genera_botones_parent(controler: $controler, etiqueta: 'a',model_parent: $modelo);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

    }

    public function test_genera_botones_parent_ir(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $etiqueta = 'a';
        $model_parent = new adm_seccion(link: $this->link);
        $registro_id = 1;

        $resultado = $ctl->genera_botones_parent_ir($controler, $etiqueta, $model_parent, $registro_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("<a role='button' title='a' href='index.php?seccion=adm_seccion&accion=modifica&registro_id=1&session_id=1&adm_menu_id=-1' class='btn btn-info col-sm-12'>a</a>",$resultado->adm_seccion);

        errores::$error = false;
    }

    public function test_genera_keys_parents(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $ctl->genera_keys_parents($controler, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_input_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $key_parent_id = 'a';
        $resultado = $ctl->input_parent($controler, $key_parent_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['a']->con_registros);
        errores::$error = false;

    }

    public function test_inputs_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $resultado = $ctl->inputs_parent($controler);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_integra_button_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $resultado = $ctl->integra_button_parent($controler, 'Nueva adm_accion',$modelo,'success');
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("<a role='button' title='Nueva adm_accion' href='index.php?seccion=adm_accion&accion=alta&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-success col-sm-12'>Nueva adm_accion</a>",$resultado->adm_accion);
        errores::$error = false;
    }

    public function test_integra_button_parent_ir(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $etiqueta = 'a';
        $model_parent = new adm_seccion(link: $this->link);
        $registro_id = -1;
        $style = 'a';
        $resultado = $ctl->integra_button_parent_ir($controler, $etiqueta, $model_parent, $registro_id, $style);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("<a role='button' title='a' href='index.php?seccion=adm_seccion&accion=modifica&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-a col-sm-12'>a</a>",$resultado->adm_seccion);
        errores::$error = false;
    }

    public function test_integra_key_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);


        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $key_parent_id = 'a';

        $resultado = $ctl->integra_key_parent($controler, $key_parent_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

    }

    public function test_integra_key_parent_get(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);


        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $key_parent_id = 'a';

        $resultado = $ctl->integra_key_parent_get($controler, $key_parent_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_key_parent_id(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $model_parent = new adm_mes(link: $this->link);
        $resultado = $ctl->key_parent_id($model_parent);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals("adm_mes_id",$resultado);

        errores::$error = false;

        $model_parent = new adm_mes(link: $this->link);
        $model_parent->tabla = '';
        $resultado = $ctl->key_parent_id($model_parent);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals("Error la tabla del modelo esta vacia",$resultado['mensaje_limpio']);
        errores::$error = false;
    }



    public function test_model_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $parent = array();
        $parent['model_parent'] = new adm_seccion(link: $this->link);
        $resultado = $ctl->model_parent($parent);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("Seccion",$resultado->etiqueta);
        errores::$error = false;
    }

    public function test_params_btn_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $parent = array();
        $parent['model_parent'] = new adm_seccion(link: $this->link);
        $parent['etiqueta'] = 'a';
        $resultado = $ctl->params_btn_parent($parent);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("adm_seccion",$resultado->model_parent->tabla);
        errores::$error = false;
    }

    public function test_param_children(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $controler->registro_id = 1;


        $key_parent_id = '';
        $params_btn_children = array();

        $resultado = $ctl->param_children($controler, $key_parent_id, $params_btn_children);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_style_btn(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $success = 'a';
        $tiene_rows = false;
        $resultado = $ctl->style_btn($success, $tiene_rows);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals("warning",$resultado);
        errores::$error = false;

    }

    public function test_style_btn_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);

        $success = 'a';
        $model_parent = new adm_seccion(link: $this->link);
        $resultado = $ctl->style_btn_parent($model_parent, $success);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals("a",$resultado);
        errores::$error = false;
    }

    public function test_value_param_children(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $controler->row_upd = new stdClass();
        $controler->row_upd->a = 'a';

        $ctl = (new _ctl_referencias());

        $ctl = new liberator($ctl);


        $params_btn_children = array();
        $key_parent_id = 'a';


        $resultado = $ctl->value_param_children($controler, $key_parent_id, $params_btn_children);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals("a",$resultado['a']);
        errores::$error = false;
    }

    public function test_value_row_children_proceso(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $ctl = (new _ctl_referencias());
        $ctl = new liberator($ctl);
        $key_parent_id = 'a';
        $params_btn_children = array();
        $row_in_proceso = new stdClass();
        $row_in_proceso->a  = '1';
        $resultado = $ctl->value_row_children_proceso($key_parent_id, $params_btn_children, $row_in_proceso);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals("1",$resultado['a']);
        errores::$error = false;
    }


}

