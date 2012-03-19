<?php 

class GeneratorController extends BasicController{
	
	
	public function query(){
		
		
		
		
		
	}
	
	public function pre_filter(){
	
	
		parent::pre_filter();
	}
	public function post_filter(){
		
		$this->decorator='ajax';
		$this->setTpl('/generator/query.tpl');
		parent::post_filter();
	}
}

?>