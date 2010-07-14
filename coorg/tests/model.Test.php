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

class Rot13Variant implements IPropertyVariant
{
	private $_original;

	private function __construct(IProperty $org)
	{
		$this->_original = $org;
	}

	public function get()
	{
		return str_rot13($this->_original->get());
	}
	
	public function set($value)
	{
		$this->_original->set(str_rot13($value));
	}
	
	public function update() {}
	
	public static function instance(IProperty $p, $args)
	{
		return new Rot13Variant($p);
	}
}

class MockExtends
{
	public static $before = false;
	public static $class;
	
	public function __construct($args)
	{
		self::$class = $args[0];
	}

	public function beforeInsert()
	{
		self::$before = true;
	}
	
	public function afterInsert() {}
	public function beforeUpdate() {}
	public function afterUpdate() {}
	public function afterDelete() {}
	public function connect() {}
	
	public function properties()
	{
		return array();
	}
}

/**
 * @property primary; name String('Name', 64); required
 * @property description String('Description');
 * @property email Email('Email'); required
 * @property conditional Integer('Conditional value'); required only('special') 
 * @property writeonly; shadowProperty String('Shadow'); required only('insert')
 * @property protected; rot13name String('Name', 64); required
 * @variant rot13 rot13 name
 * @property nodb; someRandomValue Integer('Integer'); required
 * @extends MockExtends
*/
class MockModel extends DBModel
{
	public function __construct($name = null, $description = null, $email = null)
	{
		parent::__construct();
		$this->name = $name;
		$this->description = $description;
		$this->email = $email;
	}
	
	public function doSomethingSpecialWhichTriggersConditionalValidation()
	{
		parent::validate('special');
	}
	
	public function checkInternalP($p)
	{
		return $p == $this->rot13name;
	}
	
	public static function getByName($name)
	{
		$q = DB::prepare('SELECT * FROM MockModel WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'MockModel');
		}
		else
		{
			return null;
		}
	}
	
	
	protected function beforeInsert()
	{
		$this->rot13name = str_rot13($this->name);
	}
	
	protected function beforeUpdate()
	{
		$this->rot13name = str_rot13($this->name);
	}
	
	protected function afterFetch()
	{
		$this->someRandomValue = rand();
	}
}

/**
 * @property isaExtension String('Some String', 32); required
 * @property containerID String('ContainerID"', 32);
*/
class IsAMockModel extends MockModel
{
	public static function get($name)
	{
		$q = DB::prepare('SELECT * FROM IsAMockModel 
		                      NATURAL JOIN MockModel
		                  WHERE name=:name');
		$q->execute(array(':name' => $name));
		return self::fetch($q->fetch(), 'IsAMockModel');
	}
}

class ModelTest extends CoOrgModelTest
{
	const dataset = 'modeltest.dataset.xml';
	
	public function setUp()
	{
		parent::setUp();
		MockExtends::$before = false;
		MockExtends::$class = '';
	}

	public function testModelCreate()
	{
		$m = new MockModel('nathan', null, 'email@email.com');
		$m->shadowProperty = 'Some Value';
		$m->someRandomValue = 222;
		$m->save();
		
		$m = MockModel::getByName('nathan');
		$this->assertNotNull($m);
		$this->assertEquals('nathan', $m->name);
		$this->assertNull($m->description);
		$this->assertEquals('email@email.com', $m->email);
		$this->assertTrue($m->someRandomValue > 0);

		$m = MockModel::getByName('whatsup');
		$this->assertNull($m);
	}
	
	public function testModelUpdate()
	{
		$m = new MockModel('nathan', null, 'email@email.com');
		$m->shadowProperty = 'Some Value';
		$m->someRandomValue = 222;
		$m->save();
		$m = new MockModel('nele', null, 'email2@email.com');
		$m->shadowProperty = 'Some Value';
		$m->someRandomValue = 222;
		$m->save();
		
		$m = MockModel::getByName('nathan');
		$m->description = 'XYZ';
		$m->email = 'anothermail@mail.com';
		$m->save();
		
		$m = MockModel::getByName('nathan');
		$this->assertNotNull($m);
		$this->assertEquals('nathan', $m->name);
		$this->assertEquals('XYZ', $m->description);
		$this->assertEquals('anothermail@mail.com', $m->email);
		
		$m = MockModel::getByName('nele');
		$this->assertNotNull($m);
		$this->assertEquals('nele', $m->name);
		$this->assertNull($m->description);
		$this->assertEquals('email2@email.com', $m->email);
	}
	
	public function testModelKeyUpdate()
	{
		$nathan = new MockModel('nathan', null, 'email@email.com');
		$nathan->shadowProperty = 'Some Value';
		$nathan->someRandomValue = 2;
		$nathan->save();
		$nele = new MockModel('nele', null, 'email2@email.com');
		$nele->shadowProperty = 'Some Value';
		$nele->someRandomValue = 2;
		$nele->save();
		
		$nathan->name = 'someothername';
		$nathan->email = 'someothermail@mail.com';
		$nathan->save();
		
		$nele->name = 'nathan';
		$nele->save();
		
		$nathan->description = 'XYZ';
		$nathan->save();
		
		$someother = MockModel::getByName('someothername');
		$nathan2 = MockModel::getByName('nathan');
		
		$this->assertEquals('XYZ', $someother->description);
		$this->assertEquals('someothermail@mail.com', $someother->email);
		
		$this->assertNull($nathan2->description);
		$this->assertEquals('email2@email.com', $nathan2->email);
	}
	
	public function testValidation()
	{
		$n = new MockModel('', '', 'email@email.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 221;
		try
		{
			$n->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Name is required', $n->name_error);
		}
	}
	
	public function testConditionalValidationRequired()
	{
		$n = new MockModel('a', 'b', 'email@mail.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 221;
		$n->save();
		
		try
		{
			$n->doSomethingSpecialWhichTriggersConditionalValidation();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Conditional value is required', 
			                    $n->conditional_error);
		}
	}
	
	public function testConditionalValidationNotRequiredButWrong()
	{
		$n = new MockModel('a', 'b', 'cccc@bcc.com');
		$n->shadowProperty = '...';
		$n->conditional = 'aple';
		
		try
		{
			$n->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Conditional value is not a valid number', 
			                    $n->conditional_error);
		}
	}
	
	public function testValidateNoDB()
	{
		$n = new MockModel('a', 'b', 'cccc@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 'abba';
		
		try
		{
			$n->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Integer is not a valid number', $n->someRandomValue_error);
		}
	}
	
	public function testShadowProperty()
	{
		$n = new MockModel('a', '', 'cccc@bcc.com');
		
		try
		{
			$n->save(); // Do not set the shadow property required on insert.
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Shadow is required', $n->shadowProperty_error);
		}
	}
	
	public function testInternalProperty()
	{
		$n = new MockModel('abczyx', '', 'cccc@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 22;
		$n->save();
		
		$n = MockModel::getByName('abczyx');
		$this->assertNotNull($n);
		$this->assertTrue($n->checkInternalP('nopmlk'));
		
		$n->name = 'nopmlk';
		$n->save();
		
		$n = MockModel::getByName('nopmlk');
		$this->assertTrue($n->checkInternalP('abczyx'));
	}
	
	public function testUpdateNothing()
	{
		$n = new MockModel('abczyx', '', 'cccc@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = '33';
		$n->save();
		
		$n = MockModel::getByName('abczyx');
		$this->assertNotNull($n);
		$n->save();
		
		$n = MockModel::getByName('abczyx');
		$this->assertTrue($n->checkInternalP('nopmlk'));
	}
	
	public function testDelete()
	{
		$n = new MockModel('abczyx', '', 'cccc@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 33;
		$n->save();
		
		$n = new MockModel('dvorak', '', 'sdsd@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 33;
		$n->save();
		
		$n = new MockModel('qwerty', '', 'ssf@bcc.com');
		$n->shadowProperty = '...';
		$n->someRandomValue = 33;
		$n->save();
		
		$n->delete();
		
		$this->assertNotNull(MockModel::getByName('abczyx'));
		$this->assertNotNull(MockModel::getByName('dvorak'));
		$this->assertNull(MockModel::getByName('qwerty'));
	}
	
	public function testVariants()
	{
		$n = new MockModel('abczyx', '', '...');
		$this->assertEquals('nopmlk', $n->rot13);
		$n->rot13 = 'abczyx';
		$this->assertEquals('abczyx', $n->rot13);
		$this->assertEquals('nopmlk', $n->name);
	}
	
	public function testCreateISA()
	{
		$isa = new IsAMockModel;
		$isa->name = 'Some ISA Name';
		$isa->shadowProperty = 'shadow';
		$isa->email = 'isa@isa.com';
		$isa->isaExtension = 'Ext';
		$isa->someRandomValue = 21;
		$this->assertFalse(MockExtends::$before);
		$isa->save();
		$this->assertTrue(MockExtends::$before);
		
		$base = MockModel::getByName('Some ISA Name');
		$this->assertEquals('isa@isa.com', $base->email);
		
		$risa = IsAMockModel::get('Some ISA Name');
		$this->assertEquals('isa@isa.com', $risa->email);
		$this->assertEquals('Ext', $risa->isaExtension);
		$this->assertEquals(str_rot13('Some ISA Name'), $risa->rot13);
	}
	
	public function testUpdateISA()
	{
		$isa = IsAMockModel::get('base-mock');
		$isa->isaExtension = 'Updated Ext';
		$isa->email = 'updated@isa.com';
		$isa->description = 'Update';
		$isa->save();
		
		$risa = IsAMockModel::get('base-mock');
		$this->assertEquals('Updated Ext', $risa->isaExtension);
		$this->assertEquals('updated@isa.com', $risa->email);
		$this->assertEquals('Update', $risa->description);
	}
	
	public function testDeleteISA()
	{
		$isa = IsAMockModel::get('base-mock');
		$isa->delete();
		
		$risa = IsAMockModel::get('base-mock');
		$this->assertNull($risa);
		
		$rbase = MockModel::getByName('base-mock');
		$this->assertNull($rbase);
	}
	
	public function testBatchSave()
	{
		$nathan = new MockModel('nathan', null, 'email@email.com');
		$nathan->shadowProperty = 'Some Value';
		$nathan->someRandomValue = 2;
		$nele = new MockModel('nele', null, 'email2@email.com');
		$nele->shadowProperty = 'Some Value';
		
		try
		{
			DBModel::batchSave(array($nathan, $nele));
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Integer is required', $nele->someRandomValue_error);
			$this->assertNull(MockModel::getByName('nathan'));
		}
		$nele->someRandomValue = 3;
		
		DBModel::batchSave(array($nathan, $nele));
		$this->assertNotNull(MockModel::getByName('nathan'));
		$this->assertNotNull(MockModel::getByName('nele'));
	}
}

?>
