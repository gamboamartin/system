<?php
namespace gamboamartin\system\datatables;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;


class validacion_dt extends validacion{

    public function valida_base(array|string $column, string $indice): bool|array
    {
        if(is_string($column)){
            $column = trim($column);
            if($column === ''){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        if(is_array($column)){
            if(count($column) === 0){
                return $this->error->error(mensaje: 'Error column no puede venir vacia', data:  $column);
            }
        }
        $indice = trim($indice);
        if($indice === ''){
            return $this->error->error(mensaje: 'Error indice no puede venir vacia', data:  $indice);
        }
        return true;
    }

    /**
     * REG
     * Verifica que exista una sesión iniciada y que la clave `grupo_id` esté presente y sea válida
     * dentro de `$_SESSION`. Además, valida que el parámetro `$seccion` no sea una cadena vacía.
     *
     * - Primero verifica que `$_SESSION` esté definida (de lo contrario, registra un error).
     * - Luego, usa el método `valida_ids()` para revisar que `grupo_id` exista en la sesión y sea
     *   un ID válido (entero > 0, entre otros criterios).
     * - Finalmente, valida que `$seccion` no sea una cadena vacía.
     *
     * @param string $seccion Nombre o identificador de la sección a validar. No debe ser una cadena vacía.
     *
     * @return true|array Retorna `true` si todo es correcto. En caso de error (no hay sesión, falta `grupo_id`,
     *                    `grupo_id` no válido o `$seccion` vacío), se retorna un arreglo con la información
     *                    detallada del error.
     *
     * @example
     *  Ejemplo 1: Validación exitosa
     *  ------------------------------------------------------------------------------
     *  // Suponiendo que se ha iniciado sesión y $_SESSION['grupo_id'] existe y es válido.
     *  $_SESSION['grupo_id'] = 10;
     *  $seccion = "facturacion";
     *
     *  $resultado = $this->valida_data_column($seccion);
     *  if ($resultado === true) {
     *      echo "Todo correcto: hay sesión, grupo_id válido y sección no vacía.";
     *  } else {
     *      // Manejo de error. $resultado contendrá la información del problema.
     *  }
     *
     * @example
     *  Ejemplo 2: No hay sesión iniciada
     *  ------------------------------------------------------------------------------
     *  // Si $_SESSION no está definida o no se ha llamado a session_start(), el método
     *  // detectará que no hay sesión y retornará un error.
     *  unset($_SESSION);
     *
     *  $resultado = $this->valida_data_column("facturacion");
     *  // Se retorna un arreglo con la información del error: "Error no hay SESSION iniciada".
     *
     * @example
     *  Ejemplo 3: Falta 'grupo_id' en la sesión
     *  ------------------------------------------------------------------------------
     *  $_SESSION = []; // No existe 'grupo_id'
     *  $resultado = $this->valida_data_column("facturacion");
     *  // Se retornará un arreglo de error indicando que la clave 'grupo_id' no se encontró o no es válida.
     *
     * @example
     *  Ejemplo 4: `$seccion` está vacío
     *  ------------------------------------------------------------------------------
     *  $_SESSION['grupo_id'] = 10;
     *  $resultado = $this->valida_data_column("");
     *  // Se retorna un arreglo de error indicando que 'seccion' está vacía.
     */
    final public function valida_data_column(string $seccion): true|array
    {
        // 1. Verifica que exista sesión iniciada
        if (!isset($_SESSION)) {
            return $this->error->error(
                mensaje: 'Error no hay SESSION iniciada',
                data: array()
            );
        }

        // 2. Valida que la clave 'grupo_id' en $_SESSION sea un ID válido
        $keys = array('grupo_id');
        $valida = $this->valida_ids(keys: $keys, registro: $_SESSION);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar SESSION',
                data: $valida
            );
        }

        // 3. Verifica que $seccion no esté vacío
        $seccion = trim($seccion);
        if ($seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion esta vacia',
                data: $seccion
            );
        }

        return true;
    }


}
