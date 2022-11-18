<?php
namespace tests\src;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\html_controler\params;
use gamboamartin\system\html_controler\select;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class paramsTest extends test {
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

    public function test_params_base(): void
    {
        errores::$error = false;

        $html = new params();
        $html = new liberator($html);


        $data = new stdClass();
        $params = new stdClass();
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals(true, $resultado->required);
        $this->assertEquals(false, $resultado->disabled);
        $this->assertEquals(false, $resultado->value_vacio);
        $this->assertIsObject( $resultado->row_upd);
        $this->assertIsArray( $resultado->filtro);
        $this->assertEmpty( $resultado->filtro);
        $this->assertIsArray( $resultado->not_in);
        $this->assertEmpty( $resultado->not_in);
        $this->assertEmpty( $resultado->name);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertEmpty( $resultado->extra_params_keys);

        errores::$error = false;

        $data = new stdClass();
        $params = new stdClass();
        $params->disabled = true;
        $params->extra_params_keys = array('x'=>'d');
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertNotEmpty( $resultado->extra_params_keys);

        errores::$error = false;

        $data = new stdClass();
        $params = new stdClass();
        $params->disabled = true;
        $params->extra_params_keys = array('x'=>'d');
        $params->filtro = array('x'=>'d');
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertNotEmpty( $resultado->extra_params_keys);
        $this->assertIsArray( $resultado->filtro);
        $this->assertNotEmpty( $resultado->filtro);

        errores::$error = false;

    }

    public function test_params_input2(): void
    {
        errores::$error = false;

        $html = new params();
        $html = new liberator($html);

        $params = new stdClass();
        $name = '';
        $place_holder = '';
        $resultado = $html->params_input2($params, $name, $place_holder);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue( errores::$error);
        errores::$error = false;

    }

    public function test_params_select_col_6(): void
    {
        errores::$error = false;
        $html = new params();

        $html = new liberator($html);
        $params = new stdClass();
        $label = '__a__';
        $resultado = $html->params_select_col_6($params, $label);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(6, $resultado->cols);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals(' a ', $resultado->label);
        $this->assertEquals(true, $resultado->required);
        errores::$error = false;
    }

    public function test_params_select(): void
    {
        errores::$error = false;

        $html = new params();
        //$html = new liberator($html);


        $name_model = 'a';
        $params = new stdClass();
        $resultado = $html->params_select($name_model, $params);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(12, $resultado->cols);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals('A', $resultado->label);
        $this->assertEquals(true, $resultado->required);
        errores::$error = false;
    }

    public function test_params_select_init(): void
    {
        errores::$error = false;

        $html = new params();
        //$html = new liberator($html);


        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $item = 'a';
        $keys_selects = array();

        $resultado = $html->params_select_init($item, $keys_selects);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}

