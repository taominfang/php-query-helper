<?php 
class BasicController{
	
	public $tpl_name="";
	
	public $decorator="";
	
	
	public function pre_filter(){
		
	}
	
	public function post_filter(){
	
	}
	
	public function set($pName,$pValue){
		global $smarty;
		$smarty->assign($pName,$pValue);
	}
	
	public function setTpl($tplName){
		$this->tpl_name=$tplName;
	}
	
}


?>