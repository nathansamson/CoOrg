<?php

/**
 * @property primary; groupID String('Group', 26); required
 * @property primary; keyID String('Key', 32); required
 * @property allowed Bool('allowed'); required
*/
class Acl extends DBModel
{
	protected function __construct($group, $key, $allowed)
	{
		parent::__construct();
		$this->groupID = $group;
		$this->keyID = $key;
		$this->allowed = $allowed;
	}
	
	public static function set($group, $key, $allowed)
	{
		if ($acl = self::get($group, $key))
		{
			$acl->allowed = $allowed;
		}
		else
		{
			$acl = new Acl($group, $key, $allowed);
		}
		$acl->save();
	}

	public static function isAllowed($user, $key)
	{
		$q = DB::prepare('SELECT * FROM Acl WHERE 
		                   groupID IN (SELECT groupID FROM UserGroupMember
		                                      WHERE userID=:user)
		                   AND keyID=:key');
		
		$q->execute(array('key' => $key, 'user' => $user));
		
		$allowed = null;
		foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row)
		{	
			if ($row['allowed'] == 1)
			{
				return true;
			}
			else if ($row['allowed'] == 0)
			{
				$allowed = false;
			}
		}
		if ($allowed === null)
		{
			// Find default, for now the default is false
			$allowed = false;
		}
		return $allowed;
	}
	
	private function get($group, $key)
	{
		$q = DB::prepare('SELECT * FROM Acl WHERE
		                     groupID=:group AND keyID=:key');
		$q->execute(array('group' => $group, 'key'=>$key));
		
		if ($row = $q->fetch(PDO::FETCH_ASSOC))
		{
			$a = new Acl($row['groupID'], $row['keyID'], $row['allowed']);
			$a->setSaved();
			return $a;
		}
		else
		{
			return null;
		}
	}
}

?>
