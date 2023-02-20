<?php
namespace tests\src;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use JsonException;
use stdClass;


class systemTest extends test {
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
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        //$controler = new liberator($controler);

        $resultado = $controler->alta(false);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div |class|><div |class|><input type='text' name='codigo' value=''",$resultado);
        errores::$error = false;
    }

    public function test_alta_bd(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_mes';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 40;
        $adm_seccion['descripcion'] = 'adm_mes';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_mes($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        //$controler = new liberator($controler);

        errores::$error = false;
        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $del);
            print_r($error);
            exit;
        }
        $_POST = array();
        $_POST['codigo'] = '1';
        $_POST['descripcion'] = '1';
        $resultado = $controler->alta_bd(false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_asignar_propiedad(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_mes';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        //$controler = new liberator($controler);

        $identificador = '1';
        $propiedades = array();
        $resultado = $controler->asignar_propiedad($identificador, $propiedades);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado['1']);
        errores::$error = false;
    }

    public function test_buttons_upd(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['registro_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion['id'] = 10;
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
        $adm_accion['adm_seccion_id'] = 10;
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);


        $resultado = $controler->buttons_upd();
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div |class|><div |class|><a |role| href='index.php?seccion=adm_accion&accion=status&registro_id=1&session_id=1' |class|>inactivo</a></div></div>",$resultado->status);
        $this->assertIsObject($resultado);
        errores::$error = false;
    }

    public function test_genera_botones_parent(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);

        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }



        $modelo = new adm_mes(link: $this->link);
        $resultado = $controler->genera_botones_parent($modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a role='button' title='Nueva adm_mes' href='index.php?seccion=adm_mes&accion=alta&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-warning col-sm-12' >Nueva adm_mes</a>",$resultado->adm_mes);
        $this->assertIsObject($resultado);
        errores::$error = false;


        $adm_mes['id'] = mt_rand(1,999999);
        $adm_mes['descripcion'] = 'adm_accion';
        $alta = (new adm_mes($this->link))->alta_registro($adm_mes);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $controler->genera_botones_parent($modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

    }

    /**
     */
    public function test_get_data(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = mt_rand(1,999999);
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        //$controler = new liberator($controler);


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

        $del = (new adm_accion_basica($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion_basica['id'] = 1;
        $adm_accion_basica['descripcion'] = 'lista';
        $adm_accion_basica['muestra_icono_btn'] = 'inactivo';
        $adm_accion_basica['muestra_titulo_btn'] = 'activo';

        $alta = (new adm_accion_basica($this->link))->alta_registro($adm_accion_basica);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        errores::$error = false;
        $controler->columnas_lista_data_table[] = 'adm_accion_id';
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2,$resultado['recordsTotal']);
        $this->assertCount(2,$resultado['data']);

        errores::$error = false;

        $_POST['n_rows_for_page'] = 2;
        $resultado = $controler->get_data(header:false);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2,$resultado['recordsTotal']);
        $this->assertCount(2,$resultado['data']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 21;
        $resultado = $controler->get_data(header:false);
        //print_r($resultado);exit;
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2,$resultado['recordsTotal']);
        $this->assertCount(2,$resultado['data']);
        $this->assertIsNumeric($resultado['data'][0]['adm_accion_id']);


        errores::$error = false;


        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
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
        $adm_accion['adm_seccion_id'] = 10;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $_GET['length'] = 15;
        $_GET['start'] = 0;
        $_GET['search']['value'] = 1;

        $controler->datatable['filtro'] = array('adm_accion.id');
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);
        $this->assertIsNumeric($resultado['data'][0]['adm_accion_id']);

        errores::$error = false;



        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion['id'] = 10;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = 1;
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
        $adm_accion['adm_seccion_id'] = 10;
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 1;
        $resultado = $controler->get_data(header:false);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);
        $this->assertIsNumeric($resultado['data'][0]['adm_accion_id']);

        errores::$error = false;

        $_GET['length'] = 15;
        $_GET['start'] = 20;
        $_GET['search']['value'] = 1;
        $resultado = $controler->get_data(header:false);


        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);

        errores::$error = false;





        $_GET['length'] = 15;
        $_GET['start'] = 21;
        $_GET['search']['value'] = 1;

        $resultado = $controler->get_data(header:false);

        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['recordsTotal']);
        $this->assertCount(1,$resultado['data']);
        $this->assertIsNumeric($resultado['data'][0]['adm_accion_id']);

        errores::$error = false;


    }

    public function test_init_row_upd(): void
    {
        errores::$error = false;




        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';


        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion['id'] = 10;
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
        $adm_accion['adm_seccion_id'] = 10;
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $controler = new liberator($controler);

        $resultado = $controler->init_row_upd();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->status);


        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_integra_button_parent(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);
        $controler = new liberator($controler);


        $resultado = $controler->integra_button_parent($modelo,'success');
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals("<a role='button' title='Nueva adm_accion' href='index.php?seccion=adm_accion&accion=alta&registro_id=-1&session_id=1&adm_menu_id=-1' class='btn btn-success col-sm-12' >Nueva adm_accion</a>",$resultado->adm_accion);
        errores::$error = false;
    }


    public function test_key_selects_txt(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = mt_rand(1,999999);
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        $controler = new liberator($controler);

        errores::$error = false;
        $keys_selects = array();

        $resultado = $controler->key_selects_txt($keys_selects);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_modifica(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['registro_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 999;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);


        //$controler = new liberator($controler);

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

        $controler->columnas_lista_data_table[] = 'adm_accion_id';
        $resultado = $controler->modifica(false);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_retorno_base(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

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


    public function test_rows_con_permisos(): void
    {
        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = 1;
        $_GET['seccion'] = 'adm_accion';
        $html = new html();
        $html_controler = new html_controler($html);

        $modelo = new adm_accion($this->link);
        $obj_link = new links_menu($this->link, -1);

        $controler = new system(html: $html_controler, link: $this->link, modelo: $modelo, obj_link: $obj_link,
            paths_conf: $this->paths_conf);

        $controler = new liberator($controler);


        $key_id = '';
        $rows = array();
        $seccion = 'a';

        $resultado = $controler->rows_con_permisos($key_id, $rows, $seccion);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }





}

