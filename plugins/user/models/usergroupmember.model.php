<?php

/**
 * @property primary; groupID String('group', 26); required
 * @property primary; userID String('user', 24); required
*/
class UserGroupMember extends DBModel
{
	public function __construct($userID = null, $groupID = null)
	{
		parent::__construct();
		$this->userID = $userID;
		$this->groupID = $groupID;
	}
	
	public static function get($user, $group)
	{
		$q = DB::prepare('SELECT * FROM UserGroupMember WHERE 
		                        groupID=:group AND userID=:user');
		
		$q->execute(array('group' => $group, 'user' => $user));
		
		$r = $q->fetch();
		if ($r != array())
		{
			return self::fetch($r, 'UserGroupMember');
		}
		else
		{
			return false;
		}
	}
	
	public static function getGroupsWithUser($user)
	{
		$q = DB::prepare('SELECT UserGroup.* FROM UserGroupMember
		                    JOIN UserGroup ON groupID=name
		                    WHERE userID=:user
		                    ORDER BY groupID');
		
		$q->execute(array('user' => $user));
		
		$groups = array();
		foreach ($q->fetchAll() as $r)
		{
			$groups[] = self::fetch($r, 'UserGroup');
		}
		return $groups;
	}
	
	public static function getAllInGroup($group)
	{
		$q = DB::prepare('SELECT * FROM UserGroupMember WHERE 
		                        groupID=:group');
		
		$q->execute(array('group' => $group));
		
		$members = array();
		foreach ($q->fetchAll() as $row)
		{
			$members[] = self::fetch($row, 'UserGroupMember');
		}
		return $members;
	}
	
	protected function validate($for)
	{
		parent::validate($for);
		
		if (self::get($this->userID, $this->groupID))
		{
			throw new Exception('User is already member of group');
		}
	}
}

?>
