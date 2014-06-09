<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of project
 *
 * @author mtao60
 */
include_once realpath( dirname(__FILE__)) . '/FileDB.php';

class Project extends FileDB {

    //put your code here

    public $host = "";
    public $port = "";
    public $user = "";
    public $password = "";
    public $engine = "";
    public $setting = array();

    public function __destruct() {
        parent::__destruct();
    }

    public function __construct($dbFilePath = null, $engine = null, $host = null, $port = null, $user = null, $password = null) {

        if ($dbFilePath !== null) {
            parent::__construct($dbFilePath);

            if ($engine !== null) {
                $this->engine = $engine;
                $this->save('engine', $engine);
            } else {
                $this->engine = $this->fetch('engine');
            }


            if ($host !== null) {
                $this->host = $host;
                $this->save('host', $host);
            } else {
                $this->host = $this->fetch('host');
            }


            if ($port !== null) {
                $this->port = $port;
                $this->save('port', $port);
            } else {
                $this->port = $this->fetch('port');
            }


            if ($user !== null) {
                $this->user = $user;
                $this->save('user', $user);
            } else {
                $this->user = $this->fetch('user');
            }


            if ($password !== null) {
                $this->password = $password;
                $this->save('password', $password);
            } else {
                $this->password = $this->fetch('password');
            }
            $s = $this->fetch('setting');

            if ($s !== false) {
                $this->setting = unserialize($s);
            }
        }
    }

    function set($key, $value) {
        $this->setting[$key] = $value;

        $this->save('setting', serialize($this->setting));
    }

    function get($key) {
        if(isset($this->setting[$key])){
            return $this->setting[$key];
        }
        else{
            return null;
        }
        
    }

}

?>
