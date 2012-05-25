<?php 
class BasicController{
	
	public $tpl_name="";
	
	public $decorator="";
	
	protected $required_in_session=array();
	
	public function pre_filter(&$methodName=null){
		
		
		session_start();
		header("Cache-Control: no-cache, must-revalidate");
		//dd($this->required_in_session);
		if(!empty($this->required_in_session)){
			foreach($this->required_in_session as $one){
				if(empty($_SESSION[$one])){
					error_log("Can not find :".$one." in session");
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function post_filter(&$methodName=null){
		
	}
	
	public function set($pName,$pValue){
		global $smarty;
		$smarty->assign($pName,$pValue);
	}
	
	public function setTpl($tplName){
		$this->tpl_name=$tplName;
	}
	
	public function reportErrorByAjax($em){
		$this->decorator='ajax';
		$this->setTpl('/general_error.tpl');
		$this->set('errorMessage',$em);
	}
	
	public function reportError($em	){
		$this->setTpl('/general_error.tpl');
		$this->set('errorMessage',$em);
	}
}


?>