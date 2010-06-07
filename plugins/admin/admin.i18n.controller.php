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

class AdminI18nController extends Controller
{
	private $_l;

	/**
	 * @Acl allow admin-language
	*/
	public function index()
	{
		$this->languages = Language::languages();
		$this->newLanguage = new Language;
		$this->render('i18n/index');
	}
	
	/**
	 * @Acl allow admin-language
	*/
	public function save($language, $name)
	{
		$l = new Language;
		$l->language = $language;
		$l->name = $name;
		try
		{
			$l->save();
			$this->notice(t('Installed "%l"', array('l' => $name)));
			$this->redirect('admin/i18n');
		}
		catch (ValidationException $e)
		{
			$this->languages = Language::languages();
			$this->newLanguage = $l;
			$this->error(t('Did not install "%l"', array('l' => $name)));
			$this->render('i18n/index');
		}
	}
	
	/**
	 * @Acl allow admin-language
	 * @before find $language
	*/
	public function update($language, $name)
	{
		$this->_l->name = $name;
		try
		{
			$this->_l->save();
			
			$this->notice(t('Updated "%l"', array('l' => $name)));
			$this->redirect('admin/i18n');
		}
		catch (ValidationException $e)
		{
			$this->error(t('Did not update "%l"', array('l' => $language)));
		}
	}
	
	/**
	 * @Acl allow admin-language
	 * @before find $language
	*/
	public function delete($language)
	{
		$this->_l->delete();
		$this->notice(t('Deleted "%l"', array('l' => $this->_l->name)));
		$this->redirect('admin/i18n');
	}
	
	protected function find($language)
	{
		$this->_l = Language::get($language);
		if (!$this->_l)
		{
			$this->error(t('Language "%l" not found', array('l' => $language)));
			$this->redirect('admin/i18n');
			return false;
		}
		return true;
	}
}

?>
