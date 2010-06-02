<?php

class UserGroupTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/user.dataset.xml';
	}
	
	public function testCreateGroup()
	{
		$group = new UserGroup('Webmasters');
		$group->save();
		
		$group = UserGroup::getGroupByName('Webmasters');
		$this->assertNotNull($group);
		$this->assertEquals('Webmasters', $group->name);
		$this->assertFalse($group->system);
		
		$group = new UserGroup('System');
		$group->system = true;
		$group->save();
		
		$group = UserGroup::getGroupByName('System');
		$this->assertNotNull($group);
		$this->assertEquals('System', $group->name);
		$this->assertTrue($group->system);
	}
	
	public function testCreateGroupTwice()
	{
		$group = new UserGroup('Webmasters');
		$group->save();
		
		$group = new UserGroup('Webmasters');
		try
		{
			$group->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Group name is already used', $group->name_error);
		}
	}
	
	public function testGetNonExistingGroup()
	{
		$group = UserGroup::getGroupByName('...');
		$this->assertNull($group);
	}
	
	public function testGroupMembers()
	{
		$group = new UserGroup('SomeName');
		$group->save();
		
		$this->assertEquals(array(), $group->members());
		
		$group->add('dvorak');	
		$members = $group->members();
		$this->assertEquals(1, count($members));
		$this->assertEquals('dvorak', $members[0]->userID);
		
		$group->add('azerty');
		$members = $group->members();
		$this->assertEquals(2, count($members));
		$this->assertEquals('azerty', $members[0]->userID);
		$this->assertEquals('dvorak', $members[1]->userID);
		
		$group->remove('dvorak');
		$members = $group->members();
		$this->assertEquals(1, count($members));
		$this->assertEquals('azerty', $members[0]->userID);
	}
	
	public function testGroups()
	{
		$group = new UserGroup('SomeName');
		$group->save();
		$group->add('dvorak');
		
		$group = new UserGroup('AnotherName');
		$group->save();
		$group->add('dvorak');
	
		$user = User::getUserByName('dvorak');
		$this->assertNotNull($user);
		$groups = $user->groups();
		$this->assertEquals(2, count($groups));
		$this->assertEquals('AnotherName', $groups[0]->name);
		$this->assertEquals('SomeName', $groups[1]->name);
	}
	
	public function testAddMemberTwice()
	{
		$group = new UserGroup('SomeName');
		$group->save();
		$group->add('dvorak');
		
		try
		{
			$group->add('dvorak');
			$this->fail('Expected exception');
		}
		catch (Exception $e)
		{
			$this->assertEquals('User is already member of group', $e->getMessage());
		}
	}
}

?>
