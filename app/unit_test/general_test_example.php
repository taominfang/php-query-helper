<?php

class ExampleTest extends PHPUnit_Framework_TestCase{

	private $abc;


	public static function setUpBeforeClass()
	{

		//only run once before any test case;

		parent::setUpBeforeClass();

		


		///

	}

	public static function tearDownAfterClass()
	{
		//only run once after all test case;
		parent::tearDownAfterClass();
	}

	protected function setUp()
	{
		//run before any test case;
		parent::setUp();
		$this->abc = "abc";


	}

	// called after the test functions are executed
	// this function is defined in PHPUnit_TestCase and overwritten
	// here
	protected function tearDown() {
		
		//run after any test case;
		parent::tearDown();
		unset($this->abc);
	}

	function testArrayHasKey(){
		 $this->assertArrayHasKey('foo', array('bar' => 'baz'));

	}


}

?>