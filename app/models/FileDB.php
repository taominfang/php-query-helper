<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 *
 */

class FileDB {

    protected $db;
    protected $file_path;

    public function __construct($dbFilePath) {

        $this->file_path = $dbFilePath;
        
       

        if (is_file($dbFilePath)) {
            $ss = file_get_contents($dbFilePath);
            if ($ss === false) {
                throw new Exception("Failt to oepn {$dbFilePath}");
            }
            $this->db = unserialize($ss);
        } else {
            
            $this->db = array();
            $this->save2file();
        }
    }

    public function __destruct() {
        
    }

    public function close() {
        
    }

    public function fetch($key) {
        if (isset($this->db[$key])) {
            return $this->db[$key];
        }
        else {
            return false;
        }
    }

    public function save($key, $vale) {
        $this->db[$key] = $vale;
        $this->save2file();
    }

    public function fetchAll() {

        return $this->db;
    }

    public function delete($key) {
        unset($this->db[$key]);
        $this->save2file();
    }

    protected function save2file() {
        if (file_put_contents($this->file_path, serialize($this->db)) === false) {
            throw new Exception("Failt to write {$this->file_path}");
        }
    }

}

?>
