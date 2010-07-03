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

class UserPasswordControllerTest extends CoOrgControllerTest
{
	const dataset = 'user.dataset.xml';

	public function setUp()
	{
		parent::setUp();
		Session::set('mollom/sessionid', 'new-sessionid');
		Session::set('mollom/type', 'image');
	}

	public function testReset()
	{
		$this->request('user/password/reset');
		
		$this->assertVarSet('resetPassword');
		$this->assertVarSet('resetCaptcha');
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmResetCorrectUsername()
	{
		$this->request('user/password/confirmreset', array(
		                  'username' => 'azerty',
		                  'response' => 'valid'
		                ));
		
		$this->assertMailSent('azerty@azerty.com', 'The Site: Your account information',
		                      'mails/passwordreset',
		                      array('username' => 'azerty',
		                            'renewURL' => '**?**',
		                            'site' => 'The Site'));
		$this->assertFlashNotice('A mail has been sent to you. Please follow the directions to set a new password for your account.');
		$this->assertRedirected('');
	}
	
	public function testConfirmResetCorrectEmail()
	{
		$this->request('user/password/confirmreset', array(
		                  'email' => 'azerty@azerty.com',
		                  'response' => 'valid'
		                ));
		
		$this->assertMailSent('azerty@azerty.com', 'The Site: Your account information',
		                      'mails/passwordreset',
		                      array('username' => 'azerty',
		                            'renewURL' => '**?**',
		                            'site' => 'The Site'));
		$this->assertFlashNotice('A mail has been sent to you. Please follow the directions to set a new password for your account.');
		$this->assertRedirected('');
	}
	
	public function testConfirmResetInCorrectUsername()
	{
		$this->request('user/password/confirmreset', array(
		                  'username' => 'azertysd',
		                  'response' => 'valid'
		                ));

		$this->assertFlashError('Username not found');
		$this->assertVarSet('resetCaptcha');
		$this->assertVarSet('resetPassword');
		$c = CoOrgSmarty::$vars['resetPassword'];
		$this->assertEquals('azertysd', $c->username);
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmResetInCorrectEmail()
	{
		$this->request('user/password/confirmreset', array(
		                  'email' => 'azertysd',
		                  'response' => 'valid'
		                ));

		$this->assertFlashError('Email not found');
		$this->assertVarSet('resetCaptcha');
		$this->assertVarSet('resetPassword');
		$c = CoOrgSmarty::$vars['resetPassword'];
		$this->assertEquals('azertysd', $c->email);
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmResetNoUsernameAndPassword()
	{
		$this->request('user/password/confirmreset', array(
		                  'username' => '',
		                  'email' => '',
		                  'response' => 'valid'
		                ));

		$this->assertFlashError('You have to give your username or email');
		$this->assertVarSet('resetCaptcha');
		$this->assertVarSet('resetPassword');
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmResetIncorrectCaptcha()
	{
		$this->request('user/password/confirmreset', array(
		                  'username' => 'azerty',
		                  'response' => 'invalid'
		                ));

		$this->assertFlashError('Resetting your password failed');
		$this->assertVarSet('resetCaptcha');
		$this->assertVarSet('resetPassword');
		$c = CoOrgSmarty::$vars['resetPassword'];
		$this->assertEquals('azerty', $c->username);
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmRefresh()
	{
		$this->request('user/password/confirmreset', array(
		                  'email' => 'azerty',
		                  'refresh'  => 'true'
		                ));
		
		$this->assertVarSet('resetCaptcha');
		$c = CoOrgSmarty::$vars['resetCaptcha'];
		$this->assertEquals('image', $c->type);
		$this->assertVarSet('resetPassword');
		$c = CoOrgSmarty::$vars['resetPassword'];
		$this->assertEquals('azerty', $c->email);
		$this->assertRendered('resetpassword');
	}
	
	public function testConfirmRefreshCaptchaAudio()
	{
		$this->request('user/password/confirmreset', array(
		                  'email' => 'azerty',
		                  'audio'  => 'true'
		                ));
		
		$this->assertVarSet('resetCaptcha');
		$c = CoOrgSmarty::$vars['resetCaptcha'];
		$this->assertEquals('audio', $c->type);
		$this->assertVarSet('resetPassword');
		$c = CoOrgSmarty::$vars['resetPassword'];
		$this->assertEquals('azerty', $c->email);
		$this->assertRendered('resetpassword');
	}
	
	public function testRenewPassword()
	{
		$dvorak = User::getUserByName('dvorak');
		$key = $dvorak->resetPassword();
		$dvorak->save();
		
		$this->request('user/password/renew/dvorak/'.coorgencode($key));
		
		$this->assertMailSent('dvorak@dvorak.com', 'The Site: Your new password',
		                      'mails/passwordrenew',
		                      array('username' => 'dvorak',
		                            'newpassword' => '**?**',
		                            'loginURL' => 'http://www.test.info/user/login',
		                            'site' => 'The Site'));
		$this->assertFlashNotice('A mail has been sent to you, containing your new password');
		$this->assertRedirected('');
		
	}
	
	public function testRenewPasswordIncorrectKey()
	{
		$dvorak = User::getUserByName('dvorak');
		$key = $dvorak->resetPassword();
		$dvorak->save();
		
		$this->request('user/password/renew/dvorak/nokey');
		
		$this->assertFlashError('Invalid key');
		$this->assertRedirected('');
	}
}

?>
