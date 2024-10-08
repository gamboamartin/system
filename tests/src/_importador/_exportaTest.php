<?php
namespace tests\controllers;

use gamboamartin\administrador\instalacion\instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\system\_importador\_exporta;
use gamboamartin\system\_importador\_xls;
use gamboamartin\system\datatables\filtros;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _exportaTest extends test {
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

    public function test_celda(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $exporta = new _exporta();
        $exporta = new liberator($exporta);

        $indice = 0;
        $letras = array();
        $letras[0] = 'x';
        $resultado = $exporta->celda(indice: $indice, letras: $letras);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X2',$resultado);
        errores::$error = false;
    }


    public function test_celda_busqueda(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $exporta = new _exporta();
        $exporta = new liberator($exporta);

        $campos_hd = array();
        $letras = array();
        $nombre_tabla_relacion = 'x';
        $campos_hd[] = 'x_id';
        $letras[0] = 'x';
        $resultado = $exporta->celda_busqueda($campos_hd, $letras, $nombre_tabla_relacion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X2',$resultado);
        errores::$error = false;
    }

    public function test_limpia_adm_campo(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $exporta = new _exporta();
        $exporta = new liberator($exporta);

        $adm_campo = array();
        $resultado = $exporta->limpia_adm_campo(adm_campo: $adm_campo);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_nombre_tabla_relacion(): void
    {
        $_SESSION['grupo_id'] = 2;
        errores::$error = false;
        $exporta = new _exporta();
        $exporta = new liberator($exporta);

        $adm_campo = array();
        $foraneas = new stdClass();
        $adm_campo['adm_campo_descripcion'] = 'a';
        $foraneas->a = new stdClass();
        $foraneas->a->nombre_tabla_relacion = 'v';
        $resultado = $exporta->nombre_tabla_relacion(adm_campo: $adm_campo,foraneas:  $foraneas);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('v',$resultado);
        errores::$error = false;
    }



}

