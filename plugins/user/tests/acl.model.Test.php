<?php

class MySpecificModel
{
	public $username;
}

class MyMoreSpecificModel extends MySpecificModel
{
}

class AclTest extends CoOrgModelTest
{
	const dataset = 'user.dataset.xml';

	public function testBasics()
	{
		$this->assertTrue(Acl::isAllowed('dvorak', 'dvorakAllowed'));
		$this->assertFalse(Acl::isAllowed('azerty', 'dvorakAllowed'));
		
		$this->assertFalse(Acl::isAllowed('dvorak', 'newlyGranted'));
		$dvorak = User::getUserByName('dvorak');
		$dvorak->grant('newlyGranted');
		$this->assertTrue(Acl::isAllowed('dvorak', 'newlyGranted'));
		
		$dvorak->revoke('dvorakAllowed');
		$this->assertFalse(Acl::isAllowed('dvorak', 'dvorakAllowed'));
		
		
		$user = new User('sdldlkd', 'email@email.com');
		$user->password = 'sdldlkd';
		$user->passwordConfirmation = 'sdldlkd';
		$user->save();
		
		$this->assertFalse(Acl::isAllowed('sdldlkd', 'newlyGranted'));
		$user->grant('newlyGranted');
		$this->assertTrue(Acl::isAllowed('sdldlkd', 'newlyGranted'));
		$user->revoke('newlyGranted');
		$this->assertFalse(Acl::isAllowed('sdldlkd', 'newlyGranted'));
	}
	
	public function testGroup()
	{
		$group = new UserGroup('Webmasters');
		$group->save();
		$group->grant('someGrant');
		
		$this->assertFalse(Acl::isAllowed('dvorak', 'someGrant'));
		$group->add('dvorak');
		$this->assertTrue(Acl::isAllowed('dvorak', 'someGrant'));
		
		$group = new UserGroup('BadGroup');
		$group->save();
		$group->revoke('someGrant');
		$group->add('dvorak');
		
		$this->assertTrue(Acl::isAllowed('dvorak', 'someGrant')); // Even if dvorak is in a group that has no right he is allowed
	}
	
	public function testOwns()
	{
		$o = new MyMoreSpecificModel;
		$o->username = 'me';
		$this->assertTrue(Acl::owns('me', $o));
		$this->assertFalse(Acl::owns('not-me', $o));
		
		$this->assertFalse(Acl::owns('not-me', new UserSession('a', 'a'))); // Class is not registered
	}
}

?>
