<?php

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
			if(empty($_REQUEST['host']) && empty($_SESSION['host'])){
				throw new Exception('host is required');

			}
			else{
				$host=empty($_REQUEST['host'])?$_SESSION['host']:$_REQUEST['host'];
			}




			if(empty($_REQUEST['user']) && empty($_SESSION['user'])){
				throw new Exception('user is required');

			}
			else{
				$user=empty($_REQUEST['user'])?$_SESSION['user']:$_REQUEST['user'];
			}


			if(!empty($_REQUEST['password']) ){
				$password=$_REQUEST['password'];
			}
			
			else if(!empty($_SESSION['password']) ){
				$password=$_SESSION['password'];
			}

			else{
				$password='';
			}

			if(!empty($_REQUEST['port'])){
				$port=$_REQUEST['port'];
			}
			else if(!empty($_SESSION['port']) ){
				$port=$_SESSION['port'];
			}
			else{
				$port='3306';
			}



			if(!empty($_REQUEST['database'])){
				$database=$_REQUEST['database'];
			}
			
			else if(!empty($_SESSION['database']) ){
				$database=$_SESSION['database'];
			}
			else{
				$database='';
			}




			$db=new mysqli($host,$user,$password,'',$port);

			if($db->connect_error){
				$db=NULL;
				throw new Exception("Fail connect to {$user}@{$host}, reason: {$db->connect_error}");

			}

			$_SESSION['host']=$_REQUEST['host'];
			$_SESSION['user']=$_REQUEST['user'];
			$_SESSION['password']=$password;

			$_SESSION['port']=$port;


			$_dbs=fetch_arrays_from_sql($db, "show databases");
				
			$dbs=array();
				
			foreach($_dbs as $row){

				if($row[0] == $database){
					$c='checked="checked"';
				}
				else{
					$c='';
				}

				$dbs[]=array('name'=>$row[0],'ch'=>$c);
			}
				
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