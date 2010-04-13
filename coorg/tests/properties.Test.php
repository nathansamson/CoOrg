<?php

class PropertiesTest extends PHPUnit_Framework_TestCase
{
	public function testString()
	{
		$s = new PropertyString('Name', 10);
		$s->set('azerty');
		$s->setUnchanged();
		
		$this->assertEquals('azerty', $s->old());
		$this->assertEquals('azerty', $s->get());
		$this->assertEquals('azerty', $s->db());
		
		$s->set('qwerty');
		$this->assertTrue($s->changed());
		$s->set(' azerty');
		$this->assertFalse($s->changed());
		
		$s->set('');
		$this->assertEquals('azerty', $s->old());
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		
		$s->set('tsss');
		$this->assertEquals('azerty', $s->old());
		$this->assertEquals('tsss', $s->get());
		$this->assertEquals('tsss', $s->db());
		
		$s->set(' a             ');
		$this->assertEquals('a', $s->get());
		$this->assertEquals('a', $s->db());
		$this->assertEquals(' a             ', $s->raw());
		
		$this->assertTrue($s->validate(''));
	}

	public function testEmptyString()
	{
		$s = new PropertyString('Name');
		
		$s->set('');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		
		$s->set('  ');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
	}
	
	public function testEmptyRequiredString()
	{
		$s = new PropertyString('Name');
		$s->required();
		
		$s->set('');
		$this->assertFalse($s->validate(''));
		
		$s->set('  ');
		$this->assertFalse($s->validate(''));
	}
	
	public function testEmptySometimesRequiredString()
	{
		$s = new PropertyString('Name', 5);
		$s->required();
		$s->only('abba');
		
		$s->set('');
		$this->assertTrue($s->validate(''));
		
		$s->set('  ');
		$this->assertTrue($s->validate(''));
		
		$s->set('  ');
		$this->assertFalse($s->validate('abba'));
		$this->assertEquals('Name is required', $s->errors());
		
		$s->error(null);
		$s->set('googlededoo');
		$this->assertFalse($s->validate(''));
		$this->assertEquals('Name is too long', $s->errors());
	}
	
	public function testLongString()
	{
		$s = new PropertyString('Name', 5);
		$s->set('mlsdekzjxwcklek');
		$this->assertFalse($s->validate(''));
		$this->assertEquals('Name is too long', $s->errors());
	}
	
	public function testEmail()
	{
		$s = new PropertyEmail('Email');
		$s->set('valid@valid.com ');
		$s->setUnchanged();
		
		$this->assertEquals('valid@valid.com', $s->old());
		$this->assertEquals('valid@valid.com', $s->get());
		$this->assertEquals('valid@valid.com', $s->db());
		$this->assertTrue($s->validate(''));
		
		$s->set('qwerty@qwerty.com');
		$this->assertTrue($s->changed());
		$s->set(' valid@valid.com');
		$this->assertFalse($s->changed());
		
		$s->set('  ');
		$this->assertEquals('valid@valid.com', $s->old());
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		$this->assertTrue($s->validate(''));
	}
	
	public function testEmptyEmail()
	{
		$s = new PropertyEmail('Email');
		
		$s->set('');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		
		$s->set('  ');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
	}
	
	public function testEmptyRequiredEmail()
	{
		$s = new PropertyEmail('Email');
		$s->required();
		
		$s->set('');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		$this->assertFalse($s->validate(''));
		$this->assertEquals('Email is required', $s->errors());
		
		$s->error(null);
		$s->set('  ');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		$this->assertFalse($s->validate(''));
		$this->assertEquals('Email is required', $s->errors());
	}
	
	public function testEmptySometimesRequiredEmail()
	{
		$s = new PropertyEmail('Email');
		$s->required();
		$s->only('abba');
		
		$s->set('');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		$this->assertTrue($s->validate(''));
		
		$s->error(null);
		$s->set('  ');
		$this->assertNull($s->get());
		$this->assertNull($s->db());
		$this->assertFalse($s->validate('abba'));
		$this->assertEquals('Email is required', $s->errors());
	}
	
	public function testInvalidEmail()
	{
		// Most examples are copied from http://www.linuxjournal.com/article/9585
	
		$s = new PropertyEmail('Email');
		
		$s->set('azerty@gmail.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('azerty+beta@gmail.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('azerty.beta@gmail.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('aze_rty@gmail.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('Abc\@def@example.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('customer/department=shipping@example.com');
		$this->assertTrue($s->validate(''));
		
		$s->set('!def!xyz%abc@example.com');
		$this->assertTrue($s->validate(''));
		
		$s->set("dclo@us.ibm.com");
		$this->assertTrue($s->validate(''));
		
		$s->set("abc\\@def@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("abc\\\\@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("Fred\\ Bloggs@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("Joe.\\\\Blow@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("\"Abc@def\"@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("\"Fred Bloggs\"@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("customer/department=shipping@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("\$A12345@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("!def!xyz%abc@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("_somename@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("user+mailbox@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("peter.piper@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("Doug\\ \\\"Ace\\\"\\ Lovell@example.com");
		$this->assertTrue($s->validate(''));

		$s->set("\"Doug \\\"Ace\\\" L.\"@example.com");
		$this->assertTrue($s->validate(''));

		$s->set('aze..rty@gmail.com');
		$this->assertFalse($s->validate(''));
		
		$s->set('azerty@gmail..com');
		$this->assertFalse($s->validate(''));
		
		$s->set('azerty');
		$this->assertFalse($s->validate(''));
		
		$s->set('azerty@com');
		$this->assertFalse($s->validate(''));
		
		$s->set('azerty@gsdsd.b');
		$this->assertFalse($s->validate(''));
		
		$s->set('azerty@.');
		$this->assertFalse($s->validate(''));
		
		$s->set("abc@def@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("abc\\\\@def@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("abc\\@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("doug@");
		$this->assertFalse($s->validate(''));

		$s->set("\"qu@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("ote\"@example.com");
		$this->assertFalse($s->validate(''));

		$s->set(".dot@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("dot.@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("two..dot@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("\"Doug \"Ace\" L.\"@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("Doug\\ \\\"Ace\\\"\\ L\\.@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("hello world@example.com");
		$this->assertFalse($s->validate(''));

		$s->set("gatsby@f.sc.ot.t.f.i.tzg.era.l.d.");
		$this->assertFalse($s->validate(''));
	}
	
	public function testInteger()
	{
		$i = new PropertyInteger('Value');
		$i->set(24);
		$i->setUnchanged();
		
		$this->assertEquals(24, $i->old());
		$this->assertEquals(24, $i->get());
		$this->assertEquals(24, $i->db());
		
		$i->set('38');
		$this->assertTrue($i->changed());
		$i->set(' 24');
		$this->assertFalse($i->changed());
		
		$i->set('+2234 ');
		$this->assertTrue($i->validate(''));
		$this->assertEquals(24, $i->old());
		$this->assertEquals(2234, $i->get());
		$this->assertEquals(2234, $i->db());
		
		$i->set(' -2234');
		$this->assertTrue($i->validate(''));
		$this->assertEquals(-2234, $i->get());
		$this->assertEquals(-2234, $i->db());
		
		$i->set('22+34');
		$this->assertFalse($i->validate(''));
		
		$i->set('24i');
		$this->assertEquals('24i', $i->raw());
		$this->assertFalse($i->validate(''));
		
		$i->set('a');
		$this->assertFalse($i->validate(''));
		
		$i->set('24.5');
		$this->assertFalse($i->validate(''));
		
		$i->set('24,5');
		$this->assertFalse($i->validate(''));
		
		$i->set('23^2');
		$this->assertFalse($i->validate(''));
		
		$i->set('23  2');
		$this->assertFalse($i->validate(''));
		
		$i->set('');
		$this->assertNull($i->get());
		$this->assertNull($i->db());
		
		$i->set(' ');
		$this->assertNull($i->get());
		$this->assertNull($i->db());
		
		$i->set('0');
		$this->assertEquals(0, $i->get());
		$this->assertEquals(0, $i->db());
	}
	
	public function testEmptyInt()
	{
		$i = new PropertyInteger('Int');
		
		$i->set('');
		$this->assertTrue($i->validate(''));
		
		$i->set(' ');
		$this->assertTrue($i->validate(''));
	}
	
	public function testEmptyRequiredInt()
	{
		$i = new PropertyInteger('Int');
		$i->required();
		
		$i->set('');
		$this->assertFalse($i->validate(''));
		$this->assertEquals('Int is required', $i->errors());
		
		$i->set(' ');
		$this->assertFalse($i->validate(''));
	}
	
	public function testEmptySometimesRequiredInt()
	{
		$i = new PropertyInteger('Int');
		$i->required();
		$i->only('ac/dc');
	
		$i->set('');
		$this->assertTrue($i->validate(''));
		$this->assertFalse($i->validate('ac/dc'));
		
		$i->set(' ');
		$this->assertTrue($i->validate(''));
		$this->assertFalse($i->validate('ac/dc'));
	}
	
	public function testTooBigInt()
	{
		$i = new PropertyInteger('Int', 255);
		
		$i->set(' 345');
		$this->assertFalse($i->validate(''));
		$this->assertEquals('Int is a too large number', $i->errors());
		
		$i->error(null);
		$i->set(345);
		$this->assertFalse($i->validate(''));
		$this->assertEquals('Int is a too large number', $i->errors());
		
		$i->error(null);
		$i->set(' 255');
		$this->assertTrue($i->validate(''));
	}
	
	public function testDate()
	{
		$d = new PropertyDate('Date');
		
		$d->set('2010-4-13');
		$this->assertEquals(1271109600, $d->get());
		$this->assertEquals('2010-04-13', $d->db());
		$this->assertNull($d->old());
		$d->setUnchanged();
		$this->assertEquals('2010-04-13', $d->old());
		$this->assertTrue($d->validate(''));
		
		$d->set(1271109610);
		$this->assertFalse($d->changed());
		$this->assertEquals(1271109600, $d->get());
		$this->assertTrue($d->validate(''));
		
		$d->set('2010/03/13');
		$this->assertTrue($d->changed());
		$this->assertEquals(1268434800, $d->get());
		$this->assertEquals('2010-03-13', $d->db());
		$this->assertTrue($d->validate(''));
		
		$d = new PropertyDate('Date');
		$d->set('1-1-1');
		$this->assertTrue($d->validate(''));
		
		$d->set('1960-11-03');
		$this->assertEquals('1960-11-03', $d->db());
		$this->assertTrue($d->validate(''));
	}
	
	public function testEmptyDate()
	{
		$d = new PropertyDate('Date');
		$this->assertNull($d->get());
		$this->assertNull($d->db());
		$d->set(' ');
		$this->assertNull($d->get());
		$this->assertNull($d->db());
		$d->set(0);
		$this->assertNull($d->get());
		$this->assertNull($d->db());
	}
	
	public function testEmptyRequiredDate()
	{
		$d = new PropertyDate('Date');
		$d->required();
		
		$this->assertFalse($d->validate(''));
		$this->assertEquals('Date is required', $d->errors());
		$d->error(null);
		
		$d->set(' ');
		$this->assertFalse($d->validate(''));
		$this->assertEquals('Date is required', $d->errors());
		$d->error(null);
		
		$d->set(0);
		$this->assertFalse($d->validate(''));
		$this->assertEquals('Date is required', $d->errors());
	}
	
	public function testEmptySometimesRequiredDate()
	{
		$d = new PropertyDate('Date');
		$d->required();
		$d->only('apple');
		
		$d->set(0);
		$this->assertTrue($d->validate(''));
		
		$this->assertFalse($d->validate('apple'));
		$this->assertEquals('Date is required', $d->errors());
		
		$d->set(1234567890);
		$this->assertTrue($d->validate('apple'));
	}
	
	public function testInvalidDate()
	{
		$d = new PropertyDate('Date');
		$d->set('2010-04ddfselk');
		$this->assertFalse($d->validate(''));
		$this->assertEquals('Date is not a valid date', $d->errors());
	}
}

?>
