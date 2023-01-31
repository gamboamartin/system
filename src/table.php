<?php
namespace gamboamartin\system;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class table{
    private errores $error;
    public function __construct(){

        $this->error = new errores();
    }

    private function atributos_css(array $class_css, array $id_css): array|string
    {
        $class_css_html = $this->css(atributo: 'class', css_data: $class_css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css', data: $class_css_html);
        }

        $id_css_html = $this->css(atributo: 'id', css_data: $id_css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css', data: $id_css_html);
        }

        $css = trim($class_css_html).trim($id_css_html);

        $css = $this->limpia_txt(txt: $css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css', data: $css);
        }

        $css = trim($css);
        if($css!==''){
            $css = ' '.$css;
        }

        return $css;


    }

    private function css(string $atributo, array $css_data): array|string
    {
        $css_html = '';
        foreach ($css_data as $css){
            $css_html.=$css.' ';
        }

        $css_html = $this->limpia_txt(txt: $css_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar texto',data:  $css_html);
        }

        if($css_html !== ''){
            $css_html = "$atributo='$css_html'";
        }

        $css_html = $this->limpia_txt(txt: $css_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar texto',data:  $css_html);
        }

        return $css_html;
    }


    private function contenido_table(array $class_css_td , array $childrens, int $cols_actions,
                                     stdClass $data_view, array $id_css_td ): array|string
    {
        $thead = $this->thead(names: $data_view->names);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener thead',data:  $thead);
        }


        $tbody = $this->tbody(class_css_td: $class_css_td, cols_actions: $cols_actions,
            id_css_td: $id_css_td, key_actions: $data_view->key_actions, keys_data: $data_view->keys_data, rows: $childrens);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tbody',data:  $tbody);
        }

        return $thead.$tbody;

    }

    private function integra_thead(string $ths): string
    {
        return "<thead><tr>$ths</tr></thead>";
    }

    /**
     * Limpia un valor de tipo txt
     * @param string $txt texto a limpiar
     * @return string
     * @version 0.311.40
     */
    private function limpia_txt(string $txt): string
    {
        $txt = trim($txt);

        $i = 0;
        while($i <= 10){
            $txt = str_replace('  ', ' ', $txt);
            $i++;
        }
        return trim($txt);

    }

    final public function table(array $childrens, int $cols_actions, stdClass $data_view, array $class_css_table = array(),
                          array $class_css_td = array(), array $id_css_table = array(), array $id_css_td = array()): array|string
    {
        $contenido_table = $this->contenido_table(class_css_td: $class_css_td, childrens: $childrens,
            cols_actions: $cols_actions, data_view: $data_view, id_css_td: $id_css_td);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar contenido table', data: $contenido_table);
        }
        $table = $this->table_genera(contenido_table: $contenido_table,class_css:  $class_css_table,id_css:  $id_css_table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar table', data: $table);
        }

        return $table;

    }

    final public function table_genera(string $contenido_table, array $class_css, array $id_css): array|string
    {
        $css_html = $this->atributos_css(class_css: $class_css,id_css:  $id_css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css', data: $css_html);
        }

        $css_html = $this->limpia_txt(txt: $css_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar css_html', data: $css_html);
        }

        return "<table $css_html >$contenido_table</table>";
    }

    private function tbody(array $class_css_td ,int $cols_actions, array $id_css_td, string $key_actions, array $keys_data, array $rows): array|string
    {

        $trs = $this->trs_rows(cols_actions: $cols_actions, class_css_td: $class_css_td,
            id_css_td: $id_css_td, keys_data: $keys_data, key_actions: $key_actions, rows: $rows);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar trs', data: $trs);
        }
        return "<tbody>$trs</tbody>";

    }

    /**
     * Integra un td para table
     * @param array $class_css
     * @param array $id_css
     * @param string|null $value
     * @return string
     */
    private function td(array $class_css, array $id_css, string|null $value): string
    {
        $value = $this->value_null(value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar value', data: $value);
        }

        $css_html = $this->atributos_css(class_css: $class_css,id_css:  $id_css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css', data: $css_html);
        }

        $css_html = $this->limpia_txt(txt: $css_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar css_html', data: $css_html);
        }

        $td = "<td$css_html>$value</td>";
        $td = $this->limpia_txt(txt: $td);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar td', data: $td);
        }

        return $td;
    }

    private function td_actions(bool $aplica_div, array $acciones, array $class_css, int $cols, array $id_css): array|string
    {
        $divs = '';
        foreach ($acciones as $link){
            $div = $this->td_contenido_link(aplica_div: $aplica_div, cols: $cols,link:  $link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar div', data: $div);
            }
            $divs.=$div;
        }
        $td = $this->td(class_css: $class_css, id_css: $id_css, value: $divs);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td', data: $td);
        }

        return $td;
    }

    private function td_contenido_link(bool $aplica_div, int $cols, string $link): string
    {
        $html = $link;
        if($aplica_div){
            $html = "<div class='col-md-$cols'>$link</div>";
        }
        return $html;
    }

    private function tds(array $class_css,array $id_css, array $keys, array $row): array|string
    {
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $row, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $row', data: $valida);
        }

        $tds = '';
        foreach($keys as $key){

            $value = $row[$key];
            $td = $this->td(class_css: $class_css, id_css: $id_css, value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar td', data: $td);
            }
            $tds .= $td;
        }
        return $tds;

    }

    private function tds_row(array $acciones, array $class_css_td, int $cols,array $id_css_td, array $keys, array $row): string
    {
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $row, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $row', data: $valida);
        }

        $tds_data = $this->tds(class_css: $class_css_td, id_css: $id_css_td, keys: $keys, row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar tds', data: $tds_data);
        }
        $td_action = $this->td_actions(aplica_div: false, acciones: $acciones, class_css: $class_css_td, cols: $cols,
            id_css: $id_css_td);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td_action', data: $tds_data);
        }

        $full_td = $tds_data;
        $full_td .= $td_action;
        return $full_td;
    }

    private function th(string $name): string
    {
        return "<th>$name</th>";
    }

    private function thead(array $names): array|string
    {
        $ths = $this->ths(names: $names);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra ths', data: $ths);
        }

        $thead = $this->integra_thead(ths:$ths);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra thead', data: $thead);
        }
        return $thead;
    }

    private function ths(array $names): array|string
    {

        $ths = '';
        foreach ($names as $name){
            $th = $this->th(name: $name);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integra th', data: $th);
            }
            $ths.=$th;
        }
        return $ths;

    }

    private function tr_row(array $acciones, array $class_css_td, int $cols_actions, array $id_css_td, array $keys, array $row): string
    {
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $row, valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $row', data: $valida);
        }

        $td = $this->tds_row(acciones: $acciones, class_css_td: $class_css_td, cols: $cols_actions,
            id_css_td: $id_css_td, keys: $keys, row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td', data: $td);
        }
        return "<tr>$td</tr>";
    }

    private function trs_rows(int $cols_actions, array $class_css_td, $id_css_td, array $keys_data, string $key_actions, array $rows): string|array
    {
        $trs = '';
        foreach ($rows as $row){

            foreach ($keys_data as $key_data){
                if(is_object($row)){
                    $row = (array)$row;
                }
                if(is_null($row[$key_data])){
                    $row[$key_data] = '';
                }
                if(!isset($row[$key_data])){
                    $row[$key_data] = '';
                }
            }

            $valida = (new validacion())->valida_existencia_keys(keys: $keys_data,registro:  $row, valida_vacio: false);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $row', data: $valida);
            }

            $acciones = $row[$key_actions];

            $tr = $this->tr_row(acciones: $acciones, class_css_td: $class_css_td,
                cols_actions: $cols_actions, id_css_td: $id_css_td, keys: $keys_data, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar tr', data: $tr);
            }
            $trs.=$tr;
        }
        return $trs;

    }

    /**
     * Si el elemento es null lo deja en vacio
     * @param string|null $value Valor a inicializar
     * @return string
     * @version 0.310.39
     */
    private function value_null(string|null $value): string
    {
        if(is_null($value)){
            $value = '';
        }
        return trim($value);
    }



}
