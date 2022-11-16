<?php
namespace tests\src;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
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
        $_SESSION['usuario_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion_pertenece_ins['id'] = 1;
        $adm_seccion_pertenece_ins['adm_seccion_id'] = 1;
        $adm_seccion_pertenece_ins['adm_sistema_id'] = 2;

        $r_alta = (new adm_seccion_pertenece($this->link))->alta_registro($adm_seccion_pertenece_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r_alta);
            print_r($error);
            exit;
        }



        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);


        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $resultado = $html->alta($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='text' name='codigo' value='' |class|  required id='codigo' placeholder='Codigo' /></div></div>", $resultado->codigo);

        errores::$error = false;
    }

    public function test_boton_link_permitido(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $accion_permitida = array();
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_accion_titulo'] = 'b';
        $accion_permitida['adm_seccion_descripcion'] = 'c';
        $accion_permitida['adm_accion_css'] = 'danger';
        $accion_permitida['adm_accion_es_status'] = 'inactivo';
        $indice = 1;
        $registro_id = 1;
        $rows = array();
        $rows[1] = array();

        $resultado = $html->boton_link_permitido($accion_permitida, $indice, $registro_id, $rows);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' href='index.php?seccion=c&accion=a&registro_id=1&session_id=1' class='btn btn-danger col-sm-12'>b</a>", $resultado[1]['acciones']['a']);
        errores::$error = false;
    }

    public function test_dates_template(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $params_select =new stdClass();
        $row_upd = new stdClass();

        $params_select->cols = '1';
        $params_select->disabled = true;
        $params_select->name = 'a';
        $params_select->place_holder = 'a';
        $params_select->value_vacio = false;

        $resultado = $html->dates_template($params_select, $row_upd);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><input type='date' name='a' value='' |class| disabled required id='a' placeholder='a' /></div></div>", $resultado);
        errores::$error = false;
    }

    /**
     */
    public function test_button_href(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

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


        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html_controler = new html_controler($html_);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);
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

    public function test_obtener_inputs(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $html_controler = new html_controler($html_);
        $html_controler = new liberator($html_controler);

        $campos_view = array();
        $resultado = $html_controler->obtener_inputs($campos_view);
        $this->assertIsArray($resultado);
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

    public function test_obtener_tipo_input(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $campo = array('type'=>'1');

        $resultado = $html->obtener_tipo_input($campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_params_base(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $data = new stdClass();
        $params = new stdClass();
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals(true, $resultado->required);
        $this->assertEquals(false, $resultado->disabled);
        $this->assertEquals(false, $resultado->value_vacio);
        $this->assertIsObject( $resultado->row_upd);
        $this->assertIsArray( $resultado->filtro);
        $this->assertEmpty( $resultado->filtro);
        $this->assertIsArray( $resultado->not_in);
        $this->assertEmpty( $resultado->not_in);
        $this->assertEmpty( $resultado->name);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertEmpty( $resultado->extra_params_keys);

        errores::$error = false;

        $data = new stdClass();
        $params = new stdClass();
        $params->disabled = true;
        $params->extra_params_keys = array('x'=>'d');
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertNotEmpty( $resultado->extra_params_keys);

        errores::$error = false;

        $data = new stdClass();
        $params = new stdClass();
        $params->disabled = true;
        $params->extra_params_keys = array('x'=>'d');
        $params->filtro = array('x'=>'d');
        $name = '';
        $resultado = $html->params_base($data, $name, $params);
        $this->assertIsArray( $resultado->extra_params_keys);
        $this->assertNotEmpty( $resultado->extra_params_keys);
        $this->assertIsArray( $resultado->filtro);
        $this->assertNotEmpty( $resultado->filtro);

        errores::$error = false;

    }

    public function test_params_input2(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);


        $html_controler = new html_controler($html_);
        $html_controler = new liberator($html_controler);
        $params = new stdClass();
        $name = '';
        $place_holder = '';
        $resultado = $html_controler->params_input2($params, $name, $place_holder);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue( errores::$error);
        errores::$error = false;

    }

    public function test_params_select_col_6(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);


        $html_controler = new html_controler($html_);
        $html_controler = new liberator($html_controler);
        $params = new stdClass();
        $label = '__a__';
        $resultado = $html_controler->params_select_col_6($params, $label);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(6, $resultado->cols);
        $this->assertEquals(true, $resultado->con_registros);
        $this->assertEquals(-1, $resultado->id_selected);
        $this->assertEquals(' a ', $resultado->label);
        $this->assertEquals(true, $resultado->required);
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

    public function test_retornos(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);


        $html_controler = new html_controler($html_);
        //$html_controler = new liberator($html_controler);
        $tabla = 'a';
        $registro_id= 1;
        $resultado = $html_controler->retornos($registro_id, $tabla);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<input type='hidden' name='id_retorno' value='1'>",$resultado->hidden_id_retorno);
        $this->assertEquals("<input type='hidden' name='seccion_retorno' value='a'>",$resultado->hidden_seccion_retorno);
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
        $this->assertIsNumeric( $resultado[0]['adm_accion_id']);
        $this->assertEquals('lista', $resultado[0]['adm_accion_descripcion']);
        $this->assertEquals('adm_seccion', $resultado[0]['adm_seccion_descripcion']);

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
        $namespace_model = 'gamboamartin\\administrador\\models';
        $resultado = $html->select_aut(link: $this->link,name_model:  $name_model, params: $params, selects: $selects,
            namespace_model: $namespace_model);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("l selectpicker color-secondary  adm_seccion_id' data-live-search='true' id='adm_seccion_id' n", $resultado->adm_seccion_id);
        $this->assertStringContainsStringIgnoringCase("='true' id='adm_seccion_id' name='adm_seccion_id' required ><option value=''  >Selecciona u", $resultado->adm_seccion_id);
        errores::$error = false;
    }

    public function test_select_aut2(): void
    {
        errores::$error = false;
        $html_ = new html();

        $html_controler = new html_controler($html_);
        $modelo = new adm_menu($this->link);
        //$html_controler = new liberator($html_controler);
        $params_select = new stdClass();
        $params_select->cols = '1';
        $params_select->con_registros = true;
        $params_select->id_selected = '-1';
        $params_select->disabled = false;
        $params_select->extra_params_keys = array();
        $params_select->filtro = array();
        $params_select->label = 'a';
        $params_select->not_in = array();
        $params_select->required = true;

        $resultado = $html_controler->select_aut2($modelo, $params_select);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("s='controls'><select class='form-control selectpicker color-secondary  adm_menu_id' data", $resultado);
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
        $keys_selects['adm_seccion']->namespace_model = 'gamboamartin\\administrador\\models';
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

    public function test_selects_integra(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);


        $campos_view = array();
        $keys_selects = array();

        $campos_view['selects'] = array();
        $campos_view['selects']['a'] = new adm_seccion($this->link);

        $resultado = $html->selects_integra($campos_view, $keys_selects);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("m-6'><div class='controls'><select class='form-control selectpicker color-secondar", $resultado->a);
        errores::$error = false;
    }

    public function test_style_btn(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        //$html = new liberator($html);

        $_SESSION['grupo_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $accion_permitida = array();
        $accion_permitida['adm_accion_css'] = 'info';
        $accion_permitida['adm_accion_es_status'] = 'inactivo';
        $accion_permitida['adm_accion_descripcion'] = 'a';
        $accion_permitida['adm_seccion_descripcion'] = 'a';

        $row = array();

        $resultado = $html->style_btn($accion_permitida, $row);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("info", $resultado);
        errores::$error = false;
    }

    public function test_style_btn_status(): void
    {
        errores::$error = false;
        $html_ = new html();
        $html = new html_controler($html_);
        $html = new liberator($html);
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;

        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $key_es_status = 'a';
        $row = array();
        $row['a'] = 'activo';
        $resultado = $html->style_btn_status($key_es_status, $row);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('success', $resultado);
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

        foreach ($resultado as $key=>$res){

            $this->assertIsNumeric( $res['adm_accion_id']);
            $this->assertIsString( $res['adm_accion_descripcion']);
            $this->assertIsString( $res['descripcion_select']);
        }

        errores::$error = false;
    }

}

