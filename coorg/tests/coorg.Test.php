<?php

include_once 'PHPUnit/Framework.php';
include_once 'coorg/coorg.class.php';

class CoOrgTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		CoOrg::setSite('http://www.test.info/');
		CoOrg::spoofReferer('http://www.test.info/some/part/of/the/site');
		$config = new Config('config/tests.config.php');
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
	}
	
	public function tearDown() {
		CoOrg::clear();
	}

	public function testProcessNormalRequest() {
		CoOrg::process('alpha/beta/gamma/delta/');
		
		$this->assertTrue(AlphaController::$betaExecuted);
		$this->assertEquals(array('gamma', 'delta'), AlphaController::$betaParams);
	}
	
	public function testProcessNormalRequestControllerNotFound()
	{
		CoOrg::process('theta/epsilon');
		$this->assertEquals(Header::$errorCode, '404 Not Found');
		$this->assertEquals('theta/epsilon', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://www.test.info/some/part/of/the/site', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Request not found: Theta', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|notfound.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
	
	public function testProcessNormalRequestActionNotFound()
	{
		CoOrg::process('alpha/doesnotexists');
		$this->assertEquals(Header::$errorCode, '404 Not Found');
		$this->assertEquals('alpha/doesnotexists', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://www.test.info/some/part/of/the/site', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Request not found: AlphaDoesnotexists', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|notfound.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
	
	public function testProcessNormalRequestNotEnoughParameters()
	{
		CoOrg::process('alpha/fiveparameters/one/two/three');
		
		$this->assertEquals(Header::$errorCode, '500 Internal Server Error');
		$this->assertEquals('alpha/fiveparameters/one/two/three', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://www.test.info/some/part/of/the/site', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Not enough parameters supplied', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
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
	
	public function testProcessNormalRequestPostRequired() {
		CoOrg::process('alpha/postrequired', array('p1' => 'value1',
		                                           'p2' => 'value2'), false);
		                                           
		$this->assertEquals(Header::$errorCode, '500 Internal Server Error');
		$this->assertEquals('alpha/postrequired', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://www.test.info/some/part/of/the/site', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Wrong request method', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
	
	public function testProcessPostRequestWrongReferer()
	{
		CoOrg::spoofReferer('http://someothershit.com');
		CoOrg::process('alpha/post', array('p1' => 'value1',
		                                   'p2' => 'value2'), true);

		$this->assertEquals(Header::$errorCode, '500 Internal Server Error');
		$this->assertEquals('alpha/post', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://someothershit.com', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Wrong request method', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
	}

	public function testAutoloadOfModels()
	{
		$this->assertEquals('silly', Alpha::returnSilly());
		
		// Beta is a model in a plugin that is not loaded.
		$this->assertFalse(class_exists('Beta'));
		
		
		// See if loading models not in the model directory works
		$this->assertEquals('silly', AlphaNotInDir::returnSilly());
	}
}

?>
