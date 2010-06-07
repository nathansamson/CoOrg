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

class MockPager extends Pager
{
	public function fetch($row)
	{
		return MockModel::fetch($row, 'MockModel');
	}
}

class pagerTester extends CoOrgModelTest
{
	const dataset = 'pager.dataset.xml';
	
	public function testOnlyOnePage()
	{
		$pager = new MockPager('SELECT * FROM Mock WHERE name=:name',
		                       array(':name'=>'AABB'));
		$this->assertEquals(1, count($pager->execute(1, 10)));
		$this->assertEquals(1, count($pager->pages(10)));
	}
	
	public function testGapRight()
	{
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(3, count($pager->execute(1, 3)));
		$pages = $pager->pages(7);
		$this->assertEquals(7, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertTrue($pages[0]['current']);
		
		$this->assertEquals(2, $pages[1]['id']);
		$this->assertFalse($pages[1]['current']);
		
		$this->assertEquals(3, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(4, $pages[3]['id']);
		$this->assertFalse($pages[3]['current']);
		
		$this->assertEquals(5, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals('...', $pages[5]['id']);
		$this->assertFalse($pages[5]['current']);
		$this->assertEquals(6, $pages[5]['gap']);
		
		$this->assertEquals(12, $pages[6]['id']);
		$this->assertFalse($pages[6]['current']);
		
		
		$this->assertEquals(3, count($pager->execute(2, 3)));
		$pages = $pager->pages(7);
		$this->assertEquals(7, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertFalse($pages[0]['current']);
		
		$this->assertEquals(2, $pages[1]['id']);
		$this->assertTrue($pages[1]['current']);
		
		$this->assertEquals(3, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(4, $pages[3]['id']);
		$this->assertFalse($pages[3]['current']);
		
		$this->assertEquals(5, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals('...', $pages[5]['id']);
		$this->assertFalse($pages[5]['current']);
		$this->assertEquals(6, $pages[5]['gap']);
		
		$this->assertEquals(12, $pages[6]['id']);
		$this->assertFalse($pages[6]['current']);
	}
	
	public function testGapLeft()
	{
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(3, count($pager->execute(12, 3)));
		$pages = $pager->pages(7);
		$this->assertEquals(7, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertFalse($pages[0]['current']);
		
		$this->assertEquals('...', $pages[1]['id']);
		$this->assertFalse($pages[1]['current']);
		$this->assertEquals(6, $pages[1]['gap']);
		
		$this->assertEquals(8, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(9, $pages[3]['id']);
		$this->assertFalse($pages[3]['current']);
		
		$this->assertEquals(10, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals(11, $pages[5]['id']);
		$this->assertFalse($pages[5]['current']);
		
		$this->assertEquals(12, $pages[6]['id']);
		$this->assertTrue($pages[6]['current']);
		
		
		$this->assertEquals(3, count($pager->execute(11, 3)));
		$pages = $pager->pages(7);
		$this->assertEquals(7, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertFalse($pages[0]['current']);
		
		$this->assertEquals('...', $pages[1]['id']);
		$this->assertFalse($pages[1]['current']);
		$this->assertEquals(6, $pages[1]['gap']);
		
		$this->assertEquals(8, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(9, $pages[3]['id']);
		$this->assertFalse($pages[3]['current']);
		
		$this->assertEquals(10, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals(11, $pages[5]['id']);
		$this->assertTrue($pages[5]['current']);
		
		$this->assertEquals(12, $pages[6]['id']);
		$this->assertFalse($pages[6]['current']);
	}
	
	public function testGapBoth()
	{
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(3, count($pager->execute(6, 3)));
		$pages = $pager->pages(7);
		$this->assertEquals(7, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertFalse($pages[0]['current']);
		
		$this->assertEquals('...', $pages[1]['id']);
		$this->assertFalse($pages[1]['current']);
		$this->assertEquals(3, $pages[1]['gap']);
		
		$this->assertEquals(5, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(6, $pages[3]['id']);
		$this->assertTrue($pages[3]['current']);
		
		$this->assertEquals(7, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals('...', $pages[5]['id']);
		$this->assertFalse($pages[5]['current']);
		$this->assertEquals(4, $pages[5]['gap']);
		
		$this->assertEquals(12, $pages[6]['id']);
		$this->assertFalse($pages[6]['current']);
	}
	
	public function testHasPrevAndNet()
	{
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(3, count($pager->execute(6, 3)));
		$this->assertEquals(5, $pager->prev());
		$this->assertEquals(7, $pager->next());
		
		$this->assertEquals(3, count($pager->execute(1, 3)));
		$this->assertNull($pager->prev());
		$this->assertEquals(2, $pager->next());
		
		$this->assertEquals(3, count($pager->execute(12, 3)));
		$this->assertEquals(11, $pager->prev());
		$this->assertNull($pager->next());
		
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(36, count($pager->execute(1, 40)));
		$this->assertNull($pager->prev());
		$this->assertNull($pager->next());
	}
	
	public function testNoEllips()
	{
		$pager = new MockPager('SELECT * FROM Mock ORDER BY name');
		$this->assertEquals(7, count($pager->execute(2, 7)));
		$pages = $pager->pages(7);
		$this->assertEquals(6, count($pages));
		
		$this->assertEquals(1, $pages[0]['id']);
		$this->assertFalse($pages[0]['current']);
		
		$this->assertEquals(2, $pages[1]['id']);
		$this->assertTrue($pages[1]['current']);
		
		$this->assertEquals(3, $pages[2]['id']);
		$this->assertFalse($pages[2]['current']);
		
		$this->assertEquals(4, $pages[3]['id']);
		$this->assertFalse($pages[3]['current']);
		
		$this->assertEquals(5, $pages[4]['id']);
		$this->assertFalse($pages[4]['current']);
		
		$this->assertEquals(6, $pages[5]['id']);
		$this->assertFalse($pages[5]['current']);
		
		$this->assertEquals(1, count($pager->execute(6, 7)));
	}
	
	public function testUnlimited()
	{
		$pager = new MockPager('SELECT * FROM Mock');
		$this->assertEquals(36, count($pager->execute(0, 0)));
		$this->assertNull($pager->pages(10));
	}
}

?>
