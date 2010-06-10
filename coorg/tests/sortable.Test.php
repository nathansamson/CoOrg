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
 * @property primary; name String('Name', 64); required
 * @property photobook String('Photobook', 64); required
 * @extends Sortable photobook photobook
*/
class Photos extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	//TODO: fix me to use callStatic, which does not exist on PHP 5.2
	public static function photobook()
	{
		$args = func_get_args();
		return Model::callStatic('Photos', 'photobook', $args);
	}
	
	public static function get($name)
	{
		$q = DB::prepare('SELECT * FROM Photos WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'Photos');
		}
		else
		{
			return null;
		}
	}
}

class ModelSortableTest extends CoOrgModelTest
{
	const dataset = 'modeltest.dataset.xml';
	
	public function testGetList()
	{
		$photobook = Photos::photobook('A');
		$this->assertEquals(5, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1', $photobook[0]->name);
		$this->assertEquals('A', $photobook[0]->photobook);
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
		$this->assertEquals('B', $photobook[5]->photobook);
	}
	
	public function testGet()
	{
		$photo = Photos::get('Photo 6B');
		$this->assertNotNUll($photo);
		$this->assertEquals(5, $photo->sequence);
	}
	
	public function testInsert()
	{
		$photo = new Photos;
		$photo->name = 'New photo';
		$photo->photobook = 'A';
		$photo->save();
		
		$photobook = Photos::photobook('A');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('New photo', $photobook[5]->name);
		$this->assertEquals('A', $photobook[5]->photobook);
	}
	
	public function testInsertAtPosition()
	{
		$photo = new Photos;
		$photo->name = 'New photo';
		$photo->photobook = 'A';
		$photo->sequence = 3;
		$photo->save();
		
		$photobook = Photos::photobook('A');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1', $photobook[0]->name);
		$this->assertEquals('Photo 2', $photobook[1]->name);
		$this->assertEquals('Photo 3', $photobook[2]->name);
		$this->assertEquals('New photo', $photobook[3]->name);
		$this->assertEquals('Photo 4', $photobook[4]->name);
		$this->assertEquals('Photo 5', $photobook[5]->name);
	}
	
	public function testInsertNegative()
	{
		$photo = new Photos;
		$photo->name = 'New photo';
		$photo->photobook = 'A';
		$photo->sequence = -3;
		$photo->save();
		
		$photobook = Photos::photobook('A');
		$this->assertEquals(0, $photo->sequence);
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('New photo', $photobook[0]->name);
		$this->assertEquals('Photo 1', $photobook[1]->name);
		$this->assertEquals('Photo 2', $photobook[2]->name);
		$this->assertEquals('Photo 3', $photobook[3]->name);
		$this->assertEquals('Photo 4', $photobook[4]->name);
		$this->assertEquals('Photo 5', $photobook[5]->name);
	}
	
	public function testInsertTooBig()
	{
		$photo = new Photos;
		$photo->name = 'New photo';
		$photo->photobook = 'A';
		$photo->sequence = 13;
		$photo->save();
		
		$photobook = Photos::photobook('A');
		$this->assertEquals(5, $photo->sequence);
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1', $photobook[0]->name);
		$this->assertEquals('Photo 2', $photobook[1]->name);
		$this->assertEquals('Photo 3', $photobook[2]->name);
		$this->assertEquals('Photo 4', $photobook[3]->name);
		$this->assertEquals('Photo 5', $photobook[4]->name);
		$this->assertEquals('New photo', $photobook[5]->name);
	}
	
	public function testInsertFirst()
	{
		$photo = new Photos;
		$photo->name = 'New photo';
		$photo->photobook = 'D';
		$photo->save();
		
		$photobook = Photos::photobook('D');
		$this->assertEquals(1, count($photobook));
		$this->assertEquals(0, $photo->sequence);
		$this->assertEquals(0, $photobook[0]->sequence);
		
		
		$photo = new Photos;
		$photo->name = 'Another photo';
		$photo->photobook = 'D';
		$photo->save();
		
		$photobook = Photos::photobook('D');
		$this->assertEquals(2, count($photobook));
		$this->assertEquals(1, $photo->sequence);
		$this->assertEquals(1, $photobook[1]->sequence);
	}
	
	public function testUpdate()
	{
		$photo = Photos::get('Photo 2B');
		$photo->sequence = 4;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 3B', $photobook[1]->name);
		$this->assertEquals('Photo 4B', $photobook[2]->name);
		$this->assertEquals('Photo 5B', $photobook[3]->name);
		$this->assertEquals('Photo 2B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
		
		
		$photo->sequence = 1;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 3B', $photobook[2]->name);
		$this->assertEquals('Photo 4B', $photobook[3]->name);
		$this->assertEquals('Photo 5B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
	}
	
	public function testUpdateFirst()
	{
		$photo = Photos::get('Photo 1B');
		$photo->sequence = 2;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 2B', $photobook[0]->name);
		$this->assertEquals('Photo 3B', $photobook[1]->name);
		$this->assertEquals('Photo 1B', $photobook[2]->name);
		$this->assertEquals('Photo 4B', $photobook[3]->name);
		$this->assertEquals('Photo 5B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
		
		$photo->sequence = 0;
		$photo->save();
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 3B', $photobook[2]->name);
		$this->assertEquals('Photo 4B', $photobook[3]->name);
		$this->assertEquals('Photo 5B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
	}
	
	public function testUpdateLast()
	{
		$photo = Photos::get('Photo 6B');
		$photo->sequence = 2;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 6B', $photobook[2]->name);
		$this->assertEquals('Photo 3B', $photobook[3]->name);
		$this->assertEquals('Photo 4B', $photobook[4]->name);
		$this->assertEquals('Photo 5B', $photobook[5]->name);
		
		$photo->sequence = 5;
		$photo->save();
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 3B', $photobook[2]->name);
		$this->assertEquals('Photo 4B', $photobook[3]->name);
		$this->assertEquals('Photo 5B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
	}
	
	public function testUpdateNegative()
	{
		$photo = Photos::get('Photo 3B');
		$photo->sequence = -2;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 3B', $photobook[0]->name);
		$this->assertEquals('Photo 1B', $photobook[1]->name);
		$this->assertEquals('Photo 2B', $photobook[2]->name);
		$this->assertEquals('Photo 4B', $photobook[3]->name);
		$this->assertEquals('Photo 5B', $photobook[4]->name);
		$this->assertEquals('Photo 6B', $photobook[5]->name);
	}
	
	public function testUpdateTooBig()
	{
		$photo = Photos::get('Photo 3B');
		$photo->sequence = 12;
		$photo->save();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(6, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 4B', $photobook[2]->name);
		$this->assertEquals('Photo 5B', $photobook[3]->name);
		$this->assertEquals('Photo 6B', $photobook[4]->name);
		$this->assertEquals('Photo 3B', $photobook[5]->name);
	}
	
	public function testDelete()
	{
		$photo = Photos::get('Photo 3B');
		$photo->delete();
		
		$photobook = Photos::photobook('B');
		$this->assertEquals(5, count($photobook));
		$this->sanityCheck($photobook);
		$this->assertEquals('Photo 1B', $photobook[0]->name);
		$this->assertEquals('Photo 2B', $photobook[1]->name);
		$this->assertEquals('Photo 4B', $photobook[2]->name);
		$this->assertEquals('Photo 5B', $photobook[3]->name);
		$this->assertEquals('Photo 6B', $photobook[4]->name);
	}
	
	private function sanityCheck($array)
	{
		foreach ($array as $key => $value)
		{
			$this->assertEquals($key, $value->sequence);
		}
	}
}
