<?php

include_once 'PHPUnit/Framework.php';
include_once 'coorg/coorg.class.php';

class CoOrgTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		CoOrg::setSite('http://www.test.info/');
		CoOrg::spoofReferrer('http://www.test.info/some/part/of/the/site');
		CoOrg::init('coorg/tests/mocks/');
	}
	
	public function tearDown() {
		CoOrg::clear();
	}

	public function testProcessNormalRequest() {
		CoOrg::process('alpha/beta/gamma/delta/');
		
		$this->assertTrue(AlphaController::$betaExecuted);
		$this->assertEquals(array('gamma', 'delta'), AlphaController::$betaParams);
	}
	
	/**
	 * @expectedException RequestNotFoundException
	*/
	public function testProcessNormalRequestControllerNotFound()
	{
		CoOrg::process('theta/epsilon');
	}
	
	/**
	 * @expectedException RequestNotFoundException
	*/
	public function testProcessNormalRequestActionNotFound()
	{
		CoOrg::process('alpha/epsilon');
	}
	
	/**
	 * @expectedException NotEnoughParametersException
	*/
	public function testProcessNormalRequestNotEnoughParameters()
	{
		CoOrg::process('alpha/fiveparameters/one/two/three');
	}
	
	public function testProcessIndexRequest() {
		CoOrg::process('alpha/');
		
		$this->assertTrue(AlphaController::$indexExecuted);
		$this->assertEquals(array('', ''), AlphaController::$indexParams);
		
		
		CoOrg::process('alpha/index/ola/alo');
		
		$this->assertTrue(AlphaController::$indexExecuted);
		$this->assertEquals(array('ola', 'alo'), AlphaController::$indexParams);
	}
	
	public function testProcessNormalRequestWithDefaultParams() {
		CoOrg::process('alpha/zeta/a/b/c');
		$this->assertTrue(AlphaController::$zetaExecuted);
		$this->assertEquals(array('a', 'b', 'c', 'Default1', 1, null), 
		                    AlphaController::$zetaParams);
		
	}
	
	public function testProcessDefaultRequest() {
		CoOrg::process('');
		$this->assertTrue(HomeController::$indexExecuted);
	}
	
	public function testProcessRecursiveRequest() {
		CoOrg::process('alpha/sub/action/p1/p2');
		$this->assertTrue(AlphaSubController::$actionExecuted);
		$this->assertEquals(array('p1', 'p2'), 
		                    AlphaSubController::$actionParams);
	}
	
	public function testProcessRecursiveIndexRequest() {
		CoOrg::process('alpha/sub');
		$this->assertTrue(AlphaSubController::$indexExecuted);
	}
	
	public function testProcessPostRequest() {
		CoOrg::process('alpha/post', array('p1' => 'value1',
		                                   'p2' => 'value2'));
		$this->assertTrue(AlphaController::$postExecuted);
		$this->assertEquals(array('value1', 'value2', '', 'default1'), 
		                    AlphaController::$postParams);
	}
	
	/**
	 * @expectedException RequestNotFoundException
	*/
	public function testProcessNormalRequestPostRequired() {
		CoOrg::process('alpha/postrequired', array('p1' => 'value1',
		                                           'p2' => 'value2'), false);
	}
	
	/**
	 * @expectedException RequestNotFoundException
	*/
	public function testProcessPostRequestWrongReferrer()
	{
		CoOrg::spoofReferrer('http://someothershit.com');
		CoOrg::process('alpha/post', array('p1' => 'value1',
		                                   'p2' => 'value2'), true);
	}

}

?>
