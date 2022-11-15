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

    public function test_integra_acciones_permitidas(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
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
        $acciones_permitidas[0]['adm_accion_css'] = 'd';
        $acciones_permitidas[0]['adm_accion_es_status'] = 'inactivo';


        $acciones_permitidas[1]['adm_accion_descripcion'] = 'b';
        $acciones_permitidas[1]['adm_accion_titulo'] = 'x';
        $acciones_permitidas[1]['adm_seccion_descripcion'] = 'y';
        $acciones_permitidas[1]['adm_accion_css'] = 'r';
        $acciones_permitidas[1]['adm_accion_es_status'] = 'inactivo';

        $acciones_permitidas[2]['adm_accion_descripcion'] = 'r';
        $acciones_permitidas[2]['adm_accion_titulo'] = 'ff';
        $acciones_permitidas[2]['adm_seccion_descripcion'] = 'dd';
        $acciones_permitidas[2]['adm_accion_css'] = 'ss';
        $acciones_permitidas[2]['adm_accion_es_status'] = 'activo';

        $acciones_permitidas[3]['adm_accion_descripcion'] = 'rs';
        $acciones_permitidas[3]['adm_accion_titulo'] = 'ff';
        $acciones_permitidas[3]['adm_seccion_descripcion'] = 'dd';
        $acciones_permitidas[3]['adm_accion_css'] = 'ss';
        $acciones_permitidas[3]['adm_accion_es_status'] = 'activo';


        $rows[0] = array();
        $rows[0]['dd_r'] = 'activo';
        $rows[0]['dd_rs'] = 'inactivo';


        $resultado = $out->integra_acciones_permitidas($acciones_permitidas, $html_controler, $indice, $key_id, $row, $rows);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' href='index.php?seccion=c&accion=a&registro_id=1&session_id=1' class='btn btn-d col-sm-12'>b</a>",$resultado[0]['acciones']['a']);
        $this->assertEquals("<a role='button' href='index.php?seccion=y&accion=b&registro_id=1&session_id=1' class='btn btn-r col-sm-12'>x</a>",$resultado[0]['acciones']['b']);
        $this->assertEquals("<a role='button' href='index.php?seccion=dd&accion=r&registro_id=1&session_id=1' class='btn btn-success col-sm-12'>ff</a>",$resultado[0]['acciones']['r']);
        $this->assertEquals("<a role='button' href='index.php?seccion=dd&accion=rs&registro_id=1&session_id=1' class='btn btn-danger col-sm-12'>ff</a>",$resultado[0]['acciones']['rs']);
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
        //print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar  accion_permitida',$resultado['mensaje_limpio']);

        errores::$error = false;

        $accion_permitida = array();
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_accion_titulo'] = 'a';
        $accion_permitida['adm_seccion_descripcion'] = 'a';
        $accion_permitida['adm_accion_css'] = 'a';
        $accion_permitida['adm_accion_es_status'] = 'a';
        $resultado = $out->valida_data_action($accion_permitida);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }






}

