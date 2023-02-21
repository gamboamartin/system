<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\errores\errores;
use gamboamartin\system\datatables;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;


class datatablesTest extends test {
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





    public function test_acciones_permitidas(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        //$datatables = new liberator($datatables);
        $_SESSION['grupo_id'] = 1;
        $link = $this->link;
        $seccion = 'a';

        $resultado = $datatables->acciones_permitidas($link, $seccion);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $_SESSION['grupo_id'] = 2;
        $link = $this->link;
        $seccion = 'a';

        $resultado = $datatables->acciones_permitidas($link, $seccion);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        unset($_SESSION);

        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $link = $this->link;
        $seccion = 'adm_seccion';

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

        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $adm_accion['descripcion'] = 'xxx';
        $adm_accion['adm_seccion_id'] = '10';
        $adm_accion['es_lista'] = 'activo';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $datatables->acciones_permitidas($link, $seccion);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);

        errores::$error = false;
    }

    public function test_column_init(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = '';
        $indice = '';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = '';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->title);
        $this->assertEquals("b", $resultado->data);

        errores::$error = false;

        $column = array();
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = array('a');
        $indice = 'b';
        $resultado = $datatables->column_init($column, $indice);
        $this->assertEquals("b", $resultado->title);
        $this->assertEquals("b", $resultado->data);


        errores::$error = false;
    }

    public function test_column_titulo(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = array();
        $indice = '';
        $column_obj = new stdClass();
        $resultado = $datatables->column_titulo($column, $column_obj, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar column", $resultado['mensaje']);

        errores::$error = false;

        $column['titulo'] = 'a';
        $indice = 'z';
        $column_obj = new stdClass();
        $resultado = $datatables->column_titulo($column, $column_obj, $indice);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->title);
        errores::$error = false;
    }



    public function test_columns(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $columns = array();
        $datatable = array();

        $resultado = $datatables->columns($columns, $datatable);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $columns = array();
        $datatable = array();

        $columns['a'] = 'a';

        $resultado = $datatables->columns($columns, $datatable);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }



    public function test_columns_datatable(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $rows_lista = array();
        $seccion = 'a';
        $rows_lista[] = 'a';

        $resultado = $datatables->columns_datatable($rows_lista, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("A", $resultado['a_a']['titulo']);

        errores::$error = false;
    }

    public function test_column_datatable_init(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $datatables_ = new stdClass();

        $rows_lista = array();
        $rows_lista[''] = 's';
        $seccion = 'a';
        $resultado = $datatables->column_datable_init($datatables_, $rows_lista, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("S", $resultado['a_s']['titulo']);

        errores::$error = false;
    }

    /**
     */
    public function test_columns_defs(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = '';
        $indice = '';
        $targets = '1';
        $type = '';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = '';
        $targets = '1';
        $type = '';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column = 'a';
        $indice = 'a';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("1", $resultado->targets);
        $this->assertEquals(null, $resultado->data);
        $this->assertEquals('a', $resultado->type);
        $this->assertEquals('a', $resultado->rendered[0]);


        errores::$error = false;

        $column = array();
        $indice = 'a';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al validar datos", $resultado['mensaje']);

        errores::$error = false;

        $column['campos'] = array('a');
        $indice = 'b';
        $targets = '1';
        $type = 'a';
        $resultado = $datatables->columns_defs($column, $indice, $targets, $type);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("1", $resultado->targets);
        $this->assertEquals(null, $resultado->data);
        $this->assertEquals('a', $resultado->type);
        $this->assertEquals('b', $resultado->rendered[0]);
        $this->assertEquals('a', $resultado->rendered[1]);

        errores::$error = false;
    }

    public function test_columns_dt(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $datatables_ = new stdClass();
        $link = $this->link;
        $not_actions = array();
        $rows_lista = array();
        $seccion = 'a';
        $resultado = $datatables->columns_dt($datatables_, $link, $not_actions, $rows_lista, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_columns_title(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $columns = array();
        $key_row_lista = 'a';
        $seccion = 'a';
        $resultado = $datatables->columns_title($columns, $key_row_lista, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("A", $resultado['a_a']['titulo']);
        errores::$error = false;
    }

    public function test_datatable(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        //$datatables = new liberator($datatables);

        $filtro = array();
        $columns = array();
        $columns['z'] = 'a';
        $columns['b']['type'] = 'button';

        $resultado = $datatables->datatable(columns: $columns, filtro:$filtro);
       // print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado['columns'][0]->title);
        $this->assertEquals("z", $resultado['columns'][0]->data);
        $this->assertEquals("a", $resultado['columns'][0]->title);
        $this->assertEquals("z", $resultado['columns'][0]->data);
        errores::$error = false;



    }

    public function test_datatable_base_init(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $datatables = new datatables();
        //$datatables = new liberator($datatables);

        $datatables_ = new stdClass();
        $link = $this->link;
        $rows_lista = array();
        $seccion = 'a';
        $resultado = $datatables->datatable_base_init($datatables_, $link, $rows_lista, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }


    public function test_genera_column(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = 'x';
        $columns = array();
        $datatable = array();
        $indice = 'a';
        $index_button = -1;
        $resultado = $datatables->genera_column($column, $columns, $datatable, $indice, $index_button);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }





    public function test_integra_titulo(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = '';
        $indice = 'a';
        $column_obj = new stdClass();
        $resultado = $datatables->integra_titulo($column, $column_obj, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $column = array();
        $indice = '';
        $column_obj = new stdClass();
        $resultado = $datatables->integra_titulo($column, $column_obj, $indice);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $column = array();
        $column['titulo'] = array();
        $indice = 'a';
        $column_obj = new stdClass();
        $resultado = $datatables->integra_titulo($column, $column_obj, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->title);

        errores::$error = false;

        $column = array();
        $column['titulo'] = 'z';
        $indice = 'a';
        $column_obj = new stdClass();
        $resultado = $datatables->integra_titulo($column, $column_obj, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z', $resultado->title);

        errores::$error = false;
    }



    public function test_maqueta_column_obj(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = 'a';
        $indice = 'c';

        $resultado = $datatables->maqueta_column_obj($column, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->title);
        $this->assertEquals('c', $resultado->data);

        errores::$error = false;

        $column = array('z');
        $indice = 'c';

        $resultado = $datatables->maqueta_column_obj($column, $indice);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('c', $resultado->title);
        $this->assertEquals('c', $resultado->data);
        errores::$error = false;
    }

    public function test_titulo_column_datatable(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);
        $_SESSION['grupo_id'] = 1;

        $columns = array();
        $key_row_lista = 'a';
        $seccion = 'f';

        $resultado = $datatables->titulo_column_datatable($columns, $key_row_lista, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A', $resultado['f_a']['titulo']);
        errores::$error = false;
    }

    public function test_type(): void
    {
        errores::$error = false;
        $datatables = new datatables();
        $datatables = new liberator($datatables);

        $column = array();

        $resultado = $datatables->type($column);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('text', $resultado);
        errores::$error = false;
    }


}

