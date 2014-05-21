<?php
function load_one_assoc_from_sql($db,$sql){
	$result=run_sql($db,$sql);

	if($result->num_rows >0){
		$row=$result->fetch_assoc();
		$result->close();
		return $row;
	}
	$result->close();
	return array();
}

function fetch_assoc_arrays_from_sql($db,$sql){
	$result=run_sql($db,$sql);
	$re=array();
	if($result->num_rows >0){
		while($row=$result->fetch_assoc()){
			$re[]=$row;
		}
	}
	$result->close();
	return $re;
}

function fetch_arrays_from_sql($db,$sql){
	$result=run_sql($db,$sql);
	$re=array();
	if($result->num_rows >0){
		while($row=$result->fetch_row()){
			$re[]=$row;
		}
	}
	$result->close();
	return $re;
}


function run_sql($db,$sql,$mode=MYSQLI_STORE_RESULT){
	Log::d($sql);
	$st=microtime(true);
	$result=$db->query($sql,$mode);
	$sp=sprintf('%.3f',(microtime(true)-$st));
	Log::d("The sql Spend {$sp} seconds");
	if(!$result){
		throw new Exception("SQL :[$sql] is wrong, error message from mysql:{$db->error}");
	}



	return $result;
}

/*
 * get the information from $_REQUEST and valid if by connect function, if ok, save them into $_SESSION
*/
function getDbInfoFromUser(){
	if(empty($_REQUEST['host'])){
		throw new Exception('host is required');

	}
	else{
		$host=$_REQUEST['host'];
	}

	if(empty($_REQUEST['user'])){
		throw new Exception('user is required');

	}
	else{
		$user=$_REQUEST['user'];
	}


	if(!empty($_REQUEST['password']) ){
		$password=$_REQUEST['password'];
	}


	else{
		$password='';
	}

	if(!empty($_REQUEST['port'])){
		$port=$_REQUEST['port'];
	}

	else{
		$port='3306';
	}



	if(!empty($_REQUEST['database'])){
		$database=$_REQUEST['database'];
	}


	else{
		$database='';
	}

	$db= new mysqli($host,$user,$password,'',$port);

	if($db === null || $db === false || !is_object($db) || $db->connect_errno){

		if(is_object($db) &&  $db->connect_error){
			throw new Exception("Connect error for {$user}@{$host} ,error message from db:{$db->connect_error}");
		}
		else{
			throw new Exception("Connect error for {$user}@{$host}");
		}

	}
	$db->close();

	$_SESSION['host']=$_REQUEST['host'];
	$_SESSION['user']=$_REQUEST['user'];
	$_SESSION['password']=$password;

	$_SESSION['port']=$port;
	$_SESSION['database']=$database;
}

/*
 * get a mysqli object from $_SESSION
*/
function getDBFromSession($database=null){

	
	if( empty($_SESSION['host'])){
		throw new Exception('host is required');

	}
	else{
		$host=$_SESSION['host'];
	}

	if( empty($_SESSION['user'])){
		throw new Exception('user is required');

	}
	else{
		$user=$_SESSION['user'];
	}

	if(!empty($_SESSION['password']) ){
		$password=$_SESSION['password'];
	}

	else{
		$password='';
	}

	if(!empty($_SESSION['port']) ){
		$port=$_SESSION['port'];
	}
	else{
		$port='3306';
	}

	if(empty($database)){
		if(!empty($_SESSION['database']) ){
			$database=$_SESSION['database'];
		}
		else{
			$database='';
		}
	}
	$db= @mysqli_connect($host,$user,$password,$database,$port);

	if($db === null || $db->connect_errno){
		throw new Exception("Connect error for {$user}@{$host} ,error message from db:{$db->connect_error}");
	}
	return $db;
}

/*
 * get all database name
*/

function get_all_database($db=null){
	if($db === null){
		$db=getDBFromSession();
		$closeFlag=1;
	}

	$_dbs=fetch_arrays_from_sql($db, "show databases");

	if(isset($closeFlag)){
		$db->close();
	}
	$dbs=array();

	foreach($_dbs as $row){

		$dbs[]=array('name'=>$row[0]);
	}
	return $dbs;
}

/*
 * get all tables name
*/

function get_all_tables($database,$db=null){
	if($db === null){
		$db=getDBFromSession($database);
		$closeFlag=1;
	}

	$_dbs=fetch_arrays_from_sql($db, "show tables");

	if(isset($closeFlag)){
		$db->close();
	}
	$tables=array();

	foreach($_dbs as $row){

		$tables[]=array('name'=>$row[0]);
	}
	return $tables;
}




