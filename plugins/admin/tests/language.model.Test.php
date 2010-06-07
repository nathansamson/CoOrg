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

class LanguageTests extends CoOrgModelTest
{
	const dataset = 'admin.dataset.xml';
	
	public function testCreate()
	{
		$l = new Language;
		$l->language = 'fr';
		$l->name = 'Français';
		$l->save();
		
		$l = Language::get('fr');
		$this->assertEquals('fr', $l->language);
		$this->assertEquals('Français', $l->name);
	}
	
	public function testCreateNoName()
	{
		$l = new Language;
		$l->language = 'fr';
		try
		{
			$l->save();
			$this->fail('Should throw exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Language name is required', $l->name_error);
		}
	}
	
	public function testCreateNotUnique()
	{
		$l = new Language;
		$l->language = 'nl';
		$l->name = 'Dutch';
		try
		{
			$l->save();
			$this->fail('Should throw exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Language code is used', $l->language_error);
		}
	}
	
	public function testDelete()
	{
		$l = Language::get('nl');
		$this->assertNotNull($l);
		$l->delete();
		
		$this->assertNull(Language::get('nl'));
	}
}

?>
