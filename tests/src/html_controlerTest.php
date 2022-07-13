<?php
namespace tests\controllers;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_accion;
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

    /**
     * @throws JsonException
     */
    public function test_alta(): void
    {
        errores::$error = false;
        $html = new html_controler();
        //$html = new liberator($html);


        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html_controler = new html_controler();

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);


        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->alta($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='control-group col-sm-6'><label class='control-label' for='codigo'>Codigo</label><div class='controls'><input type='text' name='codigo' value='' class='form-control'  required id='codigo' placeholder='Codigo' /></div></div>", $resultado->codigo);

        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_inputs_base(): void
    {
        errores::$error = false;
        $html = new html_controler();
        $html = new liberator($html);


        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html_controler = new html_controler();

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);
        $cols = new stdClass();
        $value_vacio = false;

        $cols->codigo = '1';
        $cols->codigo_bis = '1';

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->inputs_base($cols, $controler, $value_vacio);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='control-group col-sm-1'><label class='control-label' for='codigo'>Codigo</label><div class='controls'><input type='text' name='codigo' value='' class='form-control'  required id='codigo' placeholder='Codigo' /></div></div>", $resultado->codigo);

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

