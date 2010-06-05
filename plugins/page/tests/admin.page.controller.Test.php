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

class AdminPageControllerTest extends CoOrgControllerTest
{
	const dataset = 'page.dataset.xml';

	public function testIndex()
	{
		$this->login('admin');
		
		$this->request('admin/page');
		$this->assertRendered('admin/index');
		$this->assertVarSet('pages');
		$pages = CoOrgSmarty::$vars['pages'];
		$this->assertEquals(3, count($pages));
	}
	
	public function testIndexI18n()
	{
		$this->login('admin');
		
		$this->request('nl/admin/page');
		$this->assertRendered('admin/index');
		$this->assertVarSet('pages');
		$pages = CoOrgSmarty::$vars['pages'];
		$this->assertEquals(2, count($pages));
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}

	public function testCreate()
	{
		$this->login('admin');
		
		$this->request('admin/page/create');
		$this->assertVarSet('newPage');
		$this->assertRendered('admin/create');
	}
	
	public function testCreateNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page/create');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSave()
	{
		$this->login('admin');
		
		$this->request('admin/page/save', array(
		                 'title' => 'Some New Page',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Content'));
		$this->assertRedirected('page/show/some-new-page');
		$this->assertFlashNotice('New page created');
		
		$newPage = Page::get('some-new-page', 'en');
		$this->assertEquals('Some New Page', $newPage->title);
		$this->assertEquals('The Very Much Appreciated Content', $newPage->content);
		$this->assertEquals('admin', $newPage->author);
	}
	
	public function testSavePreview()
	{
		$this->login('admin');
		
		$this->request('admin/page/save', array(
		                 'title' => 'Some New Page',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Content',
		                 'preview' => 'View Preview'));
		$this->assertRendered('admin/create');
		$this->assertVarSet('newPage');
		$newPage = CoOrgSmarty::$vars['newPage'];
		$this->assertEquals('Some New Page', $newPage->title);
		$this->assertEquals('The Very Much Appreciated Content', $newPage->content);
		$this->assertEquals('admin', $newPage->author);
		
		$this->assertNull(Page::get('some-new-page', 'en'));
	}
	
	public function testSaveNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page/save', array(
		                 'title' => 'Some New Page',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Content'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSaveFailure()
	{
		$this->login('admin');
		
		$this->request('admin/page/save', array(
		                 'title' => 'Some New Page',
		                 'language' => 'en',
		                 'content' => ''));
		$this->assertRendered('admin/create');
		$this->assertVarSet('newPage');
		$newPage = CoOrgSmarty::$vars['newPage'];
		$this->assertEquals('Some New Page', $newPage->title);
	}
	
	public function testEdit()
	{
		$this->login('admin');

		$this->request('admin/page/edit/some-page');
		$this->assertVarSet('page');
		$this->assertRendered('admin/edit');
	}
	
	public function testEditRedirect()
	{
		$this->login('admin');
		
		$this->request('admin/page/edit/some-page/admin$2fpage');
		$this->assertVarSet('page');
		$this->assertVarSet('redirect');
		$this->assertVarIs('redirect', 'admin/page');
		$this->assertRendered('admin/edit');
	}
	
	public function testEditNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page/edit/some-page');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
		
	public function testEditNotFound()
	{
		$this->login('admin');

		$this->request('admin/page/edit/i-do-not-exist-page');
		$this->assertRendered('notfound');
		$this->assertFlashError('Page not found');
	}
	
	public function testUpdate()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some New Title',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Edited Content'));
		$this->assertRedirected('page/show/some-page');
		$this->assertFlashNotice('Page updated');
		
		$updatedPage = Page::get('some-page', 'en');
		$this->assertEquals('Some New Title', $updatedPage->title);
		$this->assertEquals('The Very Much Appreciated Edited Content', $updatedPage->content);
		$this->assertEquals('nathan', $updatedPage->author);
		$this->assertEquals('admin', $updatedPage->lastEditor);
	}
	
	public function testUpdateNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some New Title',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Edited Content'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testUpdateNotFound()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-non-existing-page',
		                 'title' => 'Some New Title',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Edited Content'));
		$this->assertRendered('notfound');
		$this->assertFlashError('Page not found');
	}
	
	public function testUpdateFailure()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some Updated Page',
		                 'language' => 'en',
		                 'content' => ''));
		$this->assertRendered('admin/edit');
		$this->assertVarSet('page');
		$newPage = CoOrgSmarty::$vars['page'];
		$this->assertEquals('Some Updated Page', $newPage->title);
	}
	
	public function testUpdateRedirect()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some New Title',
		                 'language' => 'en',
		                 'content' => 'The Very Much Appreciated Edited Content',
		                 'redirect' => 'admin/page'));
		$this->assertRedirected('admin/page');
		$this->assertFlashNotice('Page updated');
	}
	
	public function testUpdateFailureRedirect()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some Updated Page',
		                 'language' => 'en',
		                 'content' => '',
		                 'redirect' => 'admin/page'));
		$this->assertRendered('admin/edit');
		$this->assertVarSet('page');
		$this->assertVarSet('redirect');
		$this->assertVarIs('redirect', 'admin/page');
		$newPage = CoOrgSmarty::$vars['page'];
		$this->assertEquals('Some Updated Page', $newPage->title);
	}
	
	public function testUpdatePreview()
	{
		$this->login('admin');
		
		$this->request('admin/page/update', array(
		                 'ID' => 'some-page',
		                 'title' => 'Some Updated Page',
		                 'language' => 'en',
		                 'content' => '',
		                 'redirect' => 'admin/page',
		                 'preview' => 'Show Preview'));
		$this->assertRendered('admin/edit');
		$this->assertVarSet('page');
		$this->assertVarSet('redirect');
		$this->assertVarIs('redirect', 'admin/page');
		$this->assertVarSet('preview');
		$newPage = CoOrgSmarty::$vars['page'];
		$this->assertEquals('Some Updated Page', $newPage->title);
	}
	
	public function testDelete()
	{
		$this->login('admin');
		
		$this->request('admin/page/delete', array('ID' => 'some-page'));
		$this->assertRedirected('admin/page');
		$this->assertFlashNotice('Page is deleted');
		
		$this->assertNull(Page::get('some-page', 'en'));
	}
	
	public function testDeleteNotFound()
	{
		$this->login('admin');
		
		$this->request('admin/page/delete', array('ID' => 'does-not-exists'));
		$this->assertRendered('notfound');
		$this->assertFlashError('Page not found');
	}
	
	public function testDeleteNotAllowed()
	{
		$this->login('user');
		
		$this->request('admin/page/delete', array('ID' => 'some-page'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	private function login($username)
	{
		$s = new UserSession($username, $username);
		$s->save();
	}
}
