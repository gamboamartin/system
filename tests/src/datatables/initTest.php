<?php
namespace tests\src\datatables;


use gamboamartin\errores\errores;

use gamboamartin\system\datatables\init;

use gamboamartin\test\liberator;
use gamboamartin\test\test;

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

    public function test_draw(): void
    {
        errores::$error = false;
        $datatables = new init();
        //$datatables = new liberator($datatables);

        $resultado = $datatables->draw();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $_GET['draw'] = 'x';

        $resultado = $datatables->draw();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error draw debe ser un numero', $resultado['mensaje_limpio']);

        errores::$error = false;

        $_GET['draw'] = '1';

        $resultado = $datatables->draw();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado);

        errores::$error = false;
    }

    public function test_init_datatable(): void
    {
        errores::$error = false;
        $datatables = new init();
        $datatables = new liberator($datatables);

        $filtro = array();

        $resultado = $datatables->init_datatable($filtro);
        $this->assertIsArray($resultado);
        $this->assertIsArray($resultado['columns']);
        $this->assertIsArray($resultado['columnDefs']);
        $this->assertIsArray($resultado['filtro']);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_init_filtro_datatables(): void
    {
        errores::$error = false;
        $datatables = new init();
        //$datatables = new liberator($datatables);

        $datatables_d = new stdClass();
        $rows_lista = array();
        $seccion = 'a';

        $datatables_d->filtro = array();
        $datatables_d->filtro[] = 'x';

        $resultado = $datatables->init_filtro_datatables($datatables_d, $rows_lista, $seccion);
        $this->assertIsArray($resultado);

        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_n_rows_for_page(): void
    {
        errores::$error = false;
        $datatables = new init();
        //$datatables = new liberator($datatables);

        $_GET['length'] = '999';

        $resultado = $datatables->n_rows_for_page();
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(999,$resultado);
        errores::$error = false;
    }
}

