<?php

class IndexController extends BasicController {

    public function index() {

        
        
        $this->set("title", "I am Index page");
    }

    public function phpinfo() {
        
        ob_start();
        phpinfo();
        
        
        
        $info = ob_get_contents();
        ob_end_clean();
        
        $this->set('info', $info);
    }

}

?>