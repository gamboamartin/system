<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\template;
use gamboamartin\system\html_controler\texts;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class textsTest extends test {
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

    public function test_text_input_integra(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new texts();
        $html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $item = 'a';
        $keys_selects = array();
        $row_upd = new stdClass();
        $texts = new stdClass();

        $directivas = new directivas($html_);

        $resultado = $html->text_input_integra($directivas, $item, $keys_selects, $row_upd, $texts);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='a' value='' |class| required id='a' placeholder='a' title='a' /></div></div>",$resultado->a);
        errores::$error = false;
    }

    public function test_texts_integra(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new texts();
        //$html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $keys_selects = array();
        $row_upd = new stdClass();
        $campos_view = array();
        $campos_view['inputs'][] = 'x';

        $directivas = new directivas($html_);

        $resultado = $html->texts_integra( $campos_view, $directivas, $keys_selects, $row_upd);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='x' value='' |class| required id='x' placeholder='x' title='x' /></div></div>",$resultado->x);
        errores::$error = false;
    }
}

