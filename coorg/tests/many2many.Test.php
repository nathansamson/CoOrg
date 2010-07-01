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

class FoosHasBars extends Many2Many
{
	public function info()
	{
		return array(
			'from' => 'Foo',
			'to' => 'Bar',
			
			'fromLocal' => 'ID',
			'toAs' => 'bars',
			
			'toLocal' => 'ID',
			'fromAs' => 'foos',
			
			'table' => 'FooBars',
			'tableFrom' => 'fooID',
			'tableTo' => 'barID'
		);
	}
}

Model::registerRelation(new FoosHasBars);

class Many2ManyTest extends CoOrgModelTest
{
	const dataset = 'many2many.dataset.xml';
	
	public function testRetrieve()
	{
		$foo = Foo::get('foo3');
		$this->assertEquals(2, count($foo->bars));
		$this->assertEquals('Bar 1', $foo->bars[0]->barbar);
		$this->assertEquals('Bar 2', $foo->bars[1]->barbar);
		
		$bar = Bar::get('bar 1');
		$this->assertNotNull($bar);
		$this->assertEquals(2, count($bar->foos));
		$this->assertEquals('Foo 3', $bar->foos[0]->foofoo);
		$this->assertEquals('Foo 2', $bar->foos[1]->foofoo);
	}
	
	public function testAppend()
	{
		$foo = Foo::get('foo3');
		$bar = Bar::get('bar 3');
		$this->assertEquals(2, count($foo->bars));
		$foo->bars[] = $bar;
		
		$foo = Foo::get('foo3');
		$bar = Bar::get('bar 3');
		$found = false;
		$this->assertEquals(3, count($foo->bars));
		foreach ($foo->bars as $bar)
		{
			if ($bar->ID == 'bar 3') $found = true;
		}
		$this->assertTrue($found);
		
		$this->assertEquals(1, count($bar->foos));
		$this->assertEquals('foo3', $bar->foos[0]->ID);
	}
	
	public function testUnset()
	{
		$foo = Foo::get('foo3');
		$this->assertEquals(2, count($foo->bars));
		$this->assertEquals('Bar 1', $foo->bars[0]->barbar);
		$this->assertEquals('Bar 2', $foo->bars[1]->barbar);
		
		unset($foo->bars[0]);
		
		$foo = Foo::get('foo3');
		$bar = Bar::get('bar 1');
		$this->assertEquals(1, count($foo->bars));
		$this->assertEquals('Bar 2', $foo->bars[0]->barbar);
		
		$this->assertEquals(1, count($bar->foos));
		$this->assertEquals('Foo 2', $bar->foos[0]->foofoo);
	}
}

?>
