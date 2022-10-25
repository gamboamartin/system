<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JsonException;
use models\adm_accion;
use stdClass;


class systemTest extends test {
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

    /**
     */
    public function test_alta(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        //$controler = new liberator($controler);

        $resultado = $controler->alta(false);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div |class|><div |class|><input type='text' name='codigo' value=''",$resultado);
        errores::$error = false;
    }

    /**
     */
    public function test_get_data(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        //$controler = new liberator($controler);

        errores::$error = false;
        $controler->columnas_lista_data_table[] = 'adm_accion_id';
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(255,$resultado['recordsTotal']);
        $this->assertCount(10,$resultado['data']);

        errores::$error = false;

        $_POST['n_rows_for_page'] = 2;
        $resultado = $controler->get_data(header:false);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(255,$resultado['recordsTotal']);
        $this->assertCount(10,$resultado['data']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 21;
        $resultado = $controler->get_data(header:false);
        //print_r($resultado);exit;
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(255,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(16,$resultado['data'][0]['adm_accion_id']);


        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 0;
        $_GET['search']['value'] = 2;

        $controler->datatable['filtro'] = array('adm_accion.id');
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(68,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(2,$resultado['data'][0]['adm_accion_id']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 2;
        $resultado = $controler->get_data(header:false);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(68,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(420,$resultado['data'][0]['adm_accion_id']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 42;
        $resultado = $controler->get_data(header:false);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(4,$resultado['recordsTotal']);
        $this->assertCount(4,$resultado['data']);
        $this->assertEquals(42,$resultado['data'][0]['adm_accion_id']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 21;
        $_GET['search']['value'] = 420;

        $resultado = $controler->get_data(header:false);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);
        $this->assertEquals(420,$resultado['data'][0]['adm_accion_id']);

        errores::$error = false;


    }

    public function test_integra_acciones_permitidas(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $controler = new liberator($controler);

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


        $resultado = $controler->integra_acciones_permitidas($acciones_permitidas, $indice, $key_id, $row, $rows);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' href='index.php?seccion=c&accion=a&registro_id=1&session_id=1' class='btn btn-d col-sm-12'>b</a>",$resultado[0]['acciones']['a']);
        $this->assertEquals("<a role='button' href='index.php?seccion=y&accion=b&registro_id=1&session_id=1' class='btn btn-r col-sm-12'>x</a>",$resultado[0]['acciones']['b']);
        $this->assertEquals("<a role='button' href='index.php?seccion=dd&accion=r&registro_id=1&session_id=1' class='btn btn-danger col-sm-12'>ff</a>",$resultado[0]['acciones']['r']);
        $this->assertEquals("<a role='button' href='index.php?seccion=dd&accion=rs&registro_id=1&session_id=1' class='btn btn-warning col-sm-12'>ff</a>",$resultado[0]['acciones']['rs']);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_retorno_base(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);

        $registro_id = -1;
        $result = array();
        $siguiente_view = '';
        $ws = false;
        $resultado = $controler->retorno_base($registro_id, $result, $siguiente_view, $ws, false);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_rows_con_permisos(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $controler = new liberator($controler);


        $key_id = '';
        $rows = array();
        $seccion = 'a';

        $resultado = $controler->rows_con_permisos($key_id, $rows, $seccion);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    /**
     */
    public function test_valida_key_rows_lista(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);
        $key_row_lista = '';
        $resultado = $controler->valida_key_rows_lista($key_row_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el key_row_lista debe ser un objeto",$resultado['mensaje']);

        errores::$error = false;

        $key_row_lista = array();
        $resultado = $controler->valida_key_rows_lista($key_row_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el key_row_lista debe ser un objeto",$resultado['mensaje']);

        errores::$error = false;

        $key_row_lista = new stdClass();
        $resultado = $controler->valida_key_rows_lista($key_row_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar key_row_lista",$resultado['mensaje']);

        errores::$error = false;

        $key_row_lista = new stdClass();
        $key_row_lista->campo = '';
        $resultado = $controler->valida_key_rows_lista($key_row_lista);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

}

