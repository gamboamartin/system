<?php
namespace tests\src\_ctl_base;


use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;

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

    public function test_init_param_get(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $key = 'A';
        $_GET['A'] = 'X';
        $data_init = new stdClass();
        $resultado = $ctl->init_param_get($data_init, $key);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X', $resultado->A);
        errores::$error = false;
    }



}

