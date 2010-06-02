<?php

class MockAclBeforeTestController extends MockController
{

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
}

class AclBeforeTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/user.dataset.xml';
	}

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
		CoOrg::process('MockAclBeforeTest/denyAnonymous');

		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
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

	private function login($user = 'dvorak')
	{
		$s = new UserSession($user, $user);
		$s->save();
	}
}

?>
