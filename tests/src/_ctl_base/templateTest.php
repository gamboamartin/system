<?php
namespace tests\src\_ctl_base;


use gamboamartin\controllers\controlador_adm_grupo;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;

use gamboamartin\system\html_controler\template;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;


class templateTest extends test {
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

    public function test_valida_base(): void
    {
        errores::$error = false;
        $tm = new template();
        $tm = new liberator($tm);
        $params_select = new stdClass();
        $params_select->cols = 13;
        $params_select->disabled = 13;
        $params_select->name = 13;
        $params_select->place_holder = 13;
        $params_select->value_vacio = 13;
        $resultado = $tm->valida_base($params_select);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}

