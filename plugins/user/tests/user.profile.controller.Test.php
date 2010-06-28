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

class UserProfileControllerTest extends CoOrgControllerTest
{
	const dataset = 'user.dataset.xml';
	
	public function testShow()
	{
		$this->request('user/profile/show/azerty');
		
		$this->assertVarSet('profile');
		$p = CoOrgSmarty::$vars['profile'];
		$this->assertEquals('azerty', $p->username);
		$this->assertRendered('profile/show');
	}
	
	public function testShowNotFound()
	{
		$this->request('user/profile/show/i-do-not-exist');
		
		$this->assertRendered('notfound');
		$this->assertFlashError('Profile not found');
	}
	
	public function testEdit()
	{
		$this->login('azerty');
		$this->request('user/profile/edit');
		
		$p = CoOrgSmarty::$vars['profile'];
		$this->assertEquals('azerty', $p->username);
		$this->assertRendered('profile/edit');
	}
	
	public function testEditNotLoggedIn()
	{
		$this->request('user/profile/edit');
		
		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}
	
	public function testUpdate()
	{
		$this->login('azerty');
		$this->request('user/profile/update', array(
			'firstName' => 'Keyboard',
			'lastName' => 'layout',
			'birthDate' => '1900-04-28',
			'biography' => 'My Bio'));

		$this->assertRedirected('user/profile/show/azerty');
		$this->assertFlashNotice('Profile updated');
		$profile = UserProfile::get('azerty');
		$this->assertEquals('Keyboard', $profile->firstName);
		$this->assertEquals('layout', $profile->lastName);
		$this->assertEquals('1900-04-28', date('Y-m-d', $profile->birthDate));
		$this->assertEquals('My Bio', $profile->biography);
	}
	
	public function testUpdateNotLoggedIn()
	{
		$this->request('user/profile/update', array(
			'firstName' => 'Keyboard',
			'lastName' => 'layout',
			'birthDate' => '1900-04-28',
			'biography' => 'My Bio'));
		
		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}
	
?>
