<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\_importador\_xls;
use gamboamartin\system\datatables\filtros;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _xlsTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/cat_sat/config/generales.php';
        $this->paths_conf->database = '/var/www/html/cat_sat/config/database.php';
        $this->paths_conf->views = '/var/www/html/cat_sat/config/views.php';
    }

    public function test_columna_calc_def(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $xls = new _xls();
        $xls = new liberator($xls);

        $columna_cal = 'a';
        $resultado = $xls->columna_calc_def($columna_cal);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['value']);
        $this->assertEquals('a', $resultado['descripcion_select']);
        errores::$error = false;
    }


}

