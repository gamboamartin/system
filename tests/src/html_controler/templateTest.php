<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler\template;
use gamboamartin\template\directivas;
use gamboamartin\template\html;
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
        $this->paths_conf->generales = '/var/www/html/system/config/generales.php';
        $this->paths_conf->database = '/var/www/html/system/config/database.php';
        $this->paths_conf->views = '/var/www/html/system/config/views.php';
    }

    public function test_dates_template(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new template();
        // $html = new liberator($html);


        $params_select =new stdClass();
        $row_upd = new stdClass();

        $params_select->cols = '1';
        $params_select->disabled = true;
        $params_select->name = 'a';
        $params_select->place_holder = 'a';
        $params_select->value_vacio = false;

        $directivas = new directivas($html_);

        $resultado = $html->dates_template($directivas, $params_select, $row_upd);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='date' name='a' value='' |class| disabled required id='a' placeholder='a' /></div></div>", $resultado);
        errores::$error = false;
    }

    public function test_input_template(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new template();
        //$html = new liberator($html);


        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $params_select = new stdClass();
        $params_select->cols = '1';
        $params_select->disabled = true;
        $params_select->name = 'a';
        $params_select->place_holder = 'a';
        $params_select->value_vacio = false;
        $params_select->required = false;
        $row_upd = new stdClass();

        $directivas = new directivas($html_);

        $resultado = $html->input_template($directivas, $params_select, $row_upd);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='a' value='' |class| disabled  id='a' placeholder='a' /></div></div>",$resultado);
        errores::$error = false;
    }

    public function test_passwords_template(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new template();
        // $html = new liberator($html);


        $params_select =new stdClass();
        $row_upd = new stdClass();

        $params_select->cols = '1';
        $params_select->disabled = false;
        $params_select->name = 'a';
        $params_select->place_holder = 'f';
        $params_select->value_vacio = true;

        $directivas = new directivas($html_);

        $resultado = $html->passwords_template($directivas, $params_select, $row_upd);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='password' name='a' value='' class='form-control'   required id='a' placeholder='f' /></div></div>",$resultado);
        errores::$error = false;

    }




}

