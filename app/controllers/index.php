<?php 

class IndexController extends BasicController{
	
	
	public function index(){
		
            phpinfo();
            print_r(dba_handlers());
		$this->set("title","I am Index page");
		
		
		
	}
}

?>