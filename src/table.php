<?php
namespace gamboamartin\system;
use gamboamartin\errores\errores;
use stdClass;

class table{
    private errores $error;
    public function __construct(){

        $this->error = new errores();
    }

    public function contenido_table(array $childrens, int $cols_actions, stdClass $data_view): array|string
    {
        $thead = $this->thead(names: $data_view->names);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener thead',data:  $thead);
        }


        $tbody = $this->tbody(cols_actions: $cols_actions,rows:  $childrens, keys_data: $data_view->keys_data,key_actions:  $data_view->key_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tbody',data:  $tbody);
        }

        return $thead.$tbody;

    }


    private function integra_thead(string $ths): string
    {
        return "<thead><tr>$ths</tr></thead>";
    }

    private function tbody(int $cols_actions, array $rows, array $keys_data, string $key_actions): array|string
    {

        $trs = $this->trs_rows(cols_actions: $cols_actions,rows:  $rows,keys_data:  $keys_data,key_actions:  $key_actions);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar trs', data: $trs);
        }
        return "<tbody>$trs</tbody>";

    }

    private function td(string $value): string
    {
        return "<td>$value</td>";
    }

    private function td_actions(array $acciones, int $cols): array|string
    {
        $divs = '';
        foreach ($acciones as $link){
            $div = $this->td_contenido_link(cols: $cols,link:  $link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar div', data: $div);
            }
            $divs.=$div;
        }
        $td = $this->td(value: $divs);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td', data: $td);
        }

        return $td;
    }

    private function td_contenido_link(int $cols, string $link): string
    {
        return "<div class='col-md-$cols'>$link</div>";
    }

    private function tds(array $keys, array $row): array|string
    {
        $tds = '';
        foreach($keys as $key){
            $value = $row[$key];
            $td = $this->td(value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar td', data: $td);
            }
            $tds .= $td;
        }
        return $tds;

    }

    private function tds_row(array $acciones, int $cols, array $keys, array $row): string
    {
        $tds_data = $this->tds(keys: $keys,row:  $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar tds', data: $tds_data);
        }
        $td_action = $this->td_actions(acciones: $acciones,cols:  $cols);
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

    private function tr_row(array $acciones, int $cols_actions, array $keys, array $row): string
    {
        $td = $this->tds_row(acciones: $acciones,cols:  $cols_actions,keys:  $keys,row:  $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar td', data: $td);
        }
        return "<tr>$td</tr>";
    }

    private function trs_rows(int $cols_actions, array $rows, array $keys_data, string $key_actions): string
    {
        $trs = '';
        foreach ($rows as $row){
            $acciones = $row[$key_actions];

            $tr = $this->tr_row(acciones: $acciones,cols_actions:  $cols_actions, keys: $keys_data,row:  $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar tr', data: $tr);
            }
            $trs.=$tr;
        }
        return $trs;

    }



}
