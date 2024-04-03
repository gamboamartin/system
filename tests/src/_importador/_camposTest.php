<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\_importador\_campos;
use gamboamartin\system\_importador\_xls;
use gamboamartin\system\datatables\filtros;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _camposTest extends test {
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

    public function test_campo_valida(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $_campo = new _campos();
        $_campo = new liberator($_campo);

        $adm_campos = array();
        $campo_db = 'a';
        $adm_campos[]['adm_campo_descripcion'] = 'a';
        $resultado = $_campo->campo_valida($adm_campos, $campo_db);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['adm_campo_descripcion']);
        errores::$error = false;
    }

    public function test_init_row_final(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $_campo = new _campos();
        $_campo = new liberator($_campo);

        $campo_db = 'b';
        $key = 'a';
        $rows_finals = array();
        $valida = true;
        $resultado = $_campo->init_row_final($campo_db, $key, $rows_finals, $valida);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('valido', $resultado['a']['b']['mensaje']);
        $this->assertEquals('success', $resultado['a']['b']['contexto']);
        $this->assertTrue( $resultado['a']['b']['exito']);
        errores::$error = false;

        $campo_db = 'p';
        $key = 'g';
        $rows_finals = array();
        $valida = false;
        $resultado = $_campo->init_row_final($campo_db, $key, $rows_finals, $valida);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('valido', $resultado['g']['p']['mensaje']);
        $this->assertEquals('success', $resultado['g']['p']['contexto']);
        $this->assertNotTrue( $resultado['a']['b']['exito']);
        errores::$error = false;

    }


}

