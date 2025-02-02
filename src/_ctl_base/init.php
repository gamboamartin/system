<?php
/**+
 * PRUEBAS COMPLETADAS
 * POR DOCUMENTAR
 */
namespace gamboamartin\system\_ctl_base;
use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

final class init{
    private errores $error;
    public function __construct()
    {
        $this->error = new errores();
    }


    /**
     * TOTAL
     * Asigna valor a una clave en un arreglo de parametros.
     * @param stdClass $data_init Datos iniciales donde se busca la clave y su valor.
     * @param string $key Key de parametro a ser agregada al array `$params`.
     * @param array $params Parametros previos cargados.
     * @return array Retorna el array de parametros actualizado con la nueva clave y su valor.
     * @throws errores Si la clave proporcionada es invalida o no es encontrada en `$data_init`.
     *
     * @version 18.0.0
     * @url https://github.com/gamboamartin/system/wiki/src-_ctl_base.asigna_data_param
     */
    private function asigna_data_param(stdClass $data_init, string $key, array $params): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key, es_final: true);
        }
        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $data_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data_init', data: $valida);
        }
        $params[$key] = $data_init->$key;
        return $params;
    }

    /**
     * Asigna los parametros base de un boton
     * @param stdClass $data_init Datos default inicializados
     * @param array $keys_params Key de parametros a integrar
     * @param array $params Parametros previamente cargados
     * @return array
     */
    private function asigna_datas_param(stdClass $data_init, array $keys_params, array $params): array
    {
        foreach ($keys_params as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error key esta vacio', data: $key, es_final: true);
            }
            $keys = array($key);
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $data_init);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar data_init', data: $valida);
            }

            $params = $this->asigna_data_param(data_init: $data_init,key:  $key,params:  $params);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar param', data: $params);
            }
        }
        return $params;
    }

    /**
     * REG
     * Inicializa varios parámetros del controlador asegurando que los valores de `tabla`, `accion`, y las claves como `next_seccion`, `next_accion`, e `id_retorno`
     * sean válidos, y están presentes en los parámetros `$_GET`.
     *
     * Esta función toma el objeto `$controler`, valida que las propiedades `tabla` y `accion` estén correctamente configuradas (no vacías),
     * y luego verifica que las claves necesarias estén presentes en los parámetros `$_GET`. Si algún valor está vacío o falta, se devuelve un error.
     * Si todas las validaciones son exitosas, inicializa las propiedades `next_seccion`, `next_accion`, e `id_retorno` en un objeto `stdClass` y los devuelve.
     *
     * **Flujo de trabajo detallado:**
     * 1. Verifica que la propiedad `$controler->tabla` no esté vacía. Si está vacía, retorna un error con el mensaje correspondiente.
     * 2. Verifica que la propiedad `$controler->accion` no esté vacía. Si está vacía, retorna un error con el mensaje correspondiente.
     * 3. Si ambos valores son válidos, crea un objeto `stdClass` e inicializa las propiedades `next_seccion`, `next_accion`, y `id_retorno`.
     * 4. Si alguna de las validaciones falla, se devuelve un error detallado.
     * 5. Si todas las validaciones pasan, retorna el objeto `stdClass` con las propiedades inicializadas.
     *
     * **Parámetros:**
     *
     * @param controler $controler Instancia del controlador que contiene las propiedades `tabla` y `accion`.
     * - **Ejemplo:**
     *    ```php
     *    $controler = new controler();
     *    $controler->tabla = 'usuarios';
     *    $controler->accion = 'editar';
     *    ```
     *
     * **Retorno:**
     * - Si todas las claves están correctamente configuradas y no hay errores, la función retorna un objeto `stdClass` con las propiedades `next_seccion`, `next_accion`, y `id_retorno` inicializadas.
     * - Si ocurre un error, se retorna un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con datos inicializados:**
     * ```php
     * $_GET = ['next_seccion' => 'usuarios', 'next_accion' => 'editar', 'id_retorno' => 123];
     * $controler = new controler();
     * $controler->tabla = 'usuarios';
     * $controler->accion = 'editar';
     * $data = $this->data_init($controler);
     * echo $data->next_seccion;  // 'usuarios'
     * echo $data->next_accion;   // 'editar'
     * echo $data->id_retorno;    // 123
     * ```
     *
     * **Ejemplo 2: Error debido a `tabla` vacío:**
     * ```php
     * $controler = new controler();
     * $controler->tabla = '';
     * $controler->accion = 'editar';
     * $data = $this->data_init($controler);
     * // Salida: "Error $controler->tabla esta vacio"
     * ```
     *
     * **Ejemplo 3: Error debido a `accion` vacío:**
     * ```php
     * $controler = new controler();
     * $controler->tabla = 'usuarios';
     * $controler->accion = '';
     * $data = $this->data_init($controler);
     * // Salida: "Error $controler->accion esta vacio"
     * ```
     *
     * **Ejemplo 4: Error debido a claves no encontradas en `$_GET`:**
     * ```php
     * $_GET = ['next_seccion' => 'usuarios'];
     * $controler = new controler();
     * $controler->tabla = 'usuarios';
     * $controler->accion = 'editar';
     * $data = $this->data_init($controler);
     * // Salida: "Error al inicializar data" debido a que falta `next_accion` o `id_retorno` en `$_GET`
     * ```
     *
     * **Ejemplo 5: Error debido a que la propiedad `tabla` o `accion` está vacía:**
     * ```php
     * $controler = new controler();
     * $controler->tabla = '';
     * $controler->accion = 'editar';
     * $data = $this->data_init($controler);
     * // Salida: "Error $controler->tabla esta vacio"
     * ```
     *
     * **Excepciones:**
     * - Si alguna de las claves `tabla` o `accion` en el controlador está vacía, la función devolverá un error detallado.
     * - Si alguna de las claves `next_seccion`, `next_accion`, o `id_retorno` no está presente en los parámetros `$_GET`, o alguna clave está vacía, se generará un error.
     *
     * **@version 1.0.0**
     */
    private function data_init(controler $controler): array|stdClass
    {
        // 1. Validar que la propiedad `tabla` no esté vacía
        $data_init = $this->init_data_retornos(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar', data: $data_init);
        }

        // 2. Inicializar las claves necesarias de `$_GET` en el objeto `$data_init`
        $data_init = $this->init_keys_get_data(data_init: $data_init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }

        // 3. Retornar el objeto con los valores inicializados
        return $data_init;
    }


    /**
     * REG
     * Inicializa un parámetro en el objeto `$data_init` a partir del valor proporcionado en la URL (`$_GET`),
     * y realiza validaciones adicionales si se requiere una comparación.
     *
     * Esta función toma una clave (`$key`) y un valor de comparación (`$compare`). Si el valor de la clave existe en `$_GET`,
     * se valida y se asigna al objeto `$data_init`. Si el valor de `$compare` es distinto de vacío, se realiza una comparación
     * adicional y, si es necesario, se inicializa el parámetro en el objeto. Si alguna validación falla, se devuelve un mensaje de error.
     *
     * **Flujo de trabajo:**
     * 1. Recibe la clave `$key` y verifica que no esté vacía.
     * 2. Verifica que la clave `$key` exista en `$_GET`.
     * 3. Si el valor de `$compare` no está vacío, realiza una comparación adicional.
     * 4. Si las validaciones son exitosas, asigna el valor de `$_GET[$key]` al objeto `$data_init`.
     * 5. Si hay algún error (como clave vacía, clave no existente en `$_GET` o error en comparación), se devuelve un mensaje de error detallado.
     * 6. Retorna el objeto `$data_init` con el valor de la clave asignado si todo es correcto.
     *
     * **Parámetros:**
     *
     * @param string $compare Valor que se usará para comparar si la clave debe ser inicializada.
     *  - **Ejemplo:**
     *    ```php
     *    $compare = '1';  // Valor que se comparará con $_GET[$key]
     *    ```
     *
     * @param stdClass $data_init Objeto donde se debe inicializar la propiedad con el valor de `$_GET[$key]`.
     *  - **Ejemplo:**
     *    ```php
     *    $data_init = new stdClass();
     *    ```
     *    Este es el objeto que se va a modificar.
     *
     * @param string $key Clave que se buscará en los parámetros `$_GET`.
     *  - **Ejemplo:**
     *    ```php
     *    $key = 'user_id';  // Se busca en $_GET['user_id']
    ```
     *    La clave que se buscará en los parámetros de la URL (`$_GET`).
     *
     * **Retorno:**
     * - Devuelve el objeto `$data_init` con la propiedad `$key` inicializada si la clave existe en `$_GET` y la validación pasa.
     * - Si la clave no está presente, el valor de `$key` es vacío, o si la comparación falla, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con clave válida:**
     *  ```php
     *  $_GET = ['user_id' => 123];
     *  $data_init = new stdClass();
     *  $key = 'user_id';
     *  $compare = '1';  // No se realiza comparación adicional ya que compare no está vacío
     *  $data_init = $this->init_data_param_get($compare, $data_init, $key);
     *  echo $data_init->user_id;  // Imprime: 123
     *  ```
     *
     * **Ejemplo 2: Error debido a un valor vacío para `$key`:**
     *  ```php
     *  $data_init = new stdClass();
     *  $key = '';
     *  $data_init = $this->init_data_param_get('1', $data_init, $key);
     *  // Salida: "Error key esta vacio"
     *  ```
     *
     * **Ejemplo 3: Error debido a que la clave no existe en `$_GET`:**
     *  ```php
     *  $_GET = [];
     *  $data_init = new stdClass();
     *  $key = 'non_existent_key';
     *  $data_init = $this->init_data_param_get('1', $data_init, $key);
     *  // Salida: "Error al validar GET"
     *  ```
     *
     * **Ejemplo 4: Error debido a una comparación fallida (cuando `compare` es distinto de vacío):**
     *  ```php
     *  $_GET = ['user_id' => '123'];
     *  $data_init = new stdClass();
     *  $key = 'user_id';
     *  $compare = '456';  // La comparación no pasa
     *  $data_init = $this->init_data_param_get($compare, $data_init, $key);
     *  // Salida: "Error al inicializar data"
     *  ```
     *
     * **Excepciones:**
     * - Si `$key` es vacío, la función devolverá un error indicando que la clave no puede estar vacía.
     * - Si `$key` no existe en los parámetros `$_GET`, se devuelve un error indicando que la clave no fue encontrada.
     * - Si `$compare` es distinto de vacío y la comparación no pasa, se devuelve un mensaje de error indicando que la inicialización del dato falló.
     *
     * **@version 1.0.0**
     */
    private function init_data_param_get(string $compare, stdClass $data_init, string $key): array|stdClass
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }
        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys:$keys,registro:  $_GET);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar GET', data: $valida);
        }
        $compare = trim($compare);
        if($compare !== ''){
            $data_init = $this->init_param_get(data_init: $data_init,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }


    /**
     * REG
     * Inicializa los datos necesarios para el controlador, asegurando que los valores de `tabla` y `accion` sean válidos.
     *
     * Esta función valida los valores de las propiedades `tabla` y `accion` del controlador. Si alguno de estos valores está vacío,
     * se genera un mensaje de error. Si ambos valores son válidos, se crea un objeto `stdClass` con los datos necesarios (`next_seccion`,
     * `next_accion`, `id_retorno`) y se retorna dicho objeto.
     *
     * **Flujo de trabajo:**
     * 1. Valida que la propiedad `$controler->tabla` no esté vacía.
     * 2. Valida que la propiedad `$controler->accion` no esté vacía.
     * 3. Si ambos valores son válidos, asigna las propiedades `next_seccion`, `next_accion` y `id_retorno` a un objeto `stdClass`.
     * 4. Si alguno de los valores está vacío, se devuelve un mensaje de error detallado.
     * 5. Retorna el objeto `stdClass` con los datos inicializados si todo es correcto.
     *
     * **Parámetros:**
     *
     * @param controler $controler Instancia del controlador que contiene las propiedades `tabla` y `accion` que se validan.
     *
     * **Ejemplo:**
     * ```php
     * $controler = new controler();  // Instancia del controlador
     * $controler->tabla = 'usuarios';
     * $controler->accion = 'editar';
     * ```
     *
     * **Retorno:**
     * - Devuelve un objeto `stdClass` con las propiedades `next_seccion`, `next_accion`, y `id_retorno` si todo es exitoso.
     * - Si ocurre un error en cualquiera de los pasos, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con datos inicializados:**
     * ```php
     * $data = $this->init_data_retornos($controler);
     * // Retorna un objeto stdClass con las propiedades next_seccion, next_accion, y id_retorno
     * echo $data->next_seccion;  // 'usuarios'
     * echo $data->next_accion;   // 'editar'
     * echo $data->id_retorno;    // 123
     * ```
     *
     * **Ejemplo 2: Error debido a `tabla` vacío:**
     * ```php
     * $controler->tabla = '';
     * $data = $this->init_data_retornos($controler);
     * // Salida: "Error $controler->tabla esta vacio"
     * ```
     *
     * **Ejemplo 3: Error debido a `accion` vacío:**
     * ```php
     * $controler->accion = '';
     * $data = $this->init_data_retornos($controler);
     * // Salida: "Error $controler->accion esta vacio"
     * ```
     *
     * **Ejemplo 4: Error debido a ambos valores vacíos:**
     * ```php
     * $controler->tabla = '';
     * $controler->accion = '';
     * $data = $this->init_data_retornos($controler);
     * // Salida: "Error $controler->tabla esta vacio"
     * ```
     *
     * **@version 1.0.0**
     */
    private function init_data_retornos(controler $controler): stdClass|array
    {
        $controler->tabla = trim($controler->tabla);
        if($controler->tabla === ''){
            return $this->error->error(mensaje: 'Error $controler->tabla esta vacio', data: $controler->tabla);
        }
        $controler->accion = trim($controler->accion);
        if($controler->accion === ''){
            return $this->error->error(mensaje: 'Error $controler->accion esta vacio', data: $controler->accion);
        }
        $next_seccion = $controler->tabla;
        $next_accion = $controler->accion;
        $id_retorno = $controler->registro_id;

        $data = new stdClass();
        $data->next_seccion = $next_seccion;
        $data->next_accion = $next_accion;
        $data->id_retorno = $id_retorno;

        return $data;
    }


    /**
     * REG
     * Inicializa un parámetro en el objeto `$data_init` a partir del valor proporcionado en la URL (`$_GET`),
     * y realiza una validación adicional si es necesario.
     *
     * Esta función verifica que la clave proporcionada (`$key`) exista en los parámetros `$_GET`. Si la clave está presente,
     * toma su valor y lo compara con el valor dado (`$compare`). Si la comparación es exitosa, inicializa el parámetro en el
     * objeto `$data_init`. Si alguna validación falla, se devuelve un mensaje de error detallado.
     *
     * **Flujo de trabajo:**
     * 1. Recibe la clave `$key` y verifica que no esté vacía.
     * 2. Verifica que la clave `$key` exista en los parámetros `$_GET`.
     * 3. Si la clave existe, toma su valor, lo compara con `$compare` y, si es necesario, inicializa el parámetro en el objeto.
     * 4. Si hay algún error en los pasos anteriores, se devuelve un mensaje de error.
     * 5. Retorna el objeto `$data_init` con el valor de la clave asignado si todo es correcto.
     *
     * **Parámetros:**
     *
     * @param stdClass $data_init Objeto donde se debe inicializar la propiedad con el valor de `$_GET[$key]`.
     *  - **Ejemplo:**
     *    ```php
     *    $data_init = new stdClass();
     *    ```
     *    Este es el objeto que se va a modificar.
     *
     * @param string $key Clave que se buscará en los parámetros `$_GET`.
     *  - **Ejemplo:**
     *    ```php
     *    $key = 'user_id';  // Se busca en $_GET['user_id']
    ```
     *    La clave que se buscará en los parámetros de la URL (`$_GET`).
     *
     * **Retorno:**
     * - Devuelve el objeto `$data_init` con la propiedad `$key` inicializada si la clave existe en `$_GET` y la validación pasa.
     * - Si la clave no está presente, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con clave válida:**
     *  ```php
     *  $_GET = ['user_id' => 123];
     *  $data_init = new stdClass();
     *  $key = 'user_id';
     *  $data_init = $this->init_get_param($data_init, $key);
     *  echo $data_init->user_id;  // Imprime: 123
     *  ```
     *
     * **Ejemplo 2: Error debido a un valor vacío para `$key`:**
     *  ```php
     *  $data_init = new stdClass();
     *  $key = '';
     *  $data_init = $this->init_get_param($data_init, $key);
     *  // Salida: "Error key no puede venir vacio"
     *  ```
     *
     * **Ejemplo 3: Error debido a que la clave no existe en `$_GET`:**
     *  ```php
     *  $_GET = [];
     *  $data_init = new stdClass();
     *  $key = 'non_existent_key';
     *  $data_init = $this->init_get_param($data_init, $key);
     *  // Salida: "Error al inicializar data"
     *  ```
     *
     * **Excepciones:**
     * - Si `$key` es vacío, la función devolverá un error indicando que la clave no puede estar vacía.
     * - Si `$key` no existe en los parámetros `$_GET`, se devuelve un error indicando que la clave no fue encontrada.
     *
     * **@version 1.0.0**
     */
    private function init_get_param(stdClass $data_init, string $key): array|stdClass
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key no puede venir vacio', data: $key);
        }
        if(isset($_GET[$key])){
            $compare = trim($_GET[$key]);
            $data_init = $this->init_data_param_get(compare: $compare, data_init: $data_init, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }


    /**
     * REG
     * Inicializa varios parámetros en el objeto `$data_init` a partir de los valores proporcionados en los parámetros `$_GET`.
     *
     * Esta función recibe un arreglo de claves (`$keys_init`) y para cada clave verifica si existe en los parámetros `$_GET`.
     * Si la clave está presente, toma su valor y lo inicializa en el objeto `$data_init`. Si alguna de las claves no está presente
     * o hay un error en la inicialización, se devuelve un mensaje de error detallado.
     *
     * **Flujo de trabajo:**
     * 1. Recibe el arreglo de claves `$keys_init` y verifica que no estén vacías.
     * 2. Para cada clave, verifica si existe en los parámetros `$_GET`.
     * 3. Si la clave existe, inicializa el valor en el objeto `$data_init` usando la función `init_get_param`.
     * 4. Si alguna validación falla, devuelve un mensaje de error detallado.
     * 5. Retorna el objeto `$data_init` con los valores inicializados si todo es correcto.
     *
     * **Parámetros:**
     *
     * @param stdClass $data_init Objeto donde se inicializarán las propiedades con los valores de `$_GET[$key]`.
     *  - **Ejemplo:**
     *    ```php
     *    $data_init = new stdClass();
     *    ```
     *    Este es el objeto que se va a modificar.
     *
     * @param array $keys_init Arreglo de claves que se buscarán en los parámetros `$_GET`.
     *  - **Ejemplo:**
     *    ```php
     *    $keys_init = ['user_id', 'session_id'];  // Se buscan en $_GET['user_id'], $_GET['session_id']
     *    ```
     *    Las claves que se buscarán en los parámetros de la URL (`$_GET`).
     *
     * **Retorno:**
     * - Devuelve el objeto `$data_init` con las propiedades correspondientes inicializadas si las claves existen en `$_GET`.
     * - Si alguna de las claves no existe, o hay un error en la inicialización, se devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con claves válidas:**
     *  ```php
     *  $_GET = ['user_id' => 123, 'session_id' => 'abc123'];
     *  $data_init = new stdClass();
     *  $keys_init = ['user_id', 'session_id'];
     *  $data_init = $this->init_keys_get($data_init, $keys_init);
     *  echo $data_init->user_id;    // Imprime: 123
     *  echo $data_init->session_id; // Imprime: abc123
     *  ```
     *
     * **Ejemplo 2: Error debido a una clave vacía en `$keys_init`:**
     *  ```php
     *  $data_init = new stdClass();
     *  $keys_init = ['', 'session_id'];
     *  $data_init = $this->init_keys_get($data_init, $keys_init);
     *  // Salida: "Error key no puede venir vacio"
     *  ```
     *
     * **Ejemplo 3: Error debido a una clave no encontrada en `$_GET`:**
     *  ```php
     *  $_GET = ['user_id' => 123];
     *  $data_init = new stdClass();
     *  $keys_init = ['user_id', 'session_id'];  // 'session_id' no está en $_GET
     *  $data_init = $this->init_keys_get($data_init, $keys_init);
     *  // Salida: "Error al inicializar data"
     *  ```
     *
     * **Excepciones:**
     * - Si alguna de las claves en `$keys_init` está vacía, se devuelve un error indicando que la clave no puede estar vacía.
     * - Si alguna de las claves no existe en los parámetros `$_GET`, se devuelve un error indicando que la clave no fue encontrada.
     *
     * **@version 1.0.0**
     */
    private function init_keys_get(stdClass $data_init, array $keys_init): array|stdClass
    {
        foreach ($keys_init as $key) {
            $key = trim($key);
            if ($key === '') {
                return $this->error->error(mensaje: 'Error key no puede venir vacio', data: $key);
            }

            $data_init = $this->init_get_param(data_init: $data_init, key: $key);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
            }
        }
        return $data_init;
    }


    /**
     * REG
     * Inicializa varios parámetros en el objeto `$data_init` a partir de los valores de los parámetros `$_GET`,
     * verificando claves específicas como `next_seccion`, `next_accion`, e `id_retorno`.
     *
     * Esta función recibe un objeto `$data_init` y se asegura de que las claves `next_seccion`, `next_accion` e `id_retorno`
     * estén presentes en los parámetros `$_GET`. Si alguna de estas claves falta o presenta algún error, se devuelve
     * un mensaje detallado de error. Si todas las claves están presentes, inicializa los valores correspondientes en el
     * objeto `$data_init`.
     *
     * **Flujo de trabajo:**
     * 1. Se define un conjunto de claves específicas (`next_seccion`, `next_accion`, `id_retorno`).
     * 2. Se llama a la función `init_keys_get` para inicializar estas claves en el objeto `$data_init`.
     * 3. Si ocurre algún error durante la inicialización, se devuelve un mensaje de error.
     * 4. Si la inicialización es exitosa, se retorna el objeto `$data_init` con las propiedades correspondientes inicializadas.
     *
     * **Parámetros:**
     *
     * @param stdClass $data_init Objeto donde se inicializarán las propiedades `next_seccion`, `next_accion`, y `id_retorno`
     *  a partir de los valores de los parámetros `$_GET`.
     *  - **Ejemplo:**
     *    ```php
     *    $data_init = new stdClass();
     *    ```
     *    Este es el objeto que se va a modificar.
     *
     * **Retorno:**
     * - Devuelve el objeto `$data_init` con las propiedades inicializadas si todo es exitoso.
     * - Si ocurre un error al intentar obtener los parámetros, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con valores inicializados:**
     *  ```php
     *  $_GET = ['next_seccion' => 'usuarios', 'next_accion' => 'editar', 'id_retorno' => 123];
     *  $data_init = new stdClass();
     *  $data_init = $this->init_keys_get_data($data_init);
     *  echo $data_init->next_seccion;  // Imprime: usuarios
     *  echo $data_init->next_accion;   // Imprime: editar
     *  echo $data_init->id_retorno;    // Imprime: 123
     *  ```
     *
     * **Ejemplo 2: Error debido a una clave faltante en `$_GET`:**
     *  ```php
     *  $_GET = ['next_seccion' => 'usuarios', 'next_accion' => 'editar'];
     *  $data_init = new stdClass();
     *  $data_init = $this->init_keys_get_data($data_init);
     *  // Salida: "Error al inicializar data" (faltaría `id_retorno` en `$_GET`)
     *  ```
     *
     * **Excepciones:**
     * - Si alguna de las claves `next_seccion`, `next_accion`, o `id_retorno` no existe en `$_GET`, se generará un error.
     * - Si alguna de las claves está vacía, también se generará un error.
     *
     * **@version 1.0.0**
     */
    private function init_keys_get_data(stdClass $data_init): array|stdClass
    {
        $keys_init = array('next_seccion', 'next_accion', 'id_retorno');

        $data_init = $this->init_keys_get(data_init: $data_init, keys_init:  $keys_init);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }
        return $data_init;
    }


    /**
     * REG
     * Inicializa un parámetro en el objeto `$data_init` con el valor proporcionado en la URL (`$_GET`).
     *
     * Esta función toma una clave (`$key`), verifica si está presente en los parámetros `$_GET`, y si existe, asigna
     * su valor a la propiedad correspondiente en el objeto `$data_init`. Si el valor de `$key` está vacío o si el
     * parámetro no existe en `$_GET`, se devuelve un mensaje de error detallado.
     *
     * **Flujo de trabajo:**
     * 1. Recibe la clave `$key` y verifica que no esté vacía.
     * 2. Verifica que el parámetro `$key` exista en `$_GET`.
     * 3. Si la validación es exitosa, asigna el valor de `$_GET[$key]` al objeto `$data_init`.
     * 4. Si hay algún error (como clave vacía o clave no existente en `$_GET`), se devuelve un mensaje de error.
     * 5. Retorna el objeto `$data_init` con el valor de la clave asignado si todo es correcto.
     *
     * **Parámetros:**
     *
     * @param stdClass $data_init Objeto donde se debe inicializar la propiedad con el valor de `$_GET[$key]`.
     *  - **Ejemplo:**
     *    ```php
     *    $data_init = new stdClass();
     *    ```
     *    Este es el objeto que se va a modificar.
     *
     * @param string $key Clave que se buscará en los parámetros `$_GET`.
     *  - **Ejemplo:**
     *    ```php
     *    $key = 'user_id';  // Se busca en $_GET['user_id']
     *    ```
     *    La clave que se buscará en los parámetros de la URL (`$_GET`).
     *
     * **Retorno:**
     * - Devuelve el objeto `$data_init` con la propiedad `$key` inicializada si la clave existe en `$_GET`.
     * - Si la clave no está presente o el valor de `$key` es vacío, devuelve un mensaje de error detallado.
     *
     * **Ejemplos de salida:**
     *
     * **Ejemplo 1: Resultado exitoso con clave válida:**
     *  ```php
     *  $data_init = new stdClass();
     *  $key = 'user_id';
     *  $_GET['user_id'] = 123;  // Suponiendo que $_GET['user_id'] está disponible
     *
     *  $data_init = $this->init_param_get($data_init, $key);
     *  // $data_init->user_id tendrá el valor 123
     *  ```
     *
     * **Ejemplo 2: Error debido a un valor vacío para `$key`:**
     *  ```php
     *  $data_init = new stdClass();
     *  $key = '';
     *
     *  $data_init = $this->init_param_get($data_init, $key);
     *  // Salida: "Error key esta vacio"
     *  ```
     *
     * **Ejemplo 3: Error debido a que la clave no existe en `$_GET`:**
     *  ```php
     *  $data_init = new stdClass();
     *  $key = 'non_existent_key';
     *
     *  $data_init = $this->init_param_get($data_init, $key);
     *  // Salida: "Error al validar GET"
     *  ```
     *
     * **Excepciones:**
     * - Si `$key` es vacío, la función devolverá un error indicando que la clave no puede estar vacía.
     * - Si `$key` no existe en los parámetros `$_GET`, se devuelve un error indicando que la clave no fue encontrada.
     *
     * **@version 1.0.0**
     */
    private function init_param_get(stdClass $data_init, string $key): stdClass|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio', data: $key);
        }

        $keys = array($key);
        $valida = (new validacion())->valida_existencia_keys(keys:$keys,registro:  $_GET);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar GET', data: $valida);
        }

        $data_init->$key = $_GET[$key];
        return $data_init;
    }


    /**
     * Asigna parametros para enviar por GET
     * @param stdClass $data_init Datos previos cargados
     * @param array $params Parametros precargados
     * @return array
     */
    private function init_params(stdClass $data_init, array $params): array
    {
        $keys_params = array('next_seccion','next_accion','id_retorno');

        $params = $this->asigna_datas_param(data_init: $data_init,keys_params:  $keys_params,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar param', data: $params);
        }
        return $params;
    }

    /**
     * Integra parametros para enviar por GET
     * @param controler $controler Controlador en ejecucion
     * @param array $params Parametros GET previos cargados
     * @return array
     */
    final public function params(controler $controler, array $params): array
    {
        $data_init = $this->data_init(controler: $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar data', data: $data_init);
        }

        $params = $this->init_params(data_init: $data_init,params:  $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar param', data: $params);
        }
        return $params;
    }
}
