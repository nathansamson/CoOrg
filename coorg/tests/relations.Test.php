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
}

?>
