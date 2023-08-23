<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\_ctl_parent;
use gamboamartin\system\html_controler;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _ctl_parentTest extends test {
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

    public function test_modifica(): void
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_SESSION['grupo_id'] = 2;
        $_GET['session_id'] = mt_rand(1,99999999);
        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_GET['registro_id'] = 1;

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_accion',descripcion: 'login');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        $html = new html();
        $html_controler = new html_controler($html);
        $modelo = new adm_accion($this->link);
        $link_obj = new links_menu($this->link, -1);

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_accion',descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        errores::$error = false;

        $ctl = new _ctl_parent(html: $html_controler, link: $this->link,modelo: $modelo,obj_link: $link_obj,paths_conf: $this->paths_conf);
        //$ctl = new liberator($ctl);


        errores::$error = false;

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'test';
        $adm_seccion['adm_menu_id'] = 1;
        $adm_seccion['adm_namespace_id'] = 1;
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test)->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_accion',descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        $_POST['adm_seccion_id'] = 1;
        $_POST['descripcion'] = 'test';
        $resultado = $ctl->modifica(false);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }


}

