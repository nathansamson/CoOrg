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
		CoOrg::config()->set('path', '/the/path/');
		CoOrg::process('alpha/show/someID/someParameter');
		$this->assertEquals(array('coorgRequest' => 'alpha/show/someID/someParameter',
		                          'coorgUrl' => '/the/path/alpha/show/someID/someParameter',
		                          'object' => 'someID',
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
