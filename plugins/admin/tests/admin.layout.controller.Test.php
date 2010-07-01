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

class LayoutAdminControllerTest extends CoOrgControllerTest
{
	const dataset = 'admin.dataset.xml';
	
	public function setUp()
	{
		parent::setUp();
		$config = CoOrg::config();
		$navigationLeft = array(0 => array('widgetID' => 'menu/menu', 'menu' => 'someMenu'));
		$config->set('aside/navigation-left', $navigationLeft);
		$navigationRight = array();
		$config->set('aside/navigation-right', $navigationRight);
		$mainLeft = array(0 => 'user/login', 1 => 'blog/archive');
		$config->set('aside/main', $mainLeft);
	}
	
	public function testIndex()
	{
		$this->login('dvorak');
		$this->request('admin/layout');
		$this->assertRendered('layout/index');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/layout');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testEdit()
	{
		$this->login('dvorak');
		$this->request('admin/layout/edit/navigation-left/0');
		$this->assertRendered('layout/index');
		$this->assertVarIs('editWidgetID', 0);
		$this->assertVarIs('editPanelID', 'navigation-left');
		$widget = CoOrgSmarty::$vars['editWidget'];
		$this->assertSame(array('widgetID' => 'menu/menu', 'menu' => 'someMenu'), $widget);
	}
	
	public function testEditNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/layout/edit/me-not-real/0');
		$this->assertRedirected('admin/layout');
		$this->assertFlashError('Panel not found');
	}
	
	public function testEditNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/layout/edit/main/1');
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testUpdate()
	{
		$this->login('dvorak');
		$this->request('admin/layout/update',
			array('panelID' => 'navigation-left',
			      'widgetID' => '0',
			      'menu' => 'someMenu',
			      'otherVarToTest' => 'otherValue'));

		$this->assertRedirected('admin/layout/edit/navigation-left/0');
		$config = CoOrg::config();
		$navLeft = $config->get('aside/navigation-left');
		$this->assertEquals(array('widgetID' => 'menu/menu',
		                        'menu' => 'someMenu',
		                        'otherVarToTest' => 'otherValue'),
						   $navLeft[0]);
	}
	
	public function testUpdateNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/layout/update',
			array('panelID' => 'navigation-left',
			      'widgetID' => '0',
			      'menu' => 'someMenu',
			      'otherVarToTest' => 'otherValue'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testUpdateNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/layout/update',
			array('panelID' => 'navigation-left',
			      'widgetID' => '7',
			      'menu' => 'someMenu',
			      'otherVarToTest' => 'otherValue'));

		$this->assertRedirected('admin/layout');
		$this->assertFlashError('Widget not found');
	}
	
	public function testMoveUpOne()
	{
		$this->login('dvorak');
		$this->request('admin/layout/move', array('panelID' => 'main', 
		                                          'widgetID' => '1',
		                                          'to' => '0'));
		
		$config = CoOrg::config();
		$this->assertEquals(array('blog/archive', 'user/login'),
						    $config->get('aside/main'));
	}
	
	public function testMoveDownOne()
	{
		$this->login('dvorak');
		$this->request('admin/layout/move', array('panelID' => 'main', 
		                                          'widgetID' => '0',
		                                          'to' => '1'));
		
		$config = CoOrg::config();
		$this->assertEquals(array('blog/archive', 'user/login'),
						    $config->get('aside/main'));
	}
	
	public function testMoveNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/layout/move',
			array('panelID' => 'navigation-left',
			      'widgetID' => '0',
			      'to' => '1'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSaveConfigurable()
	{
		$this->login('dvorak');
		$this->request('admin/layout/save',
			array('panelID' => 'navigation-right',
			      'widgetName' => 'menu/menu'));

		$config = CoOrg::config();
		$this->assertEquals(array(array('widgetID' => 'menu/menu')),
						    $config->get('aside/navigation-right'));
		$this->assertRedirected('admin/layout/edit/navigation-right/0');
	}
	
	public function testSaveNonConfigurable()
	{
		$this->login('dvorak');
		$this->request('admin/layout/save',
			array('panelID' => 'navigation-left',
			      'widgetName' => 'blog/archive'));

		$config = CoOrg::config();
		$this->assertEquals(array(array('widgetID' => 'menu/menu', 'menu' => 'someMenu'), 
		                          'blog/archive'),
						    $config->get('aside/navigation-left'));
		$this->assertRedirected('admin/layout');
	}
	
	public function testSaveNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/layout/save',
			array('panelID' => 'navigation-right',
			      'widgetName' => 'menu/menu'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	private function login($name)
	{
		$s = new UserSession($name, $name);
		$s->save();
	}
}
