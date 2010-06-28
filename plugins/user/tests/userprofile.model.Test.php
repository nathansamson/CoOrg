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

class UserProfileTest extends CoOrgModelTest
{
	const dataset = 'user.dataset.xml';

	public function testProfile()
	{
		$profile = new UserProfile;
		$profile->user = User::getUserByName('no-profile');
		$profile->gender = PROPERTY_GENDER_MALE;
		$profile->birthDate = '2010-3-21';
		$profile->save();
		
		$user = User::getUserByName('no-profile');
		$userP = $user->profile;
		$this->assertEquals(PROPERTY_GENDER_MALE, $userP->gender);
		$this->assertEquals('2010-03-21', date('Y-m-d', $userP->birthDate));
		$this->assertNull($userP->firstName);
		$this->assertNull($userP->lastName);
		$this->assertNull($userP->intrests);
		$this->assertNull($userP->website);
		$this->assertNull($userP->biography);
		
		$userP->firstName = 'No';
		$userP->lastName = 'Profile';
		$userP->intrests = 'Reading, Programming, Opensource, and more';
		$userP->biography = 'A user without profile (not anymore)';
		$userP->website = 'somesite.in.the.wild/some/link';
		$userP->save();
		
		$profile = UserProfile::get('no-profile');
		$this->assertEquals('No', $userP->firstName);
		$this->assertEquals('Profile', $userP->lastName);
		$this->assertEquals('Reading, Programming, Opensource, and more', $userP->intrests);
		$this->assertEquals('http://somesite.in.the.wild/some/link', $userP->website);
		$this->assertEquals('A user without profile (not anymore)', $userP->biography);
	}
}


?>
