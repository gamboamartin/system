<?php
namespace tests\src\_ctl_base;


use gamboamartin\controllers\controlador_adm_grupo;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;

use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;


class initTest extends test {
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

    public function test_asigna_data_param(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $data_init = new stdClass();
        $key = 'a';
        $params = array();
        $data_init->a = 'z';
        $resultado = $ctl->asigna_data_param($data_init, $key, $params);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z',$resultado['a']);

    }

    public function test_data_init(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $controler = new controlador_adm_seccion(link: $this->link,paths_conf: $this->paths_conf);
        $controler->accion = 'lista';

        $resultado = $ctl->data_init($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_seccion',$resultado->next_seccion);
        $this->assertEquals('lista',$resultado->next_accion);
        $this->assertEquals('-1',$resultado->id_retorno);
        errores::$error = false;
    }

    public function test_init_data_param_get(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $compare = 'a';
        $data_init = new stdClass();
        $key = 'b';
        $_GET['b'] = 'x';
        $resultado = $ctl->init_data_param_get($compare, $data_init, $key);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado->b);
        errores::$error = false;
    }

    public function test_init_data_retornos(): void
    {
        $_SESSION['session_id'] = 1;
        $_SESSION['grupo_id'] = 1;
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';

        $controler = new controlador_adm_seccion(link: $this->link,paths_conf: $this->paths_conf);
        $controler->accion = 'lista';
        $resultado = $ctl->init_data_retornos($controler);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion', $resultado->next_seccion);
        $this->assertEquals('lista', $resultado->next_accion);
        $this->assertEquals('-1', $resultado->id_retorno);
        errores::$error = false;
    }

    public function test_init_get_param(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $data_init = new stdClass();
        $key = 'a';
        $_GET['a'] = 'x';
        $resultado = $ctl->init_get_param($data_init, $key);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->a);
        errores::$error = false;

    }

    public function test_init_keys_get(): void
    {
        errores::$error = false;
        $init = new _ctl_base\init();
        $init = new liberator($init);

        $data_init = new stdClass();
        $keys_init = array();
        $keys_init[] = 'a';
        $resultado = $init->init_keys_get($data_init, $keys_init);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_init_keys_get_data(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);


        $data_init = new stdClass();
        $_GET['next_seccion'] = 'a';
        $resultado = $ctl->init_keys_get_data($data_init);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->next_seccion);

        errores::$error = false;
    }

    public function test_init_param_get(): void
    {
        errores::$error = false;
        $ctl = new _ctl_base\init();
        $ctl = new liberator($ctl);

        $key = 'A';
        $_GET['A'] = 'X';
        $data_init = new stdClass();
        $resultado = $ctl->init_param_get($data_init, $key);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('X', $resultado->A);
        errores::$error = false;
    }



}

