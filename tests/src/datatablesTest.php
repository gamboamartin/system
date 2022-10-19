<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\datatables;
use gamboamartin\test\test;

use stdClass;


class datatablesTest extends test {
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

    public function test_column_init(): void
    {
        errores::$error = false;
        $datatables = new datatables();

        $column = '';
        $indice = '';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = '';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->title);
        $this->assertEquals("b", $resultado->data);

        errores::$error = false;

        $column = array();
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = array('a');
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertEquals("b", $resultado->title);
        $this->assertEquals("b", $resultado->data);


        errores::$error = false;
    }

    public function test_column_titulo(): void
    {
        errores::$error = false;
        $datatables = new datatables();

        $column = array();
        $indice = '';
        $column_obj = new stdClass();
        $resultado = $datatables->column_titulo($column, $column_obj, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar column", $resultado['mensaje']);

        errores::$error = false;
        $datatables = new datatables();

        $column['titulo'] = 'a';
        $indice = '';
        $column_obj = new stdClass();
        $resultado = $datatables->column_titulo($column, $column_obj, $indice);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->title);
        errores::$error = false;
    }

    /**
     */
    public function test_columns_defs(): void
    {
        errores::$error = false;
        $datatables = new datatables();

        $column = '';
        $indice = '';
        $targets = '1';
        $type = '';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = '';
        $targets = '1';
        $type = '';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = 'a';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("1", $resultado->targets);
        $this->assertEquals(null, $resultado->data);
        $this->assertEquals('a', $resultado->type);
        $this->assertEquals('a', $resultado->rendered[0]);


        errores::$error = false;

        $column = array();
        $indice = 'a';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column['campos'] = array('a');
        $indice = 'b';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("1", $resultado->targets);
        $this->assertEquals(null, $resultado->data);
        $this->assertEquals('a', $resultado->type);
        $this->assertEquals('b', $resultado->rendered[0]);
        $this->assertEquals('a', $resultado->rendered[1]);

        errores::$error = false;
    }



}

