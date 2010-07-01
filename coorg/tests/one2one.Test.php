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

/**
 * @property primary; ID String(' ', 20); required
 * @property barID String(' ', 10);
 * @property foofoo String(' ', 20); required
*/
class Foo extends DBModel
{
	public function get($name)
	{
		$q = DB::prepare('SELECT * FROM Foo WHERE ID=:ID');
		$q->execute(array(':ID' => $name));
		
		return self::fetch($q->fetch(), 'Foo');
	}
}

/**
 * @property primary; ID String(' ', 20); required
 * @property fooID String(' ', 20);
 * @property barbar String(' ', 20); required
*/
class Bar extends DBModel
{
	public function get($name)
	{
		$q = DB::prepare('SELECT * FROM Bar WHERE ID=:ID');
		$q->execute(array(':ID' => $name));
		
		return self::fetch($q->fetch(), 'Bar');
	}
}

class FooHasBar extends One2One
{
	public function info()
	{
		return array(
			'from' => 'Foo',
			'to' => 'Bar',
			
			'fromLocal' => 'barID',
			'fromForeign' => 'ID',
			'localAs' => 'bar',
			
			'toForeign' => 'ID',
			'toLocal' => 'fooID',
			'foreignAs' => 'foo'
		);
	}
}

Model::registerRelation(new FooHasBar);

class One2OneTest extends CoOrgModelTest
{
	const dataset = 'one2one.dataset.xml';
	
	public function testFoo()
	{
		$foo = Foo::get('someFoo');
		$this->assertNull($foo->bar);
		
		$foo = Foo::get('fooWithBar');
		$this->assertNotNull($foo->bar);
		$this->assertEquals('Some Bar', $foo->bar->barbar);
		
		$foo = Foo::get('someFoo');
		$bar = Bar::get('someBar');
		$foo->bar = $bar;
		$this->assertEquals('someBar', $foo->barID);
		//$this->assertEquals('someFoo', $bar->fooID);
	}
	
	public function testBar()
	{
		$bar = Bar::get('someBar');
		$this->assertNull($bar->foo);
		
		$bar = Bar::get('barWithFoo');
		$this->assertNotNull($bar->foo);
		$this->assertEquals('Some Foo', $bar->foo->foofoo);
		
		$foo = Foo::get('someFoo');
		$bar = Bar::get('someBar');
		$bar->foo = $foo;
		//$this->assertEquals('someBar', $foo->barID);
		$this->assertEquals('someFoo', $bar->fooID);
	}
}
?>
