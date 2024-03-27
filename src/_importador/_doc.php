<?php
namespace gamboamartin\system\_importador;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class _doc
{

    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    final public function doc_importa(int $doc_tipo_documento_id, modelo $modelo_doc_documento): array|stdClass
    {
        $alta_doc = $this->genera_doc_importa(doc_tipo_documento_id:$doc_tipo_documento_id, modelo_doc_documento: $modelo_doc_documento);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar documento', data: $alta_doc);
        }
        $ruta = $alta_doc->registro_obj->doc_documento_ruta_absoluta;
        $extension = $alta_doc->registro_obj->doc_extension_codigo;

        $data = new stdClass();
        $data->ruta = $ruta;
        $data->extension = $extension;
        $data->doc_documento_id = $alta_doc->registro_id;

        return $data;

    }

    private function genera_doc_importa(int $doc_tipo_documento_id, modelo $modelo_doc_documento): array|stdClass
    {

        $doc_documento_ins = array();
        $doc_documento_ins['doc_tipo_documento_id'] = $doc_tipo_documento_id;

        $alta_doc = $modelo_doc_documento->alta_documento(registro: $doc_documento_ins,file: $_FILES['doc_origen']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar documento', data: $alta_doc);
        }
        return $alta_doc;

    }

}
