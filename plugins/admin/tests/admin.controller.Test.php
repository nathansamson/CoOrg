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

class AdminControllerTest extends CoOrgControllerTest
{
	const dataset = 'admin.dataset.xml';
	
	public function testIndex()
	{
		$s = new UserSession('dvorak', 'dvorak');
		$s->save();
		
		$this->request('admin');
		
		$this->assertRendered('index');
		$this->assertVarSet('modules');
	}
	
	public function testIndexNoAdmin()
	{
		$s = new UserSession('azerty', 'azerty');
		$s->save();
		
		$this->request('admin');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testIndexNotLoggedIn()
	{
		$this->request('admin');
		$this->assertRendered('login');
		$this->assertFlashError('You should be logged in to view this page');
	}
}

?>
