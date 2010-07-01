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
 * @property primary; username String(t('Username'), 24); required
 * @property firstName String(t('First Name'), 20);
 * @property lastName String(t('Last Name'), 20);
 * @property birthDate Date(t('Birth date'));
 * @property gender Gender(t('Gender'));
 * @property website URL(t('Website'));
 * @property intrests String(t('Intrests'), 1024);
 * @property biography String(t('Biography'));
*/
class UserProfile extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get($username)
	{
		$q = DB::prepare('SELECT * FROM UserProfile WHERE username=:username');
		$q->execute(array(':username' => $username));
		
		return self::fetch($q->fetch(), 'UserProfile');
	}
}
