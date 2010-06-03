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

class ConfigTest extends PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$config = new Config('coorg/tests/configs/config.test.php');
		
		$this->assertEquals('Google', $config->get('searchEngine'));
		$config->set('searchEngine', 'Bing');
		$this->assertEquals('Bing', $config->get('searchEngine'));
		
		$this->assertEquals(array('a', 'b', false), $config->get('array'));
		
		$this->assertNull($config->get('keyNotFound'));
	}
	
	public function testSave()
	{
		copy('coorg/tests/configs/save.test.php.original',
		     'coorg/tests/configs/save.test.php');
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$config->set('myChangedKey', 'mySavedValue');
		$config->save();
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$this->assertEquals('mySavedValue', $config->get('myChangedKey'));
		$this->assertEquals('myUnChangedValue', $config->get('myUnChangedKey'));
	}
	
	public function testSaveArray()
	{
		copy('coorg/tests/configs/save.test.php.original',
		     'coorg/tests/configs/save.test.php');
		
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$config->set('myArrayValue', array('Google', array('A', 'B')));
		$config->save();
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$this->assertEquals(array('Google', array('A', 'B')),
		                      $config->get('myArrayValue'));
	}
	
	public function testSaveSpecialChars()
	{
		copy('coorg/tests/configs/save.test.php.original',
		     'coorg/tests/configs/save.test.php');
		
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$config->set('myArrayValue', array("Google's best browser\n"));
		$config->set('myStrangeValue', 'é & " \ () <?php shit; ?> \\\'');
		$config->save();
		
		$config = new Config('coorg/tests/configs/save.test.php');
		$this->assertEquals(array("Google's best browser\n"),
		                      $config->get('myArrayValue'));
		$this->assertEquals('é & " \ () <?php shit; ?> \\\'',
		                      $config->get('myStrangeValue'));
	}
	
	/**
	 * @expectedException ConfigFileNotWritableException
	*/
	public function testSaveNotWritable()
	{
		$config = new Config('coorg/tests/configs/notwritable.test.php');
		$config->save();
	}

}

?>
