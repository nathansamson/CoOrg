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

/**
 * @property primary autoincrement; ID Integer('ID');
 * @property name String(t('Name'), 32); required
 * @property email Email(t('Email')); required
 * @property website URL(t('Website'));
 * @property IP String('IP', 39); required
*/
class AnonProfile extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function avatar()
	{
		return 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?d=identicon';
	}

	public function get($ID)
	{
		$q = DB::prepare('Select * FROM AnonProfile WHERE ID=:ID');
		$q->execute(array(':ID' => $ID));
		
		return self::fetch($q->fetch(), 'AnonProfile');
	}
}

?>
