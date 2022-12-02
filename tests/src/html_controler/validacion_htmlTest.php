<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\validacion_html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class validacion_htmlTest extends test {
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

    public function test_valida_base_html(): void
    {
        errores::$error = false;
        $val = new validacion_html();
        $val = new liberator($val);
        $params_select = new stdClass();
        $params_select->cols = 13;
        $params_select->disabled = 13;
        $params_select->name = 13;
        $params_select->place_holder = 13;
        $params_select->value_vacio = 13;
        $resultado = $val->valida_base_html($params_select);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}

