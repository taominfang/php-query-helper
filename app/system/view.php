<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class view {

    public $title = "Unknow Title";
    public $layout = "default.phtml";
    public $template = "";
    public $content = "";
    public $scripts = "";
    public $styles = "";
    private $script_array = array();
    private $style_array = array();

    public function set($variableName, $value) {
        $this->$variableName = $value;
    }

    public function assign($variableName, $value) {
        $this->set($variableName, $value);
    }

    public function setLayout($layout_name) {
        $this->layout = $layout_name;
    }

    public function setTemplate($template) {

        $this->template = $template;
    }

    public function display($template) {
        $this->setTemplate($template);
        $this->rendering();
    }

    private function generateContent() {

        $templateFile = __PROJECT_ROOT__ . '/views/' . $this->template;
        if (!is_file($templateFile)) {
        Log::e("{$templateFile} is not a file");
            throw new Exception("Template file is not existent:" + $this->template);
        }
        ob_start();
        include $templateFile;
        $this->content = ob_get_contents();
        ob_end_clean();
        
       
    }

    public function rendering() {

        $this->generateContent();

        foreach ($this->script_array as $one) {
            $this->scripts.="<script src='{$one}'></script>";
        }

        foreach ($this->style_array as $one) {
            $this->styles.="<link rel='stylesheet' type='text/css' href='{$one}'>";
        }

        include __PROJECT_ROOT__ . '/layouts/' . $this->layout;
        
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
    }

}
