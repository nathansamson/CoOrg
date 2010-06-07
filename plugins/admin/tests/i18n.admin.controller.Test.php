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

class i18nAdminControllerTest extends CoOrgControllerTest
{
	const dataset = 'admin.dataset.xml';
	
	public function testIndex()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n');
		$this->assertRendered('i18n/index');
		$this->assertVarSet('languages');
		$this->assertVarSet('newLanguage');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('azerty');
		
		$this->request('admin/i18n');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSave()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/save', array('language'=>'de',
		                                        'name'=>'German'));
		$this->assertFlashNotice('Installed "German"');
		$this->assertRedirected('admin/i18n');
		$this->assertNotNull(Language::get('de'));
	}
	
	public function testSaveFailure()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/save', array('language'=>'en',
		                                        'name'=>'Engels'));
		$this->assertFlashError('Did not install "Engels"');
		$this->assertRendered('i18n/index');
		$this->assertVarSet('newLanguage');
		$this->assertVarSet('languages');
	}
	
	public function testSaveNotAllowed()
	{
		$this->login('azerty');
		
		$this->request('admin/i18n/save', array('language'=>'de',
		                                        'name'=>'German'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testUpdate()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/update', array('language'=>'en',
		                                        'name'=>'ENGELS'));
		$this->assertFlashNotice('Updated "ENGELS"');
		$this->assertEquals('ENGELS', Language::get('en')->name);
		$this->assertRedirected('admin/i18n');
	}
	
	public function testUpdateNotAllowed()
	{
		$this->login('azerty');
		
		$this->request('admin/i18n/update', array('language'=>'en',
		                                        'name'=>'ENGELS'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testUpdateFailure()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/update', array('language'=>'en',
		                                          'name'=>''));
		$this->assertFlashError('Did not update "en"');
	}
	
	public function testUpdateNotFound()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/update', array('language'=>'br',
		                                          'name'=>'Brazil'));
		$this->assertFlashError('Language "br" not found');
		$this->assertRedirected('admin/i18n');
	}
	
	public function testDelete()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/delete', array('language'=>'en'));
		$this->assertFlashNotice('Deleted "English"');
		$this->assertNull(Language::get('en'));
		$this->assertRedirected('admin/i18n');
	}
	
	public function testDeleteNotFound()
	{
		$this->login('dvorak');
		
		$this->request('admin/i18n/delete', array('language'=>'br'));
		$this->assertFlashError('Language "br" not found');
		$this->assertRedirected('admin/i18n');
	}
	
	public function testDeleteNotAllowed()
	{
		$this->login('azerty');
		
		$this->request('admin/i18n/delete', array('language'=>'en'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
	
}

?>
