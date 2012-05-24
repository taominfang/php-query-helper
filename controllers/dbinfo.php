<?php

class DbinfoController extends BasicController{

	public function pre_filter(){


		parent::pre_filter();
	}
	public function post_filter(){
		parent::post_filter();
	}
	public function example(){


		$this->set("Name","Fred Irving Johnathan Bradley Peppergill",true);
		$this->set("FirstName",array("John","Mary","James","Henry"));
		$this->set("LastName",array("Doe","Smith","Johnson","Case"));
		$this->set("Class",array(array("A","B","C","D"), array("E", "F", "G", "H"),
		array("I", "J", "K", "L"), array("M", "N", "O", "P")));

		$this->set("contacts", array(array("phone" => "1", "fax" => "2", "cell" => "3"),
		array("phone" => "555-4444", "fax" => "555-3333", "cell" => "760-1234")));

		$this->set("option_values", array("NY","NE","KS","IA","OK","TX"));
		$this->set("option_output", array("New York","Nebraska","Kansas","Iowa","Oklahoma","Texas"));
		$this->set("option_selected", "NE");


	}

	public function showTables(){
		if(!empty($_REQUEST['database'])){
			$database=$_REQUEST['database'];
		}

		else if(!empty($_SESSION['database']) ){
			$database=$_SESSION['database'];
		}

		else{
			$database='';
		}

		$db=null;
		
		try{
			
		}
		catch (Exception $e){
			if($db !== null){
				
			}
		}
		$dbs=get_all_database();
		
		if(!empty($database)){
			
		}

	}
}

?>