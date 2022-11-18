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
use gamboamartin\system\links_menu;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class accionesTest extends test {
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

    public function test_accion_base(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);
        $_SESSION['grupo_id'] = 1;

        $acciones_grupo = array();

        $resultado = $datatables->accion_base($acciones_grupo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
        $acciones_grupo = array();
        $acciones_grupo['a']['adm_accion_descripcion'] = 'x';

        $resultado = $datatables->accion_base($acciones_grupo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_acciones_columnas(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;

        $seccion = 'a';
        $columns = array();
        $resultado = $datatables->acciones_columnas($columns, $this->link, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_columnas_accion(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);

        $acciones_grupo = array();
        $acciones_grupo[0]['adm_accion_descripcion'] = 'a';
        $acciones_grupo[1]['adm_accion_descripcion'] = 'a';

        $adm_accion_base = 'v';
        $columns = array();
        $resultado = $datatables->columnas_accion($acciones_grupo, $adm_accion_base, $columns);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado['v']['campos'][0]);
        errores::$error = false;
    }

    public function test_genera_accion(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);

        $i = 1;

        $columns = array();
        $adm_accion_grupo = array();
        $adm_accion_grupo['adm_accion_descripcion'] = 'a';
        $adm_accion_base = 'b';
        $resultado = $datatables->genera_accion($adm_accion_base, $adm_accion_grupo, $columns, $i);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['b']['campos'][0]);
        errores::$error = false;
    }

    public function test_integra_accion(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);
        $_SESSION['grupo_id'] = 1;
        $adm_accion = 'b';
        $adm_accion_base = 'a';
        $columns = array();

        $resultado = $datatables->integra_accion($adm_accion, $adm_accion_base, $columns);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_maqueta_accion_base_column(): void
    {
        errores::$error = false;
        $datatables = new acciones();
        $datatables = new liberator($datatables);

        $acciones_grupo = array();
        $acciones_grupo[] = '';

        $adm_accion_base = 'a';
        $columns = array();

        $resultado = $datatables->maqueta_accion_base_column($acciones_grupo, $adm_accion_base, $columns);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Acciones', $resultado['a']['titulo']);
        errores::$error = false;
    }


}

