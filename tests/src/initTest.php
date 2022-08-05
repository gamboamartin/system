<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\init;
use gamboamartin\system\links_menu;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class initTest extends test {
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

    public function test_key_value_campo(): void
    {
        errores::$error = false;
        $init = new init();
        $init = new liberator($init);

        $tabla = 'a';
        $campo_puro = 'c';
        $resultado = $init->key_value_campo($campo_puro, $tabla);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_c', $resultado);
        errores::$error = false;
    }

    public function test_name_lista(): void
    {
        errores::$error = false;
        $init = new init();
        $init = new liberator($init);

        $campo_puro = '';

        $resultado = $init->name_lista($campo_puro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_limpia_data_row(): void
    {
        errores::$error = false;
        $init = new init();
        $init = new liberator($init);

        $key = 'a';
        $row = array('a'=>'x');
        $resultado = $init->limpia_data_row($key, $row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_limpia_rows(): void
    {
        errores::$error = false;
        $init = new init();
        //$init = new liberator($init);

        $keys = array('a');
        $row = array('a'=>'x');
        $resultado = $init->limpia_rows($keys, $row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_row_value_id(): void
    {
        errores::$error = false;
        $init = new init();
        //$init = new liberator($init);

        $tabla = 'a';
        $row = array();
        $resultado = $init->row_value_id($row, $tabla);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado['a_id']);

        errores::$error = false;

        $tabla = 'a';
        $row = new stdClass();
        $resultado = $init->row_value_id($row, $tabla);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado->a_id);
        errores::$error = false;
    }



}

