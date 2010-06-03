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

class MockController extends Controller
{
	public function render($t, $app = false)
	{
		if ($t == 'fake')
		{
			CoOrgSmarty::fakeRender('extends:base.html.tpl|'.$t.'.html.tpl');
		}
		else
		{
			parent::render($t, $app);
		}
	}
}

class CoOrgControllerTest extends CoOrgModelTest
{
	public function setUp()
	{
		parent::setUp();
		I18n::setLanguage('');
		Session::destroy();
		if ($s = UserSession::get())
		{
			$s->delete();
		}
		CoOrg::setSite('http://www.test.info/');
		CoOrg::spoofReferer('http://www.test.info/some/part/of/the/site');
		$config = new Config(COORG_TEST_CONFIG);
		$config->set('enabled_plugins', array('admin', 'user'));
		CoOrg::init($config, 'app', 'plugins');
		CoOrgSmarty::clearAll();
	}

	protected function request($request, $postParams = array())
	{
		CoOrg::process($request, $postParams, $postParams != array());
	}

	protected function assertVarSet($key)
	{
		$this->assertTrue(array_key_exists($key, CoOrgSmarty::$vars), "'$key' template var is set");
	}
	
	protected function assertVarIs($key, $value)
	{
		$this->assertEquals($value, CoOrgSmarty::$vars[$key]);
	}
	
	protected function assertContentType($ct)
	{
		$this->assertEquals($ct, Header::$contentType);
	}
	
	protected function assertRendered($tpl, $type = 'html',  $baseFile = 'base')
	{
		$otpl = $tpl;
		$tpl = str_replace('/', '\/', $tpl);
		if ($baseFile)
		{
			$match = preg_match('/^extends:'.$baseFile.'.'.$type.'.tpl\|(.*)'.$tpl.'.'.$type.'.tpl$/', CoOrgSmarty::$renderedTemplate) == 1;
		}
		else
		{
			$match = preg_match('/'.$tpl.'.'.$type.'.tpl$/', CoOrgSmarty::$renderedTemplate) == 1;
		}
		$this->assertTrue($match, "'$otpl' rendered");
	}
	
	protected function assertRedirected($to)
	{
		$this->assertEquals($to, Header::$redirect);
	}
	
	protected function assertFlashNotice($notice)
	{
		$this->assertTrue(in_array($notice, CoOrgSmarty::$notices), "'$notice' is a notice message");
	}
	
	protected function assertFlashError($error)
	{
		$this->assertTrue(in_array($error, CoOrgSmarty::$errors), "'$error' is an error message");
	}
}

?>
