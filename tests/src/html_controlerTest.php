<?php
namespace tests\controllers;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class html_controlerTest extends test {
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

    public function test_keys_base(): void
    {
        errores::$error = false;
        $html = new html_controler();
        $html = new liberator($html);

        $tabla = 'a';

        $resultado = $html->keys_base($tabla);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado->id);
        $this->assertEquals('a_descripcion_select', $resultado->descripcion_select);
        errores::$error = false;
    }

}

