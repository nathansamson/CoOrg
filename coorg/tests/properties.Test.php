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

class SomeEnumProperty extends PropertyEnum
{
	public function __construct($name)
	{
		parent::__construct($name, array('A', 'B', 'C', 'D'));
	}
}

class PropertiesTest extends PHPUnit_Framework_TestCase
{
	public function testString()
	{
		$s = new PropertyString('Name', 10);
		$s->set('azerty');
		$s->postsave();
		
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
		$s->postsave();
		
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
		$i->postsave();
		
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
		
		$i = new PropertyInteger('..');
		$i->set('0');
		$this->assertTrue($i->changed());
		$i->set(0);
		$this->assertTrue($i->changed());
		$i->set(null);
		$this->assertFalse($i->changed());
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
		$d->postsave();
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
	
	
	public function testDateTime()
	{
		$d = new PropertyDateTime('Date');
		
		$d->set('2010-04-13 18:35');
		$this->assertEquals(1271176500, $d->get());
		$this->assertEquals('2010-04-13 18:35:00', $d->db());
		$this->assertNull($d->old());
		$d->postsave();
		$this->assertEquals('2010-04-13 18:35:00', $d->old());
		$this->assertTrue($d->validate(''));
		
		$d->set(1271109610);
		$this->assertTrue($d->changed());
		$this->assertEquals(1271109610, $d->get());
		$this->assertTrue($d->validate(''));
		
		$d->set('2010/03/13 18:35:24');
		$this->assertTrue($d->changed());
		$this->assertEquals(1268501724, $d->get());
		$this->assertEquals('2010-03-13 18:35:24', $d->db());
		$this->assertTrue($d->validate(''));
		
		$d = new PropertyDateTime('Date');
		$d->set('1-1-1 1:1:1');
		$this->assertTrue($d->validate(''));
		
		$d->set('1960-11-03 12:22:21');
		$this->assertEquals('1960-11-03 12:22:21', $d->db());
		$this->assertTrue($d->validate(''));
	}
	
	public function testEmptyTime()
	{
		$d = new PropertyDateTime('Date');
		$this->assertNull($d->get());
		$this->assertNull($d->db());
		$d->set(' ');
		$this->assertNull($d->get());
		$this->assertNull($d->db());
		$d->set(0);
		$this->assertNull($d->get());
		$this->assertNull($d->db());
	}
	
	public function testEmptyRequiredDateTime()
	{
		$d = new PropertyDateTime('Date');
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
	
	public function testEmptySometimesRequiredDateTime()
	{
		$d = new PropertyDateTime('Date');
		$d->required();
		$d->only('apple');
		
		$d->set(0);
		$this->assertTrue($d->validate(''));
		
		$this->assertFalse($d->validate('apple'));
		$this->assertEquals('Date is required', $d->errors());
		
		$d->set(1234567890);
		$this->assertTrue($d->validate('apple'));
	}
	
	public function testInvalidDateTime()
	{
		$d = new PropertyDateTime('Date');
		$d->set('2010-04ddfselk 12:35:21');
		$this->assertFalse($d->validate(''));
		$this->assertEquals('Date is not a valid date', $d->errors());
	}
	
	public function testBool()
	{
		$b = new PropertyBool('name');
		$b->set('true');
		$this->assertTrue($b->get());
		$this->assertEquals(1, $b->db());
		$this->assertTrue($b->validate(''));
		
		$b->set('false');
		$this->assertFalse($b->get());
		$this->assertEquals('0', $b->db());
		$this->assertTrue($b->validate(''));
		
		$b->set(true);
		$this->assertEquals('1', $b->db());
		$this->assertTrue($b->get());
		
		$b->set(1);
		$this->assertTrue($b->get());
		$this->assertEquals('1', $b->db());
		
		$b->set(False);
		$this->assertFalse($b->get());
		$this->assertEquals('0', $b->db());
		
		$b->set(0);
		$this->assertFalse($b->get());
		$this->assertEquals('0', $b->db());
		
		$b->set('');
		$this->assertNull($b->get());
		$this->assertNull($b->db());
		$this->assertTrue($b->validate(''));
	}
	
	public function testRequiredBool()
	{
		$b = new PropertyBool('name');
		$b->required();
		
		$this->assertFalse($b->validate(''));
		$this->assertEquals('name is required', $b->errors());
		
		$b->set('true');
		$this->assertTrue($b->validate(''));
	}
	
	public function testRequiredSometimesBool()
	{
		$b = new PropertyBool('name');
		$b->required();
		$b->only('abba');
		
		$this->assertFalse($b->validate('abba'));
		$this->assertEquals('name is required', $b->errors());
		
		$this->assertTrue($b->validate('..'));
		
		$b->set('true');
		$this->assertTrue($b->validate('abba'));
	}
	
	public function testEnum()
	{
		$e = new SomeEnumProperty('X');
		$this->assertTrue($e->validate('.'));
		$this->assertFalse($e->changed());
		$e->set('A');
		$this->assertTrue($e->changed());
		$this->assertEquals('A', $e->get());
		$this->assertEquals('A', $e->db());
		$this->assertTrue($e->validate(''));
		$e->set('K');
		$this->assertFalse($e->validate(''));
		$this->assertEquals('Not a valid choice for X', $e->errors());
	}
	
	public function testRequiredEnum()
	{
		$e = new SomeEnumProperty('X');
		$e->required();
		$this->assertFalse($e->validate('.'));
		$this->assertEquals('X is required', $e->errors());
	}
	
	public function testURL()
	{
		$u = new PropertyURL('URL');
		$this->assertTrue($u->validate(''));
		$this->assertNull($u->get());
		$u->set('google.be/http');
		$this->assertEquals('http://google.be/http', $u->get());
		
		$u->set('google.be');
		$this->assertEquals('http://google.be', $u->get());
		
		$u->set('http://google.be/some/link');
		$this->assertEquals('http://google.be/some/link', $u->get());
		
		$u->set('https://google.be/some/link');
		$this->assertEquals('https://google.be/some/link', $u->get());
	}
	
	public function testFileProperty()
	{
		$f = new PropertyFile('File name', 'some/path');
		$this->assertNull($f->get());
		$this->assertNull($f->db());
		$this->assertNull($f->old());
		$f->set('some/string/to/file.png');
		$this->assertEquals('data/some/path/some/string/to/file.png', $f->get());
		$this->assertEquals('some/string/to/file.png', $f->db());
		$this->assertEquals('png', $f->extension());
		$this->assertNull($f->old());
		$f->postsave();
		
		$this->assertFalse($f->changed());
		$upload = new FileUpload('some/upload/file', 2400, UPLOAD_ERR_OK);
		$f->set($upload);
		$upload->setStoreName('some/string/to/file'); // Same name, but still its changed
		$this->assertTrue($f->changed());
		$this->assertEquals('some/upload/file', $f->get());
		$this->assertEquals('some/upload/file', $f->raw());
		$this->assertEquals('some/string/to/file', $f->db());
		$this->assertEquals('some/string/to/file.png', $f->old());
		$this->assertNull($f->extension());
	}
	
	public function testDeleteOldFileOnSave()
	{
		$f = new PropertyFile('File name', 'some/path');
		$f->set('some/string/to/file');
		$f->postsave();
		
		
		$upload = new FileUpload('some/upload/file', 2400, UPLOAD_ERR_OK);
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		$upload->setStoreName('some/string/to/file'); // Same name, but still its changed
		
		$this->assertFalse(MockCoOrgFile::isDeleted('data/some/path/some/string/to/file'));
		$f->postsave();
		$this->assertTrue(MockCoOrgFile::isDeleted('data/some/path/some/string/to/file'));
		
		
		
		
		$f = new PropertyFile('File name', 'some/path');
		$f->set('some/string/to/otherfile');
		$f->postsave();
		
		
		$upload = new FileUpload('', 0, UPLOAD_ERR_NO_FILE);
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		$upload->setStoreName('some/string/to/file');
		
		$this->assertFalse(MockCoOrgFile::isDeleted('data/some/path/some/string/to/otherfile'));
		$this->assertEquals('some/string/to/otherfile', $f->db());
		$f->postsave();
		$this->assertFalse(MockCoOrgFile::isDeleted('data/some/path/some/string/to/otherfile'));
	}
	
	public function testRequiredFile()
	{
		$f = new PropertyFile('File name', 'some/path');
		$f->required();
		
		$upload = new FileUpload('', 0, UPLOAD_ERR_NO_FILE);
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('You have to upload a file', $f->errors());
		$f->error(null);
		
		
		$upload = new FileUpload('', 0, UPLOAD_ERR_NO_FILE, 'some/session/file');
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		$this->assertEquals('data/.session/some/session/file', $f->get());
	}
	
	public function testErrorInFile()
	{
		$f = new PropertyFile('File name', 'some/path');
		$this->assertTrue($f->validate(''));
		
		$upload = new FileUpload('some/new/file', 500, UPLOAD_ERR_PARTIAL, 'some/older/session/file.ext');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('The file transfer was not complete, please try again', $f->errors());
		$this->assertEquals('data/.session/some/older/session/file.ext', $f->get());
		$this->assertEquals('ext', $f->extension());
		$f->error(null);
		
		
		$upload = new FileUpload('some/new/file', 500, UPLOAD_ERR_INI_SIZE, 'some/older/session/file');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('The filesize is too large', $f->errors());
		$this->assertEquals('data/.session/some/older/session/file', $f->get());
		$f->error(null);
		
		$upload = new FileUpload('some/new/file', 500, UPLOAD_ERR_CANT_WRITE, 'some/older/session/file');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('The file upload failed, please try again', $f->errors());
		$this->assertEquals('data/.session/some/older/session/file', $f->get());
		$f->error(null);
	}
	
	public function testImageIsValid()
	{
		$f = new PropertyImage('File name', 'some/path');
		$this->assertTrue($f->validate(''));
		
		$upload = new FileUpload('', 500, UPLOAD_ERR_NO_FILE);
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		
		$upload = new FileUpload('./coorg/tests/mocks/noimage.txt', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('This is not a valid image file (only png, jpeg and gif are supported)', $f->errors());
		$this->assertEquals('data/.session/some/older/session/file', $f->get());
		$f->error(null);
		
		$upload = new FileUpload('./coorg/tests/mocks/image150x80.png', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		$this->assertEquals('png', $f->extension());
		
		$upload = new FileUpload('./coorg/tests/mocks/image150x80png.jpg', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertTrue($f->validate(''));
		$this->assertEquals('png', $f->extension()); // The file is a png, but has extension jpg
		
		$f = new PropertyImage('File name', 'some/path');
		$upload = new FileUpload('./coorg/tests/mocks/image.bmp', 500, UPLOAD_ERR_OK);
		$f->set('some/original/file');
		$f->postsave();
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('This is not a valid image file (only png, jpeg and gif are supported)', $f->errors());
		$this->assertEquals('data/some/path/some/original/file', $f->get());
	}
	
	public function testImageMaxResolution()
	{
		$f = new PropertyImage('File name', 'some/path', 200, 50);
		$upload = new FileUpload('./coorg/tests/mocks/image150x80.png', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('The file resolution is too large, maximum is 200 x 50', $f->errors());
		
		$f = new PropertyImage('File name', 'some/path', 100, 100);
		$upload = new FileUpload('./coorg/tests/mocks/image150x80.png', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertFalse($f->validate(''));
		$this->assertEquals('The file resolution is too large, maximum is 100 x 100', $f->errors());
		
		$f = new PropertyImage('File name', 'some/path', 150, 80);
		$upload = new FileUpload('./coorg/tests/mocks/image150x80.png', 500, UPLOAD_ERR_OK, 'some/older/session/file');
		$f->set($upload);
		$this->assertTrue($f->validate(''));
	}
}

?>
