<?php
namespace tests\src;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\out_permisos;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JsonException;
use stdClass;


class out_permisosTest extends test {
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

    public function test_buttons_permitidos(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $out = new out_permisos();
        $out = new liberator($out);

        $acciones_permitidas = array();
        $cols = -1;
        $html = new html();
        $html_= new html_controler($html);
        $params = array();
        $params_ajustados = array();
        $registro = array();
        $registro_id = -1;
        $registro[] = '';
        $resultado = $out->buttons_permitidos($acciones_permitidas, $cols, $html_, $params, $params_ajustados,
            $registro, $registro_id);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_cols_btn_action(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $out = new out_permisos();
        $out = new liberator($out);

        $acciones_permitidas = array();

        $resultado = $out->cols_btn_action($acciones_permitidas);
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(3,$resultado);
       errores::$error = false;
    }
    public function test_integra_acciones_permitidas(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $_GET['adm_menu_id'] = -1;
        $html = new html();
        $html_controler = new html_controler($html);


        $out = new out_permisos();
        $out = new liberator($out);


        $acciones_permitidas = array();
        $indice = 0;
        $key_id = 'x';
        $row = array();
        $row['x'] = '1';
        $rows = array();


        $acciones_permitidas[0]['adm_accion_descripcion'] = 'a';
        $acciones_permitidas[0]['adm_accion_titulo'] = 'b';
        $acciones_permitidas[0]['adm_seccion_descripcion'] = 'c';
        $acciones_permitidas[0]['adm_accion_css'] = 'info';
        $acciones_permitidas[0]['adm_accion_es_status'] = 'inactivo';
        $acciones_permitidas[0]['adm_accion_muestra_icono_btn'] = 'inactivo';
        $acciones_permitidas[0]['adm_accion_muestra_titulo_btn'] = 'activo';
        $acciones_permitidas[0]['adm_accion_icono'] = '';


        $acciones_permitidas[1]['adm_accion_descripcion'] = 'b';
        $acciones_permitidas[1]['adm_accion_titulo'] = 'x';
        $acciones_permitidas[1]['adm_seccion_descripcion'] = 'y';
        $acciones_permitidas[1]['adm_accion_css'] = 'danger';
        $acciones_permitidas[1]['adm_accion_es_status'] = 'inactivo';
        $acciones_permitidas[1]['adm_accion_muestra_icono_btn'] = 'inactivo';
        $acciones_permitidas[1]['adm_accion_muestra_titulo_btn'] = 'activo';
        $acciones_permitidas[1]['adm_accion_icono'] = '';

        $acciones_permitidas[2]['adm_accion_descripcion'] = 'r';
        $acciones_permitidas[2]['adm_accion_titulo'] = 'ff';
        $acciones_permitidas[2]['adm_seccion_descripcion'] = 'dd';
        $acciones_permitidas[2]['adm_accion_css'] = 'warning';
        $acciones_permitidas[2]['adm_accion_es_status'] = 'activo';
        $acciones_permitidas[2]['adm_accion_muestra_icono_btn'] = 'inactivo';
        $acciones_permitidas[2]['adm_accion_muestra_titulo_btn'] = 'activo';
        $acciones_permitidas[2]['adm_accion_icono'] = '';

        $acciones_permitidas[3]['adm_accion_descripcion'] = 'rs';
        $acciones_permitidas[3]['adm_accion_titulo'] = 'ff';
        $acciones_permitidas[3]['adm_seccion_descripcion'] = 'dd';
        $acciones_permitidas[3]['adm_accion_css'] = 'link';
        $acciones_permitidas[3]['adm_accion_es_status'] = 'activo';
        $acciones_permitidas[3]['adm_accion_muestra_icono_btn'] = 'inactivo';
        $acciones_permitidas[3]['adm_accion_muestra_titulo_btn'] = 'activo';
        $acciones_permitidas[3]['adm_accion_icono'] = '';


        $rows[0] = array();
        $rows[0]['dd_r'] = 'activo';
        $rows[0]['dd_rs'] = 'inactivo';


        $resultado = $out->integra_acciones_permitidas($acciones_permitidas, $html_controler, $indice, $key_id, $row, $rows);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' title='b' href='index.php?seccion=c&accion=a&registro_id=1&session_id=1&adm_menu_id=-1' class='btn btn-info ' style='margin-right: 2px; '>b</a>",$resultado[0]['acciones']['a']);
        $this->assertEquals("<a role='button' title='x' href='index.php?seccion=y&accion=b&registro_id=1&session_id=1&adm_menu_id=-1' class='btn btn-danger ' style='margin-right: 2px; '>x</a>",$resultado[0]['acciones']['b']);
        $this->assertEquals("<a role='button' title='ff' href='index.php?seccion=dd&accion=r&registro_id=1&session_id=1&adm_menu_id=-1' class='btn btn-success ' style='margin-right: 2px; '>ff</a>",$resultado[0]['acciones']['r']);
        $this->assertEquals("<a role='button' title='ff' href='index.php?seccion=dd&accion=rs&registro_id=1&session_id=1&adm_menu_id=-1' class='btn btn-danger ' style='margin-right: 2px; '>ff</a>",$resultado[0]['acciones']['rs']);
        errores::$error = false;
    }

    public function test_link_btn_action(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);


        $out = new out_permisos();
        $out = new liberator($out);

        $accion_permitida = array();
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_accion_titulo'] = 'b';
        $accion_permitida['adm_seccion_descripcion'] = 'c';
        $accion_permitida['adm_accion_css'] = 'light';
        $accion_permitida['adm_accion_es_status'] = 'activo';
        $accion_permitida['adm_accion_icono'] = '';
        $accion_permitida['adm_accion_muestra_icono_btn'] = 'inactivo';
        $accion_permitida['adm_accion_muestra_titulo_btn'] = 'activo';
        $cols = -1;
        $params = array();
        $registro = array();
        $registro['c_a'] = 'activo';
        $registro_id = -1;


        $resultado = $out->link_btn_action(accion_permitida: $accion_permitida,cols:  $cols, html: $html_controler,
            params:  $params,registro:  $registro,registro_id:  $registro_id);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' title='b' href='index.php?seccion=c&accion=a&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-success ' style='margin-bottom: 5px; '>b</a>",$resultado);
        errores::$error = false;
    }

    public function test_valida_data_action(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $out = new out_permisos();
        $out = new liberator($out);

        $accion_permitida = array();
        $resultado = $out->valida_data_action($accion_permitida);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar  accion_permitida',$resultado['mensaje_limpio']);

        errores::$error = false;

        $accion_permitida = array();
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_accion_titulo'] = 'a';
        $accion_permitida['adm_seccion_descripcion'] = 'a';
        $accion_permitida['adm_accion_css'] = 'danger';
        $accion_permitida['adm_accion_es_status'] = 'a';
        $accion_permitida['adm_accion_muestra_icono_btn'] = 'activo';
        $accion_permitida['adm_accion_muestra_titulo_btn'] = 'activo';
        $accion_permitida['adm_accion_icono'] = '';
        $resultado = $out->valida_data_action($accion_permitida);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    public function test_valida_data_btn(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $out = new out_permisos();
        $out = new liberator($out);

        $accion_permitida = array();
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_accion_titulo'] = 'a';
        $accion_permitida['adm_seccion_descripcion'] = 'a';
        $accion_permitida['adm_accion_css'] = 'info';
        $accion_permitida['adm_accion_es_status'] = 'a';
        $accion_permitida['adm_accion_muestra_icono_btn'] = 'a';
        $accion_permitida['adm_accion_muestra_titulo_btn'] = 'a';
        $accion_permitida['adm_accion_icono'] = 'a';
        $key_id = 'a';
        $row = array();
        $row['a'] = '1';
        $resultado = $out->valida_data_btn($accion_permitida, $key_id, $row);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }






}

