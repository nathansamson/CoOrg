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

class AdminMollomControlerTest extends CoOrgControllerTest
{
	const dataset = 'mollom.dataset.xml';
	
	public function setUp()
	{
		parent::setUp();
	}
	
	public function testIndex()
	{
		$this->login('uberadmin');
		
		$this->request('admin/mollom');
		$this->assertVarSet('mollomConfig');
		$this->assertRendered('admin/mollom');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('nathan');
		$this->request('admin/mollom');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSave()
	{
		$this->login('uberadmin');
		
		CoOrg::config()->set('mollom/public', 'n');
		CoOrg::config()->set('mollom/private', 'p');
		
		$this->request('admin/mollom/save', array('publicKey' => 'valid-pub-key',
		                                       'privateKey' => 'valid-priv-key'));
		
		$config = MollomConfig::get();
		$this->assertEquals('valid-pub-key', $config->publicKey);
		$this->assertEquals('valid-priv-key', $config->privateKey);
		$this->assertFlashNotice('Mollom configuration saved');
		$this->assertRedirected('admin/mollom');
	}
	
	public function testSaveFailure()
	{
		$this->login('uberadmin');
		
		$this->request('admin/mollom/save', array('publicKey' => 'pub-key',
		                                       'privateKey' => 'priv-key'));

		$this->assertFlashError('Mollom configuration not saved');
		$this->assertVarSet('mollomConfig');
		$this->assertRendered('admin/mollom');
	}
	
	public function testSaveOutdatedServerList()
	{
		$this->login('uberadmin');
	
		CoOrg::config()->set('mollom/serverlist', array('outdated'));
		
		$this->request('admin/mollom/save', array('publicKey' => 'valid-pub-key',
		                                       'privateKey' => 'valid-priv-key'));
	
		$config = MollomConfig::get();
		$this->assertEquals('valid-pub-key', $config->publicKey);
		$this->assertEquals('valid-priv-key', $config->privateKey);
		$this->assertFlashNotice('Mollom configuration saved');
		$this->assertRedirected('admin/mollom');	
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
