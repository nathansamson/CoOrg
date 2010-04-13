<?php

/**
 * @primaryproperty name String('Name', 64); required
 * @property description String('Description');
 * @property email Email('Email'); required
 * @property conditional Integer('Conditional value'); required only('special') 
 * @shadowproperty shadowProperty String('Shadow'); required only('insert')
 * @internalproperty rot13name String('Name', 64); required
*/
class MockModel extends DBModel
{
	public function __construct($name, $description, $email)
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
		return $p == $this->property('rot13name', $p)->get();
	}
	
	public static function getByName($name)
	{
		$q = DB::prepare('SELECT * FROM Mock WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		if ($row = $q->fetch(PDO::FETCH_ASSOC))
		{
			$user = new MockModel($row['name'], $row['description'], $row['email']);
			$user->property('rot13name')->set($row['rot13name']);
			$user->setSaved();
			return $user;
		}
		else
		{
			return null;
		}
	}
	
	
	protected function beforeInsert()
	{
		$this->property('rot13name')->set(str_rot13($this->name));
	}
	
	protected function beforeUpdate()
	{
		$this->property('rot13name')->set(str_rot13($this->name));
	}
}

class ModelTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/modeltest.dataset.xml';
	}

	public function testModelCreate()
	{
		$m = new MockModel('nathan', null, 'email@email.com');
		$m->shadowProperty = 'Some Value';
		$m->save();
		
		$m = MockModel::getByName('nathan');
		$this->assertNotNull($m);
		$this->assertEquals('nathan', $m->name);
		$this->assertNull($m->description);
		$this->assertEquals('email@email.com', $m->email);

		$m = MockModel::getByName('whatsup');
		$this->assertNull($m);
	}
	
	public function testModelUpdate()
	{
		$m = new MockModel('nathan', null, 'email@email.com');
		$m->shadowProperty = 'Some Value';
		$m->save();
		$m = new MockModel('nele', null, 'email2@email.com');
		$m->shadowProperty = 'Some Value';
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
		$nathan->save();
		$nele = new MockModel('nele', null, 'email2@email.com');
		$nele->shadowProperty = 'Some Value';
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
		$n->save();
		
		$n = MockModel::getByName('abczyx');
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
		$n->save();
		
		$n = MockModel::getByName('abczyx');
		$n->save();
		
		$n = MockModel::getByName('abczyx');
		$this->assertTrue($n->checkInternalP('nopmlk'));
	}
}

?>
