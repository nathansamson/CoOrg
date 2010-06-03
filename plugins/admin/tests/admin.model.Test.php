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

class MockP2ModuleA
{
	public function __construct()
	{
		$this->name = 'AA BB CC';
		$this->image = '';
		$this->priority = 2;
	}
	
	public function isAllowed() { return true; }
}

class MockP2Module
{
	public function __construct()
	{
		$this->name = 'BB BB CC';
		$this->image = '';
		$this->priority = 2;
	}
	
	public function isAllowed() { return true; }
}

class MockP1Module
{
	public function __construct()
	{
		$this->name = 'AA BB CC P1';
		$this->image = '';
		$this->priority = 1;
	}
	
	public function isAllowed() { return true; }
}

class MockP1DeniedModule
{
	public function __construct()
	{
		$this->name = 'AA BB CC Denied';
		$this->image = '';
		$this->priority = 2;
	}
	
	public function isAllowed() { return false; }
}

Admin::registerModule('MockP2ModuleA');
Admin::registerModule('MockP2Module');
Admin::registerModule('MockP1Module');
Admin::registerModule('MockP1DeniedModule');

class AdminTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/admin.dataset.xml';
	}
	
	public function testModels()
	{
		$s = new UserSession('dvorak', 'dvorak');
		$s->save();
		$modules = Admin::modules();
		
		$this->assertEquals(4, count($modules));
		$this->assertEquals('AA BB CC P1', $modules[0]->name);
		$this->assertEquals('AA BB CC', $modules[1]->name);
		$this->assertEquals('BB BB CC', $modules[2]->name);
		$this->assertEquals('Visit Site', $modules[3]->name);
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
