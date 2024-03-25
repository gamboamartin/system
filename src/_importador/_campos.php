<?php
namespace gamboamartin\system\_importador;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\errores\errores;
use PDO;

class _campos
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function adm_campos(PDO $link, string $tabla): array
    {
        $modelo_adm_campo = new adm_campo(link: $link);

        $adm_campos = $modelo_adm_campo->campos_by_seccion(adm_seccion_descripcion: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_campos',data:  $adm_campos);
        }

        $adm_campos = $this->limpia_adm_campos_full(adm_campos: $adm_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar adm_campos',data:  $adm_campos);
        }
        return $adm_campos;

    }

    private function campo_valida(array $adm_campos, string $campo_db)
    {
        $campo_valida = array();
        foreach ($adm_campos as $adm_campo){
            if($adm_campo['adm_campo_descripcion'] === $campo_db){
                $campo_valida = $adm_campo;
                break;
            }
        }
        return $campo_valida;

    }

    private function limpia_adm_campos_full(array $adm_campos): array
    {
        foreach ($adm_campos as $indice=>$adm_campo){
            $adm_campos = $this->limpia_campos(adm_campo: $adm_campo,adm_campos:  $adm_campos,indice:  $indice);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar adm_campos',data:  $adm_campos);
            }
        }

        return $adm_campos;

    }
    private function limpia_campos(array $adm_campo, array $adm_campos, int $indice): array
    {
        if($adm_campo['adm_campo_descripcion'] === 'usuario_alta_id'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'usuario_update_id'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'fecha_alta'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'fecha_update'){
            unset($adm_campos[$indice]);
        }
        if($adm_campo['adm_campo_descripcion'] === 'predeterminado'){
            unset($adm_campos[$indice]);
        }

        return $adm_campos;

    }

    private function tipo_dato_valida(array $adm_campos, string $campo_db): array|string
    {
        $campo_valida = $this->campo_valida(adm_campos: $adm_campos,campo_db:  $campo_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campo_valida',data:  $campo_valida);
        }

        return trim($campo_valida['adm_tipo_dato_codigo']);

    }

}
