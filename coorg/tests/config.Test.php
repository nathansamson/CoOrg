<?php

include_once 'PHPUnit/Framework.php';
include_once 'coorg/config.class.php';

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
