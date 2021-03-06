<?php
/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

class ControllerTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Session::$site = 'http://www.test.info/';
		Session::$referrer = 'http://www.test.info/some/part/of/the/site';
		$config = new Config('config/tests.config.php');
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
		CoOrgSmarty::$vars = array();
	}
	
	public function tearDown() {
		CoOrg::clear();
	}

	public function testVariablesSet()
	{
		CoOrg::config()->set('path', '/the/path/');
		CoOrg::process('en/alpha/show/someID/someParameter');
		$this->assertEquals('someID', AlphaController::$objectRetrieve);
		$this->assertEquals(array('coorgRequest' => 'alpha/show/someID/someParameter',
		                          'coorgUrl' => '/the/path/en/alpha/show/someID/someParameter',
		                          'staticPath' => '/the/path/static/',
		                          'coorgLanguage' => 'en',
		                          'value' => 'value', /* value, name and string come from the alphacontroller*/
		                          'name' => 'name', 
		                          'string' => 'string',
		                          'object' => 'someID',
		                          'param' => 'someParameter'),
		                    CoOrgSmarty::$vars);
	}
	
	public function testCoOrgURLOnHome()
	{
		CoOrg::config()->set('path', '/the/path/');
		CoOrg::process('en/');
		$this->assertEquals(array('coorgRequest' => '',
		                          'coorgUrl' => '/the/path/en',
		                          'staticPath' => '/the/path/static/',
		                          'coorgLanguage' => 'en',
		                          'language' => 'en'),
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
		$this->assertEquals('Unable to load template extends \'base.html.tpl|bogus.html.tpl\'', CoOrgSmarty::$vars['exception']->getMessage());
		
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
	
	public function testRedirectWithSpecialSjeezelDoesWork()
	{
		CoOrg::process('alpha/doredirect');
		$this->assertEquals('some/redirect/to/a$2fpagewith$3fstrangechars', Header::$redirect);
	}
	
	public function testAutoPost()
	{
		$c = new AlphaController;
		$this->assertTrue($c->isPost('update'));
		$this->assertTrue($c->isPost('save'));
		$this->assertTrue($c->isPost('delete'));
	}
}


?>
