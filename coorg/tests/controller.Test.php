<?php

class ControllerTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		CoOrg::setSite('http://www.test.info/');
		CoOrg::spoofReferer('http://www.test.info/some/part/of/the/site');
		$config = new Config('config/tests.config.php');
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
		CoOrgSmarty::$vars = array();
	}
	
	public function tearDown() {
		CoOrg::clear();
	}

	public function testVariablesSet()
	{
		CoOrg::process('alpha/show/someID/someParameter');
		$this->assertEquals(array('object' => 'someID',
		                          'param' => 'someParameter'),
		                    CoOrgSmarty::$vars);
	}
	
	public function testBaseHTMLRendered()
	{
		CoOrg::process('alpha/show/someID/someParameter');
		$this->assertEquals(file_get_contents('coorg/tests/alphashowoutput.html'),
		                    CoOrgSmarty::$renderedOutput);
	}
	
	public function testTemplateNotFound()
	{
		CoOrg::process('alpha/bogus');
		$this->assertEquals(Header::$errorCode, '500 Internal Server Error');
		$this->assertEquals('alpha/bogus', CoOrgSmarty::$vars['request']);
		$this->assertEquals('http://www.test.info/some/part/of/the/site', CoOrgSmarty::$vars['referer']);
		$this->assertEquals('Template bogus.html.tpl not found', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
}


?>
