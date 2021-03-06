<?php

class MockAclBeforeTestController extends MockController
{
	protected $_something;

	/**
	 * @Acl allow :loggedIn
	*/
	public function allowOnlyLoggedIn()
	{
		$this->render('fake');
	}

	/**
	 * @Acl deny :anonymous
	*/
	public function denyAnonymous()
	{
		$this->render('fake');
	}

	/**
	 * @Acl allow dvorakAllowed
	*/
	public function rule()
	{
		$this->render('fake');
	}

	/**
	 * @Acl allow dvorakAllowed
	 * @Acl deny blocked
	 * @Acl allow someOtherUsers
	*/
	public function mix()
	{
		$this->render('fake');
	}

	/**
	 * @Acl allow dvorakAllowed
	 * @Acl allow someOtherUsers
	 * @Acl deny blocked
	*/
	public function othermix()
	{
		$this->render('fake');
	}
	
	/**
	 * @before findSomething $owner
	 * @Acl owns $:_something
	*/
	public function testOwns($owner)
	{
		$this->render('fake');
	}
	
	protected function findSomething($owner)
	{
		$this->_something = new MyMoreSpecificModel;
		$this->_something->username = $owner;
		return true;
	}
}

class AclBeforeTest extends CoOrgControllerTest
{
	const dataset = 'user.dataset.xml';

	public function testAllowOnlyLoggedInOk()
	{
		$this->login();
		CoOrg::process('MockAclBeforeTest/allowOnlyLoggedIn');

		$this->assertRendered('fake');
	}

	public function testAllowLoggedInFailure()
	{
		CoOrg::process('MockAclBeforeTest/allowOnlyLoggedIn');

		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}

	public function testDenyAnonymous()
	{
		CoOrg::process('nl/MockAclBeforeTest/denyAnonymous/withsomestrange$2fcharacters');

		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
		$this->assertVarSet('redirect');
		$this->assertVarIs('redirect', 'MockAclBeforeTest/denyAnonymous/withsomestrange$2fcharacters');
	}

	public function testDenyAnonymousLoggedIn()
	{
		// A special rule comes into play. If only deny rules are found, and none of them applied to the user allow the user
		$this->login();
		CoOrg::process('MockAclBeforeTest/denyAnonymous');

		$this->assertRendered('fake');
	}

	public function testRuleAllowed()
	{
		$this->login();
		CoOrg::process('MockAclBeforeTest/rule');

		$this->assertRendered('fake');
	}

	public function testRuleNotAllowedLoggedIn()
	{
		$this->login('azerty');
		CoOrg::process('MockAclBeforeTest/rule');

		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}

	public function testRuleNotAllowedLoggedOut()
	{
		CoOrg::process('MockAclBeforeTest/rule');

		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}

	public function testMixOfAllowedAndDeniesAllowed()
	{
		$this->login();
		CoOrg::process('MockAclBeforeTest/mix');

		$this->assertRendered('fake');
	}

	public function testMixOfAllowedAndDeniesDenied()
	{
		$this->login('azerty');
		CoOrg::process('MockAclBeforeTest/mix');

		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}

	public function testMixOfAllowedAndDeniesOtherMix()
	{
		$this->login('azerty');
		CoOrg::process('MockAclBeforeTest/othermix');

		$this->assertRendered('fake');
	}
	
	public function testOwnsOk()
	{
		$this->login('azerty');
		CoOrg::process('MockAclBeforeTest/testOwns/azerty');

		$this->assertRendered('fake');
	}
	
	public function testOwnsDenied()
	{
		$this->login('azerty');
		CoOrg::process('MockAclBeforeTest/testOwns/qwerty');

		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}

	private function login($user = 'dvorak')
	{
		$s = new UserSession($user, $user);
		$s->save();
	}
}

?>
