<?php
namespace tests\controllers;
use gamboamartin\controllers\controlador_adm_session;
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
        $this->paths_conf->generales = '/var/www/html/system/config/generales.php';
        $this->paths_conf->database = '/var/www/html/system/config/database.php';
        $this->paths_conf->views = '/var/www/html/system/config/views.php';
    }

    /**
     */
    public function test_alta(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

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

    /**
     */
    public function test_button_href(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';



        $accion = 'b';
        $etiqueta = 'd';
        $registro_id = -1;
        $seccion = 'a';
        $style = 'c';
        $resultado = $html->button_href($accion, $etiqueta, $registro_id, $seccion, $style);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' href='index.php?seccion=a&accion=b&registro_id=-1&session_id=1' class='btn btn-c col-sm-12'>d</a>", $resultado);
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

    /**
     */
    public function test_hidden(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        
        $name = 'a';
        $value = 'c';
        $resultado = $html->hidden($name, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<input type='hidden' name='a' value='c'>",$resultado);
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
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo' value='' |class|  required id='codigo' placeholder='Código' /></div></div>", $resultado);

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
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo_bis' value='' |class|  required id='codigo_bis' placeholder='Código BIS' /></div></div>", $resultado);

        errores::$error = false;
    }

    public function test_input_descripcion(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $row_upd = new stdClass();
        $cols = 1;
        $value_vacio = false;

        $resultado = $html->input_descripcion($cols, $row_upd, $value_vacio);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='descripcion' value='' |class|  required id='descripcion' placeholder='Descripcion' /></div></div>", $resultado);

        errores::$error = false;
    }

    public function test_input_id(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $row_upd = new stdClass();
        $cols = 1;
        $value_vacio = false;

        $resultado = $html->input_id($cols, $row_upd, $value_vacio);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='id' value='' |class|  required id='id' placeholder='Id' /></div></div>", $resultado);

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

    public function test_input_text_required(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $row_upd = new stdClass();
        $cols = 1;
        $disabled = false;
        $name = 'a';
        $place_holder = 'b';
        $value_vacio = false;

        $resultado = $html->input_text_required($cols, $disabled, $name, $place_holder, $row_upd, $value_vacio);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='a' value='' |class|  required id='a' placeholder='b' /></div></div>",$resultado);
        errores::$error = false;
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

    public function test_menu_lateral_title(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);


        $etiqueta = 'a';

        $resultado = $html->menu_lateral_title($etiqueta);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<span class='texto-menu-lateral'>a</span>", $resultado);
        errores::$error = false;
    }

    public function test_modifica(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $controler = new controlador_adm_session(link: $this->link, paths_conf: $this->paths_conf);


        $resultado = $html->modifica($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_obtener_select(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);


        $html_controler = new html_controler($html_);
        $html_controler = new liberator($html_controler);
        $campo = array();
        $campo['model'] = new adm_accion($this->link);
        $resultado = $html_controler->obtener_select($campo);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_params_select(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $name_model = 'a';
        $params = new stdClass();
        $resultado = $html->params_select($name_model, $params);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(12, $resultado->cols);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals('A', $resultado->label);
        $this->assertEquals(true, $resultado->required);
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

    public function test_select_aut(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $name_model = 'adm_seccion';
        $params = new stdClass();
        $selects = new stdClass();
        $resultado = $html->select_aut($this->link, $name_model, $params, $selects);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("l selectpicker color-secondary  adm_seccion_id' data-live-search='true' id='adm_seccion_id' n", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("='true' id='adm_seccion_id' name='adm_seccion_id' required ><option value=''  >Selecciona u", $resultado->adm_seccion_id);
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
        $resultado = $html->select_catalogo(cols: $cols,con_registros:  $con_registros,id_selected:  $id_selected,
            modelo:  $modelo,key_descripcion_select: 'adm_menu_id');

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='control-group col-sm-1'><div class='controls'><select class='form-contr", $resultado);
        $this->assertStringContainsStringIgnoringCase("><select class='form-control selectpicker color-secondary  adm_", $resultado);
        $this->assertStringContainsStringIgnoringCase("lor-secondary  adm_menu_id' data-live-search='true' id='adm_menu_id", $resultado);
        $this->assertStringContainsStringIgnoringCase("u_id' name='adm_menu_id'  ><option value=''  >Selecciona una opcion</option><op", $resultado);
        errores::$error = false;


        $cols = 1;
        $con_registros = true;
        $id_selected = -1;
        $modelo = new adm_menu($this->link);
        $resultado = $html->select_catalogo(cols: $cols,con_registros:  $con_registros,id_selected:  $id_selected,
            modelo:  $modelo,key_descripcion_select: 'adm_menu_id',name: 'x');



        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='control-group col-sm-1'><div class='controls'><sele", $resultado);
        $this->assertStringContainsStringIgnoringCase("lect class='form-control selectpicker color-secondar", $resultado);
        $this->assertStringContainsStringIgnoringCase("x' data-live-search='true' id='x' n", $resultado);
        $this->assertStringContainsStringIgnoringCase("'x' name='x'  ><option value=''  >S", $resultado);
        $this->assertStringContainsStringIgnoringCase("elecciona una opcion</option><option value='1'", $resultado);


        errores::$error = false;
    }

    public function test_selects_alta(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $link = $this->link;
        $keys_selects = array();
        $keys_selects['adm_seccion'] = new stdClass();
        $resultado = $html->selects_alta($keys_selects, $link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='control-group col-sm-12'><div class='cont", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("'controls'><select class='form-control selectp", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("l selectpicker color-secondary  adm_secci", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("m_seccion_id' data-live-search='true' id='ad", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("dm_seccion_id' name='adm_seccion_id' required ><option value='", $resultado->adm_seccion_id);
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

