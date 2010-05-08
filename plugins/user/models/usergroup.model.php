<?php

/**
 * @property primary; name String(t('Name'), 26); required
 * @property system Bool('System group'); required
*/
class UserGroup extends DBModel
{
	public function __construct($name)
	{
		parent::__construct();
		$this->name = $name;
	}

	public function beforeInsert()
	{
		if ($this->system === null)
		{
			$this->system = false;
		}
	}
	
	public function add($user)
	{
		$member = new UserGroupMember($user, $this->name);
		$member->save();
	}
	
	public function remove($user)
	{
		$member = UserGroupMember::get($user, $this->name);
		$member->delete();
	}
	
	public function members()
	{
		return UserGroupMember::getAllInGroup($this->name);
	}
	
	public function grant($key)
	{
		$acl = Acl::set($this->name, $key, true);
	}
	
	public function revoke($key)
	{
		$acl = Acl::set($this->name, $key, false);
	}
	
	public static function from($row)
	{
		$group = new UserGroup($row['name']);
		$group->system = $row['system'];
		$group->setSaved();
		return $group;
	}
	
	public static function getGroupByName($name)
	{
		$q = DB::prepare('SELECT * FROM UserGroup WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		$row = $q->fetch(PDO::FETCH_ASSOC);
		if ($row != false)
		{
			return self::from($row);
		}
		else
		{
			return null;
		}
	}
	
	protected function validate($for)
	{
		if ($for == 'insert' && self::getGroupByName($this->name) != null)
		{
			$this->name_error = 'Group name is already used';
			throw new ValidationException($this);
		}
	}
}

?>
