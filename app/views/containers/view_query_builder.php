<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class view_query_builder extends view {

    public static $MYSQL_TYPE_2_PDO_TYPE_MAP = array(
        'int' => 'PDO::PARAM_INT',
        'bigint' => 'PDO::PARAM_INT',
        'tinyint' => 'PDO::PARAM_INT',
        'smallint' => 'PDO::PARAM_INT',
        'mediumint' => 'PDO::PARAM_INT'
    );

    public function generatPdoBinds($smtpName,$group=false) {
        foreach ($this->variables as $name => $setting) {
            if (!empty($setting['bind_var'])) {

                $pType = 'PDO::PARAM_STR';
                if (!empty($setting['bind_type'])) {
                    $by = strtolower($setting['bind_type']);
                    foreach (view_query_builder::$MYSQL_TYPE_2_PDO_TYPE_MAP as $mkey => $pv) {

                        if (strpos($by, $mkey) !== false) {
                            $pType = $pv;
                        }
                    }
                }

               

                if($group){
                    echo $smtpName . '->bindParam(\':' . $name . '\',$one["'.$name.'"]'  . ",{$pType});\n";
                }
                else{
                    echo $smtpName . '->bindParam(\':' . $name . '\',$' . $name . ",{$pType});\n";
                }

            }
        }
    }

    public function generatPdoFixStrBinds($smtpName) {
        foreach ($this->columns as $one) {
            if ($one['type'] === 'pure') {

                $pType = 'PDO::PARAM_STR';

                if (!empty($one['bind_type'])) {
                    $by = strtolower($one['bind_type']);
                    foreach (view_query_builder::$MYSQL_TYPE_2_PDO_TYPE_MAP as $mkey => $pv) {

                        if (strpos($by, $mkey) !== false) {
                            $pType = $pv;
                        }
                    }
                }
                if ($pType === 'PDO::PARAM_INT') {
                     echo '$temp_fix_value_for_bind=' . $one['value']. ";\n";
                } else {
                    echo '$temp_fix_value_for_bind=' . "'" . str_replace("'", "\\" . "'", $one['value']) . "';\n";
                }
                echo $smtpName . '->bindParam(\':fix_' . $one['column_name'] . '\',$temp_fix_value_for_bind, ' . "{$pType});\n";
            }
        }
    }

}

?>
