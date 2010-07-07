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

function deldir($path)
{
	$childs = scandir($path);
	foreach ($childs as $c)
	{
		if ($c == '.' || $c == '..') continue;
		$childpath = $path.'/'.$c;
		
		if (is_file($childpath))
		{
			unlink($childpath);
		}
		else if (is_dir($childpath))
		{
			deldir($childpath);
		}
	}
	rmdir($path);
}

class DatamanagerTest extends PHPUnit_Framework_TestCase
{
	private $_m;

	public function setUp()
	{
		mkdir('.test-path/dir1/subdir/test', 0777, true);
		mkdir('.test-path/dir1/otherdir', 0777, true);
		mkdir('.test-path/dir1/adir', 0777, true);
		mkdir('.test-path/dir2/files', 0777, true);
		mkdir('.test-path/dir2/otherfiles', 0777, true);
		mkdir('.test-path/dir2/morefiles', 0777, true);
		
		file_put_contents('.test-path/dir1/afile.txt', 'Some File');
		file_put_contents('.test-path/dir1/someimage.png', 'Some Image');
		file_put_contents('.test-path/dir1/noextension', 'File without extension');
		file_put_contents('.test-path/dir1/extendend.extension.longextension', 'Some Strange File');
		file_put_contents('.test-path/dir1/.noextension', 'No extension!');
		
		$this->_m = new DataManager('.test-path');
	}
	
	public function testFiles()
	{
		$m = $this->_m;
		$dir = $m->get('dir1');
		
		$files = $dir->files();
		$names = array('.noextension', 'adir', 'afile.txt', 
		               'extendend.extension.longextension',
		               'noextension', 'otherdir', 'someimage.png', 'subdir');

		$this->assertEquals(8, count($files));
		foreach ($files as $key => $file)
		{
			$this->assertEquals($names[$key], $file->name());
		}
	}
	
	public function testDelete()
	{
		$m = $this->_m;
		$dir = $m->get('dir2');
		
		$this->assertTrue(is_dir('.test-path/dir2'));
		$dir->delete();
		$this->assertFalse(is_dir('.test-path/dir2'));
		
		$file = $m->get('dir1/someimage.png');
		$file->delete();
		$this->assertFalse(file_exists('.test-path/dir2'));
	}
	
	public function testContent()
	{
		$m = $this->_m;
		
		$f = $m->get('dir1/someimage.png');
		$this->assertEquals('Some Image', $f->content());
		
		$f->content('Change Content');
		$this->assertEquals('Change Content', $f->content());
		$this->assertEquals('Change Content', file_get_contents('.test-path/dir1/someimage.png'));
	}
	
	public function testPath()
	{
		$m = $this->_m;
		
		$f = $m->get('dir1/someimage.png');
		$this->assertEquals('dir1', $f->path());
		
		$d = $m->get('dir1/subdir/test');
		$this->assertEquals('dir1/subdir', $d->path());
	}
	
	public function testExtension()
	{
		$m = $this->_m;
		
		$f = $m->get('dir1/someimage.png');
		$this->assertEquals('png', $f->extension());
		
		$f = $m->get('dir1/extendend.extension.longextension');
		$this->assertEquals('longextension', $f->extension());
		
		$f = $m->get('dir1/noextension');
		$this->assertNull($f->extension());
		
		$f = $m->get('dir1/.noextension');
		$this->assertNull($f->extension());
	}
	
	public function tearDown()
	{
		deldir('.test-path');
	}
}

?>
