<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\validacion_html;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class validacion_htmlTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

    public function test_valida_input(): void
    {
        errores::$error = false;
        $val = new validacion_html();
        $val = new liberator($val);
        $params_select = new stdClass();
        $params_select->cols = 13;
        $params_select->disabled = true;
        $params_select->name = 13;
        $params_select->place_holder = 13;
        $params_select->value_vacio = false;
        $resultado = $val->valida_input($params_select);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }

    public function test_valida_input_base(): void
    {
        errores::$error = false;
        $val = new validacion_html();
        $val = new liberator($val);
        $params_select = new stdClass();
        $params_select->cols = 12;
        $params_select->disabled = true;
        $params_select->name = 12;
        $params_select->place_holder = 12;
        $params_select->value_vacio = false;


        $html = new html();
        $directivas = new directivas($html);
        $resultado = $val->valida_input_base($directivas, $params_select);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;


    }



}

