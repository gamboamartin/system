<?php
namespace tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JsonException;
use models\adm_accion;
use stdClass;


class systemTest extends test {
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
    public function test_system(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        //$controler = new liberator($controler);

        $resultado = $controler->alta(false);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div |class|><div |class|><input type='text' name='codigo' value=''",$resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_retorno_base(): void
    {
        errores::$error = false;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);

        $registro_id = -1;
        $result = array();
        $siguiente_view = '';
        $ws = false;
        $resultado = $controler->retorno_base($registro_id, $result, $siguiente_view, $ws, false);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

}

