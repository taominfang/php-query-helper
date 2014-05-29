<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 *
 */

class BerkeleyDB {

    protected $db_file_handle = false;

    public function __construct($dbFilePath, $openMode = "c") {
        $this->db_file_handle = dba_open($dbFilePath, $openMode);

        if ($this->db_file_handle === false) {
            throw new Exception("Failt to oepn {$dbFilePath}");
        }
    }

    public function __destruct() {
        $this->close();
    }

    public function close() {
        if ($this->db_file_handle !== FALSE) {
            dba_close($this->db_file_handle);

            $this->db_file_handle = false;
        }
    }

    public function fetch($key) {
        if ($this->db_file_handle !== false) {
            return dba_fetch($key, $this->db_file_handle);
        } else {
            throw new Exception("There is not opened db");
        }
    }

    public function save($key, $vale) {
        if ($this->db_file_handle !== false) {
            dba_replace($key, $vale, $this->db_file_handle);
        } else {
            throw new Exception("There is not opened db");
        }
    }

    function fetchAll() {

        $re = array();



        if ($this->db_file_handle === false) {
            throw new Exception("There is not opened db");
        } else {

            $fk = dba_firstkey($this->db_file_handle);

            if ($fk !== false) {
                $re[$fk] = dba_fetch($fk, $this->db_file_handle);
            }

            while (($nk = dba_nextkey($this->db_file_handle)) !== false) {
                $re[$nk] = dba_fetch($nk, $this->db_file_handle);
            }


            return $re;
        }
    }

    function delete($key) {
        dba_delete($key, $this->db_file_handle);
    }

}

?>
