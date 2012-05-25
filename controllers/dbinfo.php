<?php
include_once '../system/dbfunction.php';
class DbinfoController extends BasicController{

	public function pre_filter($methodName=null){


		$this->required_in_session=array("main_database",'host','user','password');


		
		if( ! parent::pre_filter($methodName)){
				
			if(in_array($methodName, array('select_table_or_subquery'))){
				
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



	public function select_table_or_subquery(){


		try {
			srand(time(	));
			$tables=get_all_tables($_SESSION['main_database']);
				
			$this->set('random',rand(1000000,9999999));
			$this->set('tables',$tables);
			$this->decorator='ajax';
		} catch (Exception $e) {
			
			logException($e);
			$this->reportErrorByAjax($e->getMessage());
			return;
		}

	}
}

?>