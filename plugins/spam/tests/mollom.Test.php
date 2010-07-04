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

class MollomTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		class_exists('MollomCaptcha'); // Load MollomCaptcha which will include correct Mollom
		Mollom::clearAll();
		CoOrg::config()->set('mollom/public', 'valid-pub-key');
		CoOrg::config()->set('mollom/private', 'valid-priv-key');
	}

	public function testCheckCaptcha()
	{
		$captcha = MollomCaptcha::create();
		$this->assertEquals('image', $captcha->type);
		$this->assertEquals('mollom.com/new-captcha', $captcha->url);
		
		$this->assertEquals('new-sessionid', Session::get('mollom/sessionid'));
		Mollom::clear(); // Clear serverlist etc
		
		// Probably a post follows, client request new captcha
		
		$captcha = MollomCaptcha::check('invalid');
		$this->assertNotNull($captcha);
		$this->assertEquals('image', $captcha->type);
		$this->assertEquals('mollom.com/invalid-captcha', $captcha->url);
		
		// Third try, client requests refresh
		Mollom::clear(); // Clear serverlist etc
		$captcha = MollomCaptcha::refresh();
		$this->assertNotNull($captcha);
		$this->assertEquals('image', $captcha->type);
		$this->assertEquals('mollom.com/refresh-captcha', $captcha->url);
		
		// Fourh try, client requests audio file
		Mollom::clear(); // Clear serverlist etc
		$captcha = MollomCaptcha::refresh('audio');
		$this->assertNotNull($captcha);
		$this->assertEquals('audio', $captcha->type);
		$this->assertEquals('mollom.com/refresh-audio-captcha', $captcha->url);
		
		// Fifth try, client refreshes
		Mollom::clear(); // Clear serverlist etc
		$captcha = MollomCaptcha::refresh();
		$this->assertNotNull($captcha);
		$this->assertEquals('audio', $captcha->type);
		$this->assertEquals('mollom.com/refresh-audio-captcha', $captcha->url);
		
		// Last and valid try
		Mollom::clear(); // Clear serverlist etc
		$captcha = MollomCaptcha::check('valid');
		$this->assertNull($captcha);
		
		$this->assertFalse(Session::has('mollom/sessionid'));
	}
	
	public function testCreateInvalidKeys()
	{
		CoOrg::config()->set('mollom/public', 'novalid-pub-key');
		CoOrg::config()->set('mollom/private', 'novalid-priv-key');
		
		$captcha = MollomCaptcha::create();
		$this->assertNotNull($captcha);
		$this->assertTrue($captcha instanceof MollomInvalidConfigCaptcha);
	}
	
	public function testCreateNoServerList()
	{
		CoOrg::config()->set('mollom/serverlist', array());
		
		$captcha = MollomCaptcha::create();
		$this->assertEquals('image', $captcha->type);
		$this->assertEquals('mollom.com/new-captcha', $captcha->url);
		
		$this->assertEquals(array('retrieved-list'), CoOrg::config()->get('mollom/serverlist'));
	}
	
	public function testCreateOutdatedServerList()
	{
		CoOrg::config()->set('mollom/serverlist', array('outdated'));
		
		$captcha = MollomCaptcha::create();
		$this->assertEquals('image', $captcha->type);
		
		$this->assertEquals(array('retrieved-list'), CoOrg::config()->get('mollom/serverlist'));
	}
	
	public function testCheckCaptchaInvalidKeys()
	{
		CoOrg::config()->set('mollom/public', 'novalid-pub-key');
		CoOrg::config()->set('mollom/private', 'novalid-priv-key');
		
		Session::set('mollom/sessionid', 'some-session');
		
		$captcha = MollomCaptcha::check('valid');
		$this->assertNotNull($captcha);
		$this->assertTrue($captcha instanceof MollomInvalidConfigCaptcha);
	}
	
	public function testCheckContent()
	{
		$mollomMessage = new MollomMessage;
		$mollomMessage->authorEmail = 'someemail@email.com';
		$mollomMessage->body = 'SPAM BODY';
		$this->assertEquals(PropertySpamStatus::SPAM, $mollomMessage->check());
		
		$this->assertTrue(Session::has('mollom/sessionid'));
		Session::delete('mollom/sessionid');
		
		$mollomMessage = new MollomMessage;
		$mollomMessage->authorEmail = 'someemail@email.com';
		$mollomMessage->body = 'GOOD BODY';
		$this->assertEquals(PropertySpamStatus::OK, $mollomMessage->check());
		$this->assertTrue(Session::has('mollom/sessionid'));
	}
	
	public function testCheckContentOutDatedServerList()
	{
		CoOrg::config()->set('mollom/serverlist', array('outdated'));
		
		$mollomMessage = new MollomMessage;
		$mollomMessage->authorEmail = 'someemail@email.com';
		$mollomMessage->body = 'SPAM BODY';
		$this->assertEquals(PropertySpamStatus::SPAM, $mollomMessage->check());
		
		$this->assertTrue(Session::has('mollom/sessionid'));
		Session::delete('mollom/sessionid');
		Mollom::clear();
		CoOrg::config()->set('mollom/serverlist', array('outdated'));
		
		$mollomMessage = new MollomMessage;
		$mollomMessage->authorEmail = 'someemail@email.com';
		$mollomMessage->body = 'GOOD BODY';
		$this->assertEquals(PropertySpamStatus::OK, $mollomMessage->check());
		$this->assertTrue(Session::has('mollom/sessionid'));
	}
	
	public function testFeedback()
	{
		$this->assertTrue(MollomMessage::feedback('some-sess-id', 'profanity'));
	}
	
	public function testFeedbackProblem()
	{
		CoOrg::config()->set('mollom/serverlist', array('outdated'));
		
		$this->assertTrue(MollomMessage::feedback('some-sess-id', 'profanity'));
	}
}
?>
