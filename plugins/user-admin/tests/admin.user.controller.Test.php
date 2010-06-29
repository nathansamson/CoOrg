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

class AdminUserControllerTest extends CoOrgControllerTest
{
	const dataset = 'user.admin.dataset.xml';
	
	public function testIndex()
	{
		$this->login('dvorak');
		
		$this->request('admin/user');
		$this->assertVarSet('users');
		$users = CoOrgSmarty::$vars['users'];
		$this->assertVarSet('userPager');
		$this->assertEquals(3, count($users));
		$this->assertRendered('index');
	}
	
	public function testIndexPage()
	{
		for ($i = 0; $i < 110; $i++)
		{
			$u = new User;
			$u->username = 'u'.$i;
			$u->email = 'u'.$i.'@gmail.com';
			$u->password = 'u'.$i;
			$u->passwordConfirmation = 'u'.$i;
			$u->save();
		}
		
		$this->login('dvorak');
		$this->request('admin/user/index/6');
		$this->assertVarSet('users');
		$users = CoOrgSmarty::$vars['users'];
		$this->assertEquals(13, count($users));
		$this->assertRendered('index');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('azerty');
		
		$this->request('admin/user');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testEditUser()
	{
		$this->login('dvorak');
		
		$this->request('admin/user/edit/azerty/admin$2fuser$2findex$2f7');
		$this->assertVarSet('user');
		$u = CoOrgSmarty::$vars['user'];
		$this->assertVarIs('from', 'admin/user/index/7');
		$this->assertEquals('azerty', $u->username);
		$this->assertRendered('edit');
	}
	
	public function testEditUserNotFound()
	{
		$this->login('dvorak');
	
		$this->request('admin/user/edit/dsdsdsd');
		$this->assertRendered('notfound');
		$this->assertFlashError('User not found');
	}
	
	public function testUpdateUser()
	{
		$this->login('dvorak');
		$this->request('admin/user/save', array(
				'username' => 'azerty',
				'email' => 'azerty@mail.com',
				'from' => 'admin/user/index/7'
			));
	
		$user = User::getUserByName('azerty');
		$this->assertEquals('azerty@mail.com', $user->email);
		$this->assertRedirected('admin/user/index/7');
		$this->assertFlashNotice('Updated user');
	}
	
	public function testUpdateUserFailure()
	{
		$this->login('dvorak');
		$this->request('admin/user/save', array(
				'username' => 'azerty',
				'email' => 'azertymail',
				'from' => 'admin/user/index/7'
			));
	
		$this->assertVarSet('user');
		$u = CoOrgSmarty::$vars['user'];
		$this->assertVarIs('from', 'admin/user/index/7');
		$this->assertEquals('azerty', $u->username);
		$this->assertEquals('azertymail', $u->email);
		$this->assertFlashError('Failed updating user');
		$this->assertRendered('edit');
	}
	
	public function testChangePassword()
	{
		$this->login('dvorak');
		$this->request('admin/user/save', array(
				'username' => 'azerty',
				'email' => 'azerty@mail.com',
				'password' => 'newpassword',
				'passwordConfirmation' => 'newpassword',
				'from' => 'admin/user/index/7'
			));
		$this->assertRedirected('admin/user/index/7');
		$this->assertFlashNotice('Updated user');
		
		$user = User::getUserByName('azerty');
		$this->assertTrue($user->checkPassword('newpassword'));
	}
	
	public function testChangePasswordFailure()
	{
		$this->login('dvorak');
		$this->request('admin/user/save', array(
				'username' => 'azerty',
				'email' => 'azerty@mail.com',
				'password' => 'newpassword',
				'passwordConfirmation' => 'sdsd',
				'from' => 'admin/user/index/7'
			));
		$this->assertFlashError('Failed updating user');
		$this->assertRendered('edit');
		$this->assertVarSet('user');
		$u = CoOrgSmarty::$vars['user'];
		$this->assertVarIs('from', 'admin/user/index/7');
		$this->assertEquals('azerty', $u->username);
		
		$user = User::getUserByName('azerty');
		$this->assertTrue($user->checkPassword('azerty'));
	}
	
	public function testUnlockUser()
	{
		$this->login('dvorak');
		
		$user = User::getUserByName('locked');
		$this->assertTrue($user->isLocked());
		
		$this->request('admin/user/unlock', array(
				'username' => 'locked',
				'from' => 'admin/edit/locked/admin$2fuser$2findex$2f7'
			));
		
		$this->assertRedirected('admin/edit/locked/admin$2fuser$2findex$2f7');
		$this->assertFlashNotice('Unlocked user');
		$user = User::getUserByName('locked');
		$this->assertFalse($user->isLocked());
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
