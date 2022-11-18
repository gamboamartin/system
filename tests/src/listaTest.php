<?php
namespace tests\src;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\lista;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JsonException;
use stdClass;


class listaTest extends test {
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

    public function test_columnas_lista(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();


        $controler = new lista();
        $controler = new liberator($controler);
        $keys_row_lista = array();

        $resultado = $controler->columnas_lista($keys_row_lista);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $keys_row_lista = array();
        $keys_row_lista[] = '';

        $resultado = $controler->columnas_lista($keys_row_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals("Error al validar key_row_lista",$resultado['mensaje_limpio']);

        errores::$error = false;

        $keys_row_lista = array();
        $keys_row_lista[0] = new stdClass();

        $resultado = $controler->columnas_lista($keys_row_lista);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals("Error al validar key_row_lista",$resultado['mensaje_limpio']);

        errores::$error = false;

        $keys_row_lista = array();
        $keys_row_lista[0] = new stdClass();
        $keys_row_lista[0]->campo = 'a';

        $resultado = $controler->columnas_lista($keys_row_lista);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a",$resultado[0]);

        errores::$error = false;
    }


    public function test_valida_key_rows_lista(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();


        $controler = new lista();
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

