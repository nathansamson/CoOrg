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

class CoOrgTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		CoOrgSmarty::$vars = array();
		CoOrg::setSite('http://www.test.info/');
		CoOrg::spoofReferer('http://www.test.info/some/part/of/the/site');
		$config = new Config('config/tests.config.php');
		$config->set('aside/main', array('home/alpha'));
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
		I18n::setLanguage('');
	}
	
	public function tearDown() {
		CoOrg::clear();
	}

	public function testProcessNormalRequest() {
		CoOrg::process('alpha/beta/gamma/delta$3fand$2fand$2e/');
		
		$this->assertTrue(AlphaController::$betaExecuted);
		$this->assertEquals(array('gamma', 'delta?and/and.'), AlphaController::$betaParams);
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
	
	public function testAside()
	{
		CoOrg::process('alpha/withaside/p1/p2');
		
		$this->assertEquals('alpha/withaside', HomeAlphaAside::$request);
		$this->assertEquals('p2', HomeAlphaAside::$p2);
		$this->assertEquals(array(), HomeAlphaAside::$widgetParams);
		$this->assertFalse(array_key_exists('asideVar',  CoOrgSmarty::$vars));
		
		$this->assertFalse(class_exists('HomeAlpha2Aside')); // This is not configured
	}
	
	public function testAsideWithParams()
	{
		CoOrg::config()->set('aside/main', array('home/alpha' => array('beta' => 'gamma')));
		CoOrg::process('alpha/withaside/p1/p2');
		
		$this->assertEquals('alpha/withaside', HomeAlphaAside::$request);
		$this->assertEquals('p2', HomeAlphaAside::$p2);
		$this->assertEquals(array('beta' => 'gamma'), HomeAlphaAside::$widgetParams);
		$this->assertFalse(array_key_exists('asideVar',  CoOrgSmarty::$vars));
	}
	
	public function testAsideOverwriteParam()
	{
		CoOrg::process('alpha/withaside/triggerSomethingBad/p2');
		
		$this->assertEquals('Can not overwrite template variable!', CoOrgSmarty::$vars['exception']->getMessage());
		$this->assertEquals('extends:base.html.tpl|systemerror.html.tpl', CoOrgSmarty::$renderedTemplate);
	}
	
	public function testI18nManual()
	{
		I18n::setLanguage('nl');
		
		CoOrg::process('alpha/sub/i18ntest');
		
		$this->assertEquals('Google is leuk', AlphaSubController::$i18ntest1);
		$this->assertEquals('shit van Google', AlphaSubController::$i18ntest2);
		$this->assertEquals('Dit bericht komt van alpha', AlphaSubController::$i18nfromAlpha);
		$this->assertEquals('Message not found with 1 paramaters', AlphaSubController::$notFoundWithParams);
		AlphaSubController::$i18ntest1 = '';
		AlphaSubController::$i18ntest2 = '';
		AlphaSubController::$i18nfromAlpha = '';
		AlphaSubController::$notFoundWithParams = '';
	}
	
	public function testI18nAuto()
	{
		CoOrg::process('nl/alpha/sub/i18ntest');
		
		$this->assertEquals('Google is leuk', AlphaSubController::$i18ntest1);
		$this->assertEquals('shit van Google', AlphaSubController::$i18ntest2);
		$this->assertEquals('Dit bericht komt van alpha', AlphaSubController::$i18nfromAlpha);
		$this->assertEquals('Message not found with 1 paramaters', AlphaSubController::$notFoundWithParams);
	}
	
	public function testI18nAutoWithoutPathPrefix()
	{
		CoOrg::process('nl/alpha/sub/doredirect');
		
		$this->assertEquals('alpha/sub/google', Header::$redirect);
	}
	
	public function testI18nAutoIndex()
	{
		CoOrg::process('nl/');
		
		$this->assertEquals(1, preg_match('/^extends:base.html.tpl\|(.*)home.html.tpl$/', CoOrgSmarty::$renderedTemplate));
		$this->assertEquals('nl', CoOrgSmarty::$vars['language']);
	}
	
	public function testI18nAutoWithPathPrefix()
	{
		$config = new Config('config/tests.config.php');
		$config->set('urlPrefix', ':language');
		$this->alternativeConfig($config);
		CoOrg::process('nl/alpha/sub/doredirect');
		
		$this->assertEquals('nl/alpha/sub/google', Header::$redirect);
	}
	
	public function testI18nOtherDefaultLanguage()
	{
		$config = new Config('config/tests.config.php');
		$config->set('defaultLanguage', 'nl');
		$this->alternativeConfig($config);
		CoOrg::process('/');
		$this->assertEquals('nl', CoOrgSmarty::$vars['language']);
	}

	public function testBeforeFilter()
	{
		CoOrg::process('alpha/beforeFilter/myName/myValue');

		$this->assertEquals('myValue', CoOrgSmarty::$vars['value']);
		$this->assertEquals('myName', CoOrgSmarty::$vars['name']);
		$this->assertEquals('ran', CoOrgSmarty::$vars['status']);
		$this->assertEquals('olajong', CoOrgSmarty::$vars['string']);
	}

	public function testBeforeFilterStops()
	{
		CoOrg::process('alpha/beforeFilter/myName/myStopCode');

		$this->assertEquals('myStopCode', CoOrgSmarty::$vars['value']);
		$this->assertEquals('myName', CoOrgSmarty::$vars['name']);
		$this->assertEquals('stopped', CoOrgSmarty::$vars['status']);
	}

	public function testAdvancedBeforeFilter()
	{
		CoOrg::process('alpha/advancedBefore/myName/myValue');

		$this->assertEquals('myValue', CoOrgSmarty::$vars['value']);
		$this->assertEquals('myName', CoOrgSmarty::$vars['name']);
		$this->assertEquals('someString', CoOrgSmarty::$vars['arbitraryValue']);
		$this->assertEquals('ran', CoOrgSmarty::$vars['status']);
	}

	public function testAdvancedBeforeFilterStops()
	{
		CoOrg::process('alpha/advancedBefore/myName/myStopCode');

		$this->assertEquals('myStopCode', CoOrgSmarty::$vars['value']);
		$this->assertEquals('myName', CoOrgSmarty::$vars['name']);
		$this->assertEquals('stopped', CoOrgSmarty::$vars['status']);
	}
	
	public function testPropagationOfBeforeFilter()
	{
		AlphaSubController::$set = array();
		CoOrg::process('alpha/sub/beforeFilterPropagation');
		
		$this->assertEquals(array('alphasub', 'propagate'), AlphaSubController::$set);
		$this->assertTrue(AlphaSubController::$executed);
		$this->assertEquals('value', CoOrgSmarty::$vars['value']);
		$this->assertEquals('name', CoOrgSmarty::$vars['name']);
		$this->assertEquals('string', CoOrgSmarty::$vars['string']);
	}
	
	public function testLoadPluginInfo()
	{
		$this->assertFalse(class_exists('AlphaInfo'));
		$this->assertFalse(class_exists('BetaInfo'));
		$this->assertFalse(class_exists('HomeInfo'));
	
		CoOrg::loadPluginInfo('info');
		
		$this->assertTrue(class_exists('AlphaInfo'));
		$this->assertFalse(class_exists('BetaInfo')); // Beta is not loaded
		$this->assertTrue(class_exists('HomeInfo'));
		
		CoOrg::loadPluginInfo('info'); // This should not fail.
		
		
		CoOrg::loadPluginInfo('medoesnotexists');
	}
	
	public function testCreateURL()
	{
		$this->assertEquals('a/b/c/d/parameterwith$3fand$2f',
		                    CoOrg::createURL(array('a/b/c/d',
		                                           'parameterwith?and/')));
	}
	
	public function testLoadPluginInfoSpecific()
	{
		$this->assertFalse(class_exists('Alpha2Info'));
		$this->assertFalse(class_exists('Home2Info'));
		
		CoOrg::loadPluginInfo('info2', 'home');
		
		$this->assertFalse(class_exists('Alpha2Info'));
		$this->assertTrue(class_exists('Home2Info'));
		
		CoOrg::loadPluginInfo('info2', 'sub'); // Sub does not have an info2
		CoOrg::loadPluginInfo('info2', 'home');
	}
	
	private function alternativeConfig($config)
	{
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
	}
}

?>
