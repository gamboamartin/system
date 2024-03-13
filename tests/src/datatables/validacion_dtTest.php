<?php
namespace tests\src\datatables;


use gamboamartin\errores\errores;

use gamboamartin\system\datatables\init;

use gamboamartin\system\datatables\validacion_dt;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;


class validacion_dtTest extends test {
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

    public function test_valida_data_column(): void
    {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['grupo_id'] = 1;
        errores::$error = false;
        $val = new validacion_dt();
        //$datatables = new liberator($datatables);

        $seccion = 'a';
        $resultado = $val->valida_data_column($seccion);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }


}

