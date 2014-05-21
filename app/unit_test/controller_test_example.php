<?php

// if do not close redirect output, session_start and header funciton will report error!
ob_start();
include_once '../system/basic_controller.php';
class ExampleTest extends PHPUnit_Framework_TestCase{

	private $abc;


	public static function setUpBeforeClass()
	{


		parent::setUpBeforeClass();

		//init smarty object
		global $smarty;
		$smarty_template_dir='../smarty/templates';
		require('../smarty/configs/smartmain.php');
		$smarty->caching=0;


		///

	}

	public static function tearDownAfterClass()
	{
		//
	}

	protected function setUp()
	{
		$this->abc = "abc";


	}

	// called after the test functions are executed
	// this function is defined in PHPUnit_TestCase and overwritten
	// here
	protected function tearDown() {
		// delete your instance
		header_remove(); // <-- VERY important.
		parent::tearDown();
		unset($this->abc);
	}

	function test1ExampleAjax(){
		include_once '../controllers/example.php';

		$controller=new ExampleController;

		$parameters=array();

		$preResult=true;

		$method="ajax";

		if(method_exists($controller,'pre_filter')){

			$preResult=$controller->pre_filter($method);
		}


		if( $preResult !== false){



			$controller->$method($parameters);

		}

		if(method_exists($controller,'post_filter')){

			$controller->post_filter($method);
		}


	}


}

?>