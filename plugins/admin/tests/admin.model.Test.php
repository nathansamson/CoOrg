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

class MockP2ModuleA extends AdminModule
{
	public function __construct()
	{
		$this->name = 'AA BB CC';
		$this->image = '';
		$this->priority = 2;
	}
}

class TabP1ModuleA
{
	public function __construct()
	{
		$this->name = 'Tab 1';
		$this->url = 'secondtab';
		$this->priority = 1;
	}
	
	public function isAllowed() { return true; }
}

class TabP2ModuleA
{
	public function __construct()
	{
		$this->name = 'Tab 2';
		$this->url = 'thirdtab';
		$this->priority = 2;
	}
	
	public function isAllowed() { return true; }
}

class ATabP1ModuleA
{
	public function __construct()
	{
		$this->name = 'A Tab 1';
		$this->url = 'firsttab';
		$this->priority = 1;
	}
	
	public function isAllowed($user)
	{
		return $user->username == 'dvorak';
	}
}

Admin::registerTab('TabP1ModuleA', 'MockP2ModuleA');
Admin::registerModule('MockP2ModuleA');
Admin::registerTab('TabP2ModuleA', 'MockP2ModuleA');
Admin::registerTab('ATabP1ModuleA', 'MockP2ModuleA');

class MockP2Module extends AdminModule
{
	public function __construct()
	{
		$this->name = 'BB BB CC';
		$this->image = '';
		$this->priority = 2;
	}
}

class TabP2Module
{
	public function __construct()
	{
		$this->name = 'Another Tab';
		$this->url = '';
		$this->priority = 1;
	}
	
	public function isAllowed() { return true; }
}

Admin::registerModule('MockP2Module');
Admin::registerTab('TabP2Module', 'MockP2Module');

class MockP1Module extends AdminModule
{
	public function __construct()
	{
		$this->name = 'AA BB CC P1';
		$this->image = '';
		$this->priority = 1;
	}
}

class TabP1Module
{
	public function __construct()
	{
		$this->name = 'Some Tab';
		$this->url = '';
		$this->priority = 1;
	}

	public function isAllowed() { return true; }
}

Admin::registerModule('MockP1Module');
Admin::registerTab('TabP1Module', 'MockP1Module');

class MockP1DeniedModule extends AdminModule
{
	public function __construct()
	{
		$this->name = 'AA BB CC Denied';
		$this->image = '';
		$this->priority = 2;
	}
}

class TabMockP1DeniedAdminTab
{
	public function __construct()
	{
		$this->name = 'Denied';
		$this->url = '';
		$this->priority = 1;
	}

	public function isAllowed() { return false; }
}

Admin::registerModule('MockP1DeniedModule');
Admin::registerTab('TabMockP1DeniedAdminTab', 'MockP1DeniedModule');

class AdminTest extends CoOrgModelTest
{
	const dataset = 'admin.dataset.xml';
	
	public function testModels()
	{
		$s = new UserSession('dvorak', 'dvorak');
		$s->save();
		$modules = Admin::modules();
		
		$this->assertEquals(7, count($modules));
		$this->assertEquals('AA BB CC P1', $modules[0]->name);
		$this->assertEquals('Site Configuration', $modules[1]->name);
		$this->assertEquals('AA BB CC', $modules[2]->name);
		$this->assertEquals('firsttab', $modules[2]->url($s->user()));
		$this->assertEquals('BB BB CC', $modules[3]->name);
		$this->assertEquals('Languages', $modules[4]->name);
		$this->assertEquals('Layout', $modules[5]->name);
		$this->assertEquals('Visit Site', $modules[6]->name);
	}
	
	public function testTabs()
	{
		$s = new UserSession('dvorak', 'dvorak');
		$s->save();
		
		$tabs = Admin::tabs('MockP2ModuleA', 'TabP2ModuleA');
		$this->assertEquals('firsttab', $tabs[0]->url);
		$this->assertEquals('secondtab', $tabs[1]->url);
		$this->assertEquals('thirdtab', $tabs[2]->url);
	}
	
	public function testModelsNoAdmin()
	{
		$s = new UserSession('azerty', 'azerty');
		$s->save();
		$modules = Admin::modules();
		
		$this->assertNull($modules);
		
		$s->delete();
		$modules = Admin::modules();
		$this->assertNull($modules);
	}
}
?>
