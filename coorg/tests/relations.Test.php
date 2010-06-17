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

class ContainmentHasContainer extends One2Many
{
	protected function info()
	{
		return array(
			'from' => 'SomeContainment',
			'to' => 'SomeContainer',
			'localAs' => 'container',
			'local' => 'containerID',
			'foreign' => 'ID',
			'foreignAs' => 'containments'
		);
	}
}
Model::registerRelation(new ContainmentHasContainer);

/**
 * @property primary; ID String('ID', 32); required
 * @property content String('Text'); required
*/
class SomeContainer extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get($id)
	{
		$q = DB::prepare('SELECT * FROM SomeContainer WHERE ID=:id');
		$q->execute(array(':id'=>$id));
		
		return self::fetch($q->fetch(), 'SomeContainer');
	}
}

/**
 * @property primary; name String('Name', 64); required
 * @property containerID String('Container', 32); required
*/
class SomeContainment extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get($id)
	{
		$q = DB::prepare('SELECT * FROM SomeContainment WHERE name=:id');
		$q->execute(array(':id'=>$id));
		
		return self::fetch($q->fetch(), 'SomeContainment');
	}
}

class One2ManyTest extends CoOrgModelTest
{
	const dataset = 'one2many.dataset.xml';
	
	public function testContainmentLink()
	{
		$containment = SomeContainment::get('Containment of One');
		$this->assertNotNull($containment->container);
		$this->assertEquals('One Text', $containment->container->content);
		
		$containment->container = SomeContainer::get('two');
		$this->assertEquals('two', $containment->containerID);
	}
	
	public function testCollections()
	{
		$container = SomeContainer::get('one');
		$this->assertEquals(3, count($container->containments));
		
		$expected = array('Containment of One',
		                  'Another Containment of One',
		                  'Last Containment of One');

		foreach ($container->containments as $key => $value)
		{
			$this->assertEquals($expected[$key], $value->name);
		}
		
		$this->assertEquals('Another Containment of One',
		                    $container->containments[1]->name);
	}
	
	public function testAppendCollection()
	{
		$containment = new SomeContainment;
		$containment->name = 'Me Name';
		
		$container = SomeContainer::get('one');
		$container->containments[] = $containment;
		
		$this->assertEquals('one', $containment->containerID);
		$this->assertEquals('one', $containment->container->ID);
		$retrieve = SomeContainment::get('Me Name');
		$this->assertNotNull($retrieve);
		$this->assertEquals('one', $retrieve->containerID);
		$this->assertEquals('one', $retrieve->container->ID);
		$this->assertEquals(4, count($container->containments));
	}
	
	public function testAppendCollectionFailure()
	{
		$containment = new SomeContainment;
		
		$container = SomeContainer::get('one');
		try
		{
			$container->containments[] = $containment;
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Name is required', $containment->name_error);
		}
		
		$this->assertEquals(3, count($container->containments));
	}
	
	public function testUnsetCollection()
	{
		$container = SomeContainer::get('one');
		unset($container->containments[1]);
		$this->assertNull(SomeContainment::get('Another Containment of One'));
		$this->assertEquals('Last Containment of One', $container->containments[2]->name);
		$this->assertEquals(2, count($container->containments));
		
		$this->assertTrue($container->containments->offsetExists(2));
	}
	
	public function testSetIsNotAllowedCollection()
	{
		$container = SomeContainer::get('one');
		try
		{
			$container->containments[2] = SomeContainment::get('Containment of Three');
			$this->fail('Exception expected');
		}
		catch (Exception $e)
		{
		}
	}
}

?>
