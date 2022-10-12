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

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(255,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(16,$resultado['data'][0][0]);


        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 0;
        $_GET['search']['value'] = 2;
        $controler->columnas_lista_data_table_filter[] = 'adm_accion.id';
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(68,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(2,$resultado['data'][0][0]);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 2;
        $resultado = $controler->get_data(header:false);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(68,$resultado['recordsTotal']);
        $this->assertCount(15,$resultado['data']);
        $this->assertEquals(420,$resultado['data'][0][0]);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 42;
        $resultado = $controler->get_data(header:false);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(4,$resultado['recordsTotal']);
        $this->assertCount(4,$resultado['data']);
        $this->assertEquals(42,$resultado['data'][0][0]);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 21;
        $_GET['search']['value'] = 420;

        $resultado = $controler->get_data(header:false);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);
        $this->assertEquals(420,$resultado['data'][0][0]);

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

