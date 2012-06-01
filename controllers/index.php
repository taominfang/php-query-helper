<?php
include_once '../system/dbfunction.php';
class IndexController extends BasicController{


	public function index(){

		
		if(empty($_SESSION['user'])){
			$this->set('user','');
		}
		else{
			$this->set('user',$_SESSION['user']);
		}
		if(empty($_SESSION['host'])){
			$this->set('host','');
		}
		else{
			$this->set('host',$_SESSION['host']);
		}
		if(empty($_SESSION['database'])){
			$this->set('database','');
		}
		else{
			$this->set('database',$_SESSION['database']);
		}

	}

	public function show_databases(){
		$db=NULL;

		if(empty($_REQUEST['usingcache'])){
			$_SESSION['usingcache']=0;
		}
		else{
			$_SESSION['usingcache']=1;
		}

		try {

			getDbInfoFromUser();



			$dbs=get_all_database();



			$this->set('dbs', $dbs);


		} catch (Exception $e) {
			$this->tpl_name='index';
			$this->set('error_message',$e->getMessage());
			return;
		}

		if($db!=NULL){
			$db->close();
		}


	}
	function test(){
		echo "";
	}

	function generate(){

		if(empty($_REQUEST['main_database'])){
			$this->reportError("main_database is required !");
			return;
		}

		else{
			$_SESSION['main_database'] = $_REQUEST['main_database'];
		}

		if(!empty($_REQUEST['select_job_id'])){
			$select_job_id=$_REQUEST['select_job_id'];
		}
		else{
			$select_job_id=uniqid('sel_job_');
		}

		$this->set('select_job_id', $select_job_id);
	}
}



?>