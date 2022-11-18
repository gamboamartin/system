<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\datatables\acciones;
use gamboamartin\system\datatables\filtros;
use gamboamartin\system\datatables\init;
use gamboamartin\system\links_menu;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class filtrosTest extends test {
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

    public function test_filtro_especial_datatable(): void
    {
        errores::$error = false;
        $datatables = new filtros();
        $datatables = new liberator($datatables);

        $column = 'x';

        $str = 'x';
        $indice = 0;
        $filtro_especial = array();
        $resultado = $datatables->filtro_especial_datatable($filtro_especial, $indice, $column, $str);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('%x%', $resultado[0]['x']['valor']);
        errores::$error = false;
    }
}

