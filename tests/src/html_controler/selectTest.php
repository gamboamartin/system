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
use gamboamartin\system\html_controler\select;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class selectTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/system/config/generales.php';
        $this->paths_conf->database = '/var/www/html/system/config/database.php';
        $this->paths_conf->views = '/var/www/html/system/config/views.php';
    }

    public function test_data_keys(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $columns_ds = array();
        $key_descripcion = '';
        $key_descripcion_select = '';
        $resultado = $html->data_keys($columns_ds, $key_descripcion,$key_descripcion_select);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_genera_data_keys(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $columns_ds = array();
        $key_descripcion = '';
        $key_descripcion_select = '';
        $tabla = 'a';
        $resultado = $html->genera_data_keys($columns_ds, $key_descripcion,$key_descripcion_select, $tabla);


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_genera_values_selects(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $keys = new stdClass();
        $keys->id = 'a';
        $keys->descripcion_select = 'b';
        $registros = array();
        $registros[0]['a'] = 'x';
        $registros[0]['b'] = 'd';
        $registros[0]['a_id'] = 'd';
        $registros[0]['a_descripcion'] = 'd';
        $resultado = $html->genera_values_selects(true, $keys, $registros,'a');
       // print_r($resultado);exit;
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
        $html = new select();
        //$html = new liberator($html);

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

    public function test_integra_descripcion_select(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $aplica_default = true;
        $keys = new stdClass();
        $registro = array();
        $tabla = 'a';

        $keys->id = 'key_id';
        $keys->descripcion_select = 'descripcion_select';

        $registro['key_id'] = 'a';
        $registro['a_descripcion'] = 'b';

        $resultado = $html->integra_descripcion_select($aplica_default,$keys,$registro,$tabla);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['key_id']);
        $this->assertEquals('b', $resultado['a_descripcion']);
        $this->assertEquals('a b', $resultado['descripcion_select']);

        errores::$error = false;
    }

    public function test_keys_base(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $tabla = 'a';

        $resultado = $html->keys_base($tabla);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado->id);
        $this->assertEquals('a_descripcion_select', $resultado->descripcion_select);
        errores::$error = false;
    }

    public function test_key_descripcion(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $tabla = 'a';
        $key_descripcion = '';

        $resultado = $html->key_descripcion($key_descripcion,$tabla);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_descripcion', $resultado);
        errores::$error = false;
    }

    public function test_key_descripcion_select(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $key_descripcion_select = '';
        $tabla = 'a';

        $resultado = $html->key_descripcion_select($key_descripcion_select, $tabla);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_descripcion_select', $resultado);
        errores::$error = false;
    }

    public function test_key_descripcion_select_default(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $key_descripcion = 'descripcion';
        $keys = new stdClass();
        $registro = array();

        $keys->id = 'x';
        $keys->descripcion_select = 'xd';
        $registro['x'] = 'key_id';
        $registro['descripcion'] = 'xxx';

        $resultado = $html->key_descripcion_select_default($key_descripcion, $keys,$registro);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('key_id xxx', $resultado['xd']);
        errores::$error = false;
    }
    public function test_key_id(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $key_id = '';
        $tabla = 'a';

        $resultado = $html->key_id($key_id, $tabla);
        //print_r($resultado);exit;

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a_id', $resultado);
        errores::$error = false;

        $key_id = 'b';
        $tabla = 'a';

        $resultado = $html->key_id($key_id, $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('b', $resultado);

        errores::$error = false;

    }

    public function test_label(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $tabla = 'a_';
        $resultado = $html->label($tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);
        errores::$error = false;
    }

    public function test_label_(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $label = '';
        $tabla = 'a';
        $resultado = $html->label_($label, $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado);

        errores::$error = false;
    }

    public function test_name(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $key_id = '';
        $name = '';

        $resultado = $html->name($key_id, $name);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;


    }

    public function test_registros_select(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';

        $con_registros = true;
        $modelo = new adm_accion($this->link);
        $extra_params_keys = array();
        $columns_ds = array();
        $filtro = array();
        $not_in = array();
        $registros = array();
        $columns_ds[] = 'a';
        $extra_params_keys[] = 'b';

        $resultado = $html->registros_select(columns_ds: $columns_ds, con_registros: $con_registros, extra_params_keys: $extra_params_keys,
            filtro: $filtro, in: array(), key_value_custom: '', keys: $keys, modelo: $modelo, not_in: $not_in, registros: $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';

        $con_registros = true;
        $modelo = new adm_accion($this->link);
        $extra_params_keys = array();
        $columns_ds = array();
        $filtro = array();
        $not_in = array();
        $registros = array();
        $columns_ds[] = 'a';
        $extra_params_keys[] = 'b';
        $key_value_custom = 's';

        $resultado = $html->registros_select(columns_ds: $columns_ds, con_registros: $con_registros, extra_params_keys: $extra_params_keys,
            filtro: $filtro, in: array(), key_value_custom: $key_value_custom, keys: $keys, modelo: $modelo, not_in: $not_in, registros: $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


    }
    public function test_rows_select(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;

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
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['titulo'] = 'test';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new select();
        $html = new liberator($html);

        $modelo = new adm_accion($this->link);
        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';
        $extra_params_keys = array();
        $extra_params_keys[] = 'adm_seccion_descripcion';

        $resultado = $html->rows_select(columns_ds: array(), extra_params_keys: $extra_params_keys, filtro: array(),
            in: array(), key_value_custom: '', keys: $keys, modelo: $modelo, not_in: array());


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric( $resultado[0]['adm_accion_id']);
        $this->assertEquals('test', $resultado[0]['adm_accion_descripcion']);
        $this->assertEquals('adm_accion', $resultado[0]['adm_seccion_descripcion']);

        errores::$error = false;

        $modelo = new adm_accion($this->link);
        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';
        $extra_params_keys = array();
        $extra_params_keys[] = 'adm_seccion_descripcion';

        $resultado = $html->rows_select(columns_ds: array(), extra_params_keys: $extra_params_keys, filtro: array(),
            in: array(), key_value_custom: 'a', keys: $keys, modelo: $modelo, not_in: array());

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric( $resultado[0]['adm_accion_id']);
        $this->assertEquals('test', $resultado[0]['adm_accion_descripcion']);
        $this->assertEquals('adm_accion', $resultado[0]['adm_seccion_descripcion']);

    }

    public function test_value_select(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);


        $keys = new stdClass();
        $registro = array();
        $values = array();

        $keys->id = 'tabla_id';
        $keys->descripcion_select = 'tabla_ds';
        $registro['tabla_id'] ='1';
        $registro['tabla_ds'] ='ds';

        $resultado = $html->value_select($keys,$registro,$values);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[1]['tabla_id']);


        errores::$error = false;
    }

    public function test_value_select_row(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);


        $aplica_default = true;
        $keys = new stdClass();
        $registro = array();
        $tabla = 'table';
        $values = array();

        $keys->id = 'tabla_id';
        $keys->descripcion_select = 'descripcion_select';

        $registro['tabla_id'] = -1;
        $registro['table_descripcion'] = -1;

        $resultado = $html->value_select_row($aplica_default,$keys,$registro,$tabla,$values);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('-1', $resultado[-1]['tabla_id']);
        $this->assertEquals('-1 -1', $resultado[-1]['descripcion_select']);


        errores::$error = false;
    }

    public function test_values(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $aplica_default = true;
        $keys = new stdClass();
        $registros = array();
        $tabla = 'aa';
        $registros[0] = array();
        $keys->id = 'kid';
        $keys->descripcion_select = 'descripcion_select';

        $registros[0]['kid'] = 'x';
        $registros[0]['aa_descripcion'] = 'x';

        $resultado = $html->values($aplica_default,$keys,$registros,$tabla);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x x',$resultado['x']['descripcion_select']);


        errores::$error = false;

    }

    public function test_values_selects(): void
    {
        errores::$error = false;

        $html = new select();
        $html = new liberator($html);

        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';

        $con_registros = true;
        $modelo = new adm_accion($this->link);
        $extra_params_keys = array();
        $resultado = $html->values_selects(columns_ds: array(), con_registros: $con_registros,
            extra_params_keys: $extra_params_keys, filtro: array(), in: array(), key_value_custom: '',
            keys: $keys, modelo: $modelo, not_in: array(), registros: array(), aplica_default: true);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        foreach ($resultado as $key=>$res){

            $this->assertIsNumeric( $res['adm_accion_id']);
            $this->assertIsString( $res['adm_accion_descripcion']);
            $this->assertIsString( $res['descripcion_select']);
        }

        errores::$error = false;


        $keys = new stdClass();
        $keys->id = 'adm_accion_id';
        $keys->descripcion_select = 'adm_accion_descripcion';
        $keys->descripcion = 'adm_accion_descripcion';

        $con_registros = true;
        $modelo = new adm_accion($this->link);
        $extra_params_keys = array();
        $resultado = $html->values_selects(columns_ds: array(), con_registros: $con_registros,
            extra_params_keys: $extra_params_keys, filtro: array(), in: array(), key_value_custom: 'a',
            keys: $keys, modelo: $modelo, not_in: array(), registros: array(),aplica_default: true);
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

