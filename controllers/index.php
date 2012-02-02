<?php 

class IndexController extends BasicController{
	
	
	public function index(){
		
		
		$this->set("title","Iam Index page");
		$this->setTpl("/index/index.tpl");
		
		
	}
}

?>