<?php

class ExampleController extends BasicController {

    public function pre_filter(&$methodName = null) {
        parent::pre_filter($methodName);

        $this->view->addInternalJs("jquery-1.7.1.min.js");
        $this->view->addInternalJs("jquery-ui-1.8.17.custom.min.js");
        $this->view->addInternalCss("ui-lightness/jquery-ui-1.8.17.custom.css");
    }

    public function index() {


        $this->set('exa1', 'hello');
        $this->view->exa2 = array('world', ' .');

        $this->view->addInternalJs("logic_edit_debug.js");
        $this->view->addInternalCss("logic_edit.css");
    }

    public function ajax() {
        $this->setLayout("ajax.phtml");
        $tables = array("abcd", '2323');
        $this->set("ajax_output", json_encode($tables));
    }

}

?>