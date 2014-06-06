<?php

class BasicController {



    protected $view;
    protected $required_in_session = array();

    public $redirect_url=null;


    public function redirect($url){
        $this->redirect_url=$url;
    }

    public function setView($v) {
        $this->view=$v;
    }

    public function getView(){
        return $this->view;
    }

    public function setLayout($layout_name) {
        $this->view->setLayout($layout_name);
    }

    public function pre_filter(&$methodName = null) {


        session_start();

        header("Cache-Control: no-cache, must-revalidate");
        //dd($this->required_in_session);
        if (!empty($this->required_in_session)) {
            foreach ($this->required_in_session as $one) {
                if (empty($_SESSION[$one])) {
                    error_log("Can not find :" . $one . " in session");
                    return false;
                }
            }
        }

        return true;
    }

    public function post_filter(&$methodName = null) {

    }

    public function set($pName, $pValue) {

        $this->view->set($pName, $pValue);
    }








}

?>