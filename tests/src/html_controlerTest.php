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
use models\adm_menu;
use models\adm_usuario;
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
     */
    public function test_alta(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);


        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu(-1);


        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->alta($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo' value='' |class|  required id='codigo' placeholder='Codigo' /></div></div>", $resultado->codigo);

        errores::$error = false;
    }

    public function test_genera_values_selects(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $keys = new stdClass();
        $keys->id = 'a';
        $keys->descripcion_select = 'b';
        $registros = array();
        $registros[0]['a'] = 'x';
        $registros[0]['b'] = 'd';
        $resultado = $html->genera_values_selects($keys, $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("x", $resultado['x']['a']);
        $this->assertEquals("d", $resultado['x']['b']);
        $this->assertEquals("d", $resultado['x']['descripcion_select']);

        errores::$error = false;
    }

    public function test_init_data_select(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $con_registros = true;
        $modelo = new adm_usuario($this->link);
        $extra_params_keys = array('adm_grupo_descripcion');
        $key_descripcion_select = 'adm_usuario_id';
        $key_id = '';
        $label = '';
        $resultado = $html->init_data_select(con_registros:$con_registros,modelo: $modelo,
            extra_params_keys:$extra_params_keys,key_descripcion_select:$key_descripcion_select,key_id:$key_id,
            label:$label);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_usuario_id',$resultado->id);
        $this->assertEquals('adm_usuario_id',$resultado->descripcion_select);
        $this->assertEquals('2',$resultado->values[2]['adm_usuario_id']);
        errores::$error = false;
    }

    public function test_input_codigo(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $row_upd = new stdClass();
        $cols = 1;
        $value_vacio = false;

        $resultado = $html->input_codigo($cols, $row_upd, $value_vacio);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo' value='' |class|  required id='codigo' placeholder='Codigo' /></div></div>", $resultado);

        errores::$error = false;
    }

    public function test_input_codigo_bis(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $row_upd = new stdClass();
        $cols = 1;
        $value_vacio = false;

        $resultado = $html->input_codigo_bis($cols, $row_upd, $value_vacio);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo_bis' value='' |class|  required id='codigo_bis' placeholder='Codigo BIS' /></div></div>", $resultado);

        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_inputs_base(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html_controler = new html_controler($html_);

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
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo' value='' |class|  required id='codigo' placeholder='Codigo' /></div></div>", $resultado->codigo);

    }

    public function test_keys_base(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $tabla = 'a';

        $resultado = $html->keys_base($tabla);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado->id);
        $this->assertEquals('a_descripcion_select', $resultado->descripcion_select);
        errores::$error = false;
    }

    public function test_label(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $tabla = 'a_';
        $resultado = $html->label($tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);
        errores::$error = false;
    }

    public function test_rows_select(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $modelo = new adm_accion($this->link);
        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $extra_params_keys = array();
        $extra_params_keys[] = 'adm_seccion_descripcion';

        $resultado = $html->rows_select(keys:$keys, modelo:$modelo,extra_params_keys:$extra_params_keys);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[0]['adm_accion_id']);
        $this->assertEquals('alta', $resultado[0]['adm_accion_descripcion']);
        $this->assertEquals('adm_grupo', $resultado[0]['adm_seccion_descripcion']);

        errores::$error = false;
    }

    public function test_select_catalogo(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $cols = -1;
        $con_registros = false;
        $id_selected = -1;
        $modelo = new adm_menu($this->link);
        $resultado = $html->select_catalogo($cols, $con_registros, $id_selected, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar cols', $resultado['mensaje']);

        errores::$error = false;

        $cols = 1;
        $con_registros = true;
        $id_selected = -1;
        $modelo = new adm_menu($this->link);
        $resultado = $html->select_catalogo($cols, $con_registros, $id_selected, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar datos', $resultado['mensaje']);

        errores::$error = false;

        $cols = 1;
        $con_registros = true;
        $id_selected = -1;
        $modelo = new adm_menu($this->link);
        $resultado = $html->select_catalogo(cols: $cols,con_registros:  $con_registros,id_selected:  $id_selected,
            modelo:  $modelo,key_descripcion_select: 'adm_menu_id');

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("div class='control-group col-sm-1'><div class='controls'><select class='form-control selectpicker color-secondary adm_menu_id' id='adm_menu_id'", $resultado);
        errores::$error = false;


        $cols = 1;
        $con_registros = true;
        $id_selected = -1;
        $modelo = new adm_menu($this->link);
        $resultado = $html->select_catalogo(cols: $cols,con_registros:  $con_registros,id_selected:  $id_selected,
            modelo:  $modelo,key_descripcion_select: 'adm_menu_id',name: 'x');



        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='control-group col-sm-1'><div class='controls'><select class='form-control selectpicker color-secondary x' id='x' name='x'  ><option value=''  >Selecciona una opcion</option><option value='1'  >1", $resultado);


        errores::$error = false;
    }

    public function test_values_selects(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';

        $con_registros = true;
        $modelo = new adm_accion($this->link);
        $extra_params_keys = array();
        $resultado = $html->values_selects($con_registros, $keys, $modelo,$extra_params_keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[1]['adm_accion_id']);
        $this->assertEquals('alta', $resultado[1]['adm_accion_descripcion']);
        $this->assertEquals('alta', $resultado[1]['descripcion_select']);
        errores::$error = false;
    }

}

