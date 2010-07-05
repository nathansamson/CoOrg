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
		Session::$site = 'http://www.test.info/';
		Session::$referrer = 'http://www.test.info/some/part/of/the/site';
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
	
	public function testRenderThemedTemplate()
	{
		CoOrg::config()->set('theme', 'testtheme');
		CoOrg::process('');
		
		$this->assertContains('Home from Test Theme', CoOrgSmarty::$renderedOutput);
	}
	
	public function testRenderFallbackTemplate()
	{
		CoOrg::config()->set('theme', 'testtheme');
		CoOrg::process('home/fallback');
		
		$this->assertContains('FALLBACK TEMPLATE', CoOrgSmarty::$renderedOutput);
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
		Session::$referrer = 'http://someothershit.com';
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
		$this->assertEquals(CoOrg::PANEL_ORIENT_VERTICAL, HomeAlphaAside::$orient);
		$this->assertEquals('p2', HomeAlphaAside::$p2);
		$this->assertEquals(array(), HomeAlphaAside::$widgetParams);
		$this->assertFalse(array_key_exists('asideVar',  CoOrgSmarty::$vars));
		
		$this->assertFalse(class_exists('HomeAlpha2Aside')); // This is not configured
	}
	
	public function testAsideTheme()
	{
		CoOrg::config()->set('theme', 'testtheme');
		CoOrg::process('alpha/withaside/p1/p2');
		
		$this->assertContains('THIS IS TESTTHEME VERSION', CoOrgSmarty::$renderedOutput);
	}
	
	public function testAsideThemeFallback()
	{
		CoOrg::config()->set('theme', 'testtheme');
		CoOrg::process('alpha/withaside/p1/fallback');
		
		$this->assertContains('FALLBACK ASIDE', CoOrgSmarty::$renderedOutput);
	}
	
	public function testAsideWithParams()
	{
		CoOrg::config()->set('aside/main', array(
			array('widgetID' => 'home/alpha',
			      'beta' => 'gamma')));
		CoOrg::process('alpha/withaside/p1/p2');
		
		$this->assertEquals('alpha/withaside', HomeAlphaAside::$request);
		$this->assertEquals('p2', HomeAlphaAside::$p2);
		$this->assertEquals(array('beta' => 'gamma'), HomeAlphaAside::$widgetParams);
		$this->assertFalse(array_key_exists('asideVar',  CoOrgSmarty::$vars));
	}
	
	public function testAsideOverwriteParam()
	{
		CoOrg::process('alpha/withaside/triggerSomethingBad/p2');
		
		$this->assertEquals('some Value', CoOrgSmarty::$vars['myActionVar']); // As set by controller
		$this->assertEquals('extends:base.html.tpl|show.html.tpl', CoOrgSmarty::$renderedTemplate);

		$this->assertContains('From controller:some Value:', CoOrgSmarty::$renderedOutput);
		$this->assertContains('From aside:lets rock\'n roll', CoOrgSmarty::$renderedOutput);
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
	
	public function testStaticFile()
	{
		CoOrg::config()->set('path', '/');
		CoOrg::process('/');
		$this->assertEquals('/coorg/tests/mocks/plugins/alpha/static/default/somefile.css?v=2010-10-03',
		                    CoOrg::staticFile('somefile.css', 'alpha'));

		$this->assertEquals('/static/default/mockfile.css?v=A',
		                    CoOrg::staticFile('mockfile.css'));
	}
	
	public function testExternalStaticFile()
	{
		CoOrg::config()->set('path', '/');
		CoOrg::config()->set('staticpath', 'http://mystatic.somestatic.com/static/path/');
		CoOrg::config()->set('staticpath/alpha', true);
		CoOrg::config()->set('staticpath/home', false);
		CoOrg::process('/');
		
		$this->assertEquals('/coorg/tests/mocks/app/home/static/default/homefile.css?v=theversion',
		                    CoOrg::staticFile('homefile.css', 'home'));
		
		$this->assertEquals('http://mystatic.somestatic.com/static/path/alpha/default/somefile.css?v=2010-10-03',
		                    CoOrg::staticFile('somefile.css', 'alpha'));

		$this->assertEquals('http://mystatic.somestatic.com/static/path/_root/default/mockfile.css?v=A',
		                    CoOrg::staticFile('mockfile.css'));
	}
	
	public function testStaticFileRootDir()
	{
		CoOrg::config()->set('path', '/');
		CoOrg::process('/');
		$this->assertEquals('/coorg/tests/mocks/plugins/alpha/static/default/somefile.css?v=2010-10-03',
		                    CoOrg::staticFile('somefile.css', 'alpha'));

		$this->assertEquals('/static/default/mockfile.css?v=A',
		                    CoOrg::staticFile('mockfile.css'));
	}
	
	public function testI18n()
	{
		i18n::setLanguage('nl');
		$this->assertEquals('App Shared String', t('home|some shared string'));
		$this->assertEquals('Alpha Shared String', t('alpha|some shared string'));
		$this->assertEquals('Do Not Translate Me', t('Do Not Translate Me'));
	}
	
	public function testThemes()
	{
		CoOrg::config()->set('path', '/');
		CoOrg::config()->set('theme', 'testtheme');
		CoOrg::process('/');
		
		$this->assertEquals('/static/testtheme/mockfile.css?v=testtheme',
		                    CoOrg::staticFile('mockfile.css'));

		$this->assertEquals('/static/default/onlydefault.css?v=A',
		                    CoOrg::staticFile('onlydefault.css'));
		                    
		$this->assertEquals('/coorg/tests/mocks/plugins/alpha/static/testtheme/somefile.css?v=alphaV',
		                    CoOrg::staticFile('somefile.css', 'alpha'));
		
		$this->assertEquals('/coorg/tests/mocks/plugins/alpha/static/default/onlydefault.css?v=2010-10-03',
		                    CoOrg::staticFile('onlydefault.css', 'alpha'));
		                    
		$this->assertEquals(array(
			'/coorg/tests/mocks/plugins/alpha/static/testtheme/extends.css?v=extendsV',
			'/coorg/tests/mocks/plugins/alpha/static/default/extends.css?v=baseV',),
		                    CoOrg::staticFile('extends.css', 'alpha'));

		$this->assertEquals(array(
			'/static/testtheme/extends.css?v=extendsV',
			'/static/default/extends.css?v=baseV'),
		                    CoOrg::staticFile('extends.css'));
	}
	
	private function alternativeConfig($config)
	{
		CoOrg::init($config, 'coorg/tests/mocks/app', 'coorg/tests/mocks/plugins');
	}
}

?>
