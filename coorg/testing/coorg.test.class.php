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
	public function render($t, $app = false, $base = 'base')
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
		Session::$site = 'http://www.test.info/';
		Session::$referrer = 'http://www.test.info/some/part/of/the/site';
		unlink(COORG_TEST_CONFIG);
		copy(COORG_TEST_CONFIG_CLEAN, COORG_TEST_CONFIG);
		$config = new Config(COORG_TEST_CONFIG);
		$config->set('site/title', 'The Site');
		$config->set('defaultLanguage', '');
		CoOrg::init($config, 'app', 'plugins');
		CoOrgSmarty::clearAll();
		Header::$redirect = '__none__';
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
	
	protected function assertRendered($tpl, $type = null,  $baseFile = 'base')
	{
		$otpl = $tpl;
		$tpl = str_replace('/', '\/', $tpl);
		if ($type != null)
		{
			if ($baseFile)
			{
				$match = preg_match('/^extends:'.$baseFile.'.'.$type.'.tpl\|'.$tpl.'.'.$type.'.tpl$/', CoOrgSmarty::$renderedTemplate) == 1;
			}
			else
			{
				$match = preg_match('/'.$tpl.'.'.$type.'.tpl$/', CoOrgSmarty::$renderedTemplate) == 1;
			}
		}
		else
		{
			$type = 'html';
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
	
	protected function assertMailSent($to, $subject, $tpl, $vars)
	{
		$mail = @Mail::$sentMails[$to][$subject];
		if ($mail)
		{
			$this->assertEquals($tpl, $mail->tpl);
			foreach ($vars as $var => $value)
			{
				$this->assertTrue(array_key_exists($var, $mail->vars), $var. ' is set in mail');
				if ($value != '**?**')
				{
					$this->assertEquals($value, $mail->vars[$var]);
				}
			}
		}
		else
		{
			$this->fail('No mail sent to: '.$to.' with subject '.$subject);
		}
	}
}

?>
