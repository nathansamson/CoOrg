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

class UserProfileController extends Controller
{
	public function show($username)
	{
		$profile = UserProfile::get($username);
		if ($profile)
		{
			$this->profile = $profile;
			$this->render('profile/show');
		}
		else
		{
			$this->error(t('Profile not found'));
			$this->notfound();
		}
	}
	
	/**
	 * @Acl allow :loggedIn
	*/
	public function edit()
	{
		$this->profile = UserSession::get()->user()->profile;
		$this->render('profile/edit');
	}
	
	/**
	 * @Acl allow :loggedIn
	*/
	public function update($firstName, $lastName, $birthDate, $gender,
	                       $intrests, $biography, $website)
	{
		$profile = UserSession::get()->user()->profile;
		$profile->firstName = $firstName;
		$profile->lastName = $lastName;
		$profile->birthDate = $birthDate;
		$profile->gender = $gender;
		$profile->intrests = $intrests;
		$profile->biography = $biography;
		$profile->website = $website;
		$profile->save();
		
		$this->notice(t('Profile updated'));
		$this->redirect('user/profile/show', $profile->username);
	}
}
