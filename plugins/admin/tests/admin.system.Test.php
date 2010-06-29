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

class AdminSystemControllerTest extends CoOrgControllerTest
{
	const dataset = 'admin.dataset.xml';

	public function testIndex()
	{
		$this->login('dvorak');
		$this->request('admin/system');
		
		$this->assertVarSet('config');
		$this->assertRendered('config');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/system');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testUpdate()
	{
		$this->login('dvorak');
		
		$c = new SiteConfig;
		$this->request('admin/system/update', array(
				'title' => 'My New Title',
				'subtitle' => '',
				'siteAuthor' => 'The New Author',
				'siteContactEmail' => 'xyz@xyz.org',
				'friendlyURL' => 'on',
				'UUID' => 'abbaabbaabbabababa',
				'databaseConnection' => $c->databaseConnection,
				'databaseUser' => $c->databaseUser,
				'databasePassword' => $c->databasePassword
			));
		
		$this->assertRedirected('admin/system');
		$this->assertFlashNotice('Saved site configuration');
		$c = new SiteConfig;
		$this->assertEquals('My New Title', $c->title);
		$this->assertEquals('', $c->subtitle);
		$this->assertEquals('The New Author', $c->siteAuthor);
		$this->assertEquals('xyz@xyz.org', $c->siteContactEmail);
		$this->assertTrue($c->friendlyURL);
		$this->assertEquals('abbaabbaabbabababa', $c->UUID);
	}
	
	public function testUpdateFailure()
	{
		$this->login('dvorak');
		
		$c = new SiteConfig;
		$this->request('admin/system/update', array(
				'title' => 'My New Title',
				'subtitle' => '',
				'siteAuthor' => 'The New Author',
				'siteContactEmail' => 'xyz.org',
				'friendlyURL' => 'on',
				'UUID' => 'abbaabbaabbabababa',
				'databaseConnection' => $c->databaseConnection,
				'databaseUser' => $c->databaseUser,
				'databasePassword' => $c->databasePassword
			));
		
		$this->assertFlashError('Failed saving configuration');
		$this->assertRendered('config');
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
