<?php
include_once '../system/dbfunction.php';
class DbinfoController extends BasicController{

	public function pre_filter($methodName=null){


		$this->required_in_session=array("main_database",'host','user','password');


		
		if( ! parent::pre_filter($methodName)){
				
			if(in_array($methodName, array(strtolower('selectTableOrSubquery')))){
				
				$this->reportErrorByAjax("Session Expired!");
			}
			else{
				
				redirect("/");
			}
			
			return false;
		}



	}

	public function post_filter($methodName){
		parent::post_filter($methodName);
	}



	public function selectTableOrSubquery(){


		try {
			
			$tables=get_all_tables($_SESSION['main_database']);
				
			if(!empty($database)){

			}
		} catch (Exception $e) {
			
			logException($e);
			$this->reportErrorByAjax($e->getMessage());
			return;
		}

	}
}

?>