<?php

include_once '/Users/mtao60/phplib/predis/examples/SharedConfigurations.php';


define('__PRJ_HEADER__', '_FILE_MODIFY__');

$f = new finder();
$f->main($single_server, $argv);

class fileDB {

    private $db = null;
    private $fp = null;

    public function __construct($filePath) {
        $this->fp = $filePath;
        if (is_file($filePath)) {
            $this->db = unserialize(file_get_contents($filePath));
        } else {
            $this->db = array();

            if ($this->save() === false) {
                throw new Exception("{$filePath} is not a writable file");
            }
        }
        
    }

    public function __destruct() {
        $this->save();
    }

    public function save() {
        if ($this->db !== null && $this->fp !== null) {
            return file_put_contents($this->fp, serialize($this->db));
        }
        return false;
    }

    public function get($key) {
        if (isset($this->db[$key])) {
            return $this->db[$key];
        } else {
            //echo "not find {$key}\n";
            return false;
        }
    }

    public function set($key, $val) {
        $this->db[$key] = $val;
    }

}

class finder {

    private $file_db;
    private $result;
    private $timestamp;
    private $prefix;

    function main($single_server, $argv) {


        echo PHP_EOL;






        if (empty($argv[1]) || empty($argv[2])) {
            $this->usage($argv, "Need Parameters");
            return;
        }

        if (!is_dir($argv[1])) {

            $this->usage($argv, "error: {$argv[1]} is not a folder");
            return;
            return;
        }



        echo "start .....\n";

        $this->prefix = __PRJ_HEADER__ . $argv[2];


        $this->file_db = new FileDB($argv[2]);

        $this->result = array();

        $this->timestamp = time();
        $this->scan($argv[1], "");

        var_dump($this->result);
        echo "done\n";
        $this->file_db->save();
    }

    function scan($root, $dir) {
        $d = dir("{$root}/{$dir}");


        while (false !== ($entry = $d->read())) {


            if (strpos($entry, ".") === 0) {
                continue;
            }

            $f = "{$root}/{$dir}/{$entry}";


            if (is_file($f)) {
                $st = stat($f);
                if (!empty($st['mtime'])) {
                    $lastMoTime = intval($st['mtime']);

                    $key = $this->prefix . $f;
                    $savedTime = $this->file_db->get($key);

                    if ($savedTime === false || intval($savedTime) < $lastMoTime) {


                        $this->result[] = array('dir' => $dir, 'fn' => $entry);
                    }

                    $this->file_db->set($key, "{$this->timestamp}");
                }
            } else if (is_dir($f)) {
                $this->scan($root, "{$dir}/{$entry}");
            }
        }
        $d->close();
    }

    function usage($argv, $errorMessage) {
        echo $errorMessage . PHP_EOL;
        echo "Usage: \n php {$argv[0]} need_scan_folder file_db_path\n";
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
