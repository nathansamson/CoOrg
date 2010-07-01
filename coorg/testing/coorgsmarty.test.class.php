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

include_once 'lib/smarty/Smarty.class.php';

class CoOrgSmarty extends Smarty implements ICoOrgSmarty
{
	public static $vars = array();
	public static $renderedOutput;
	public static $renderedTemplate;
	public static $notices = array();
	public static $errors = array();
	public static $stylesheets = array();

	public static function clearAll()
	{
		self::$renderedOutput = null;
		self::$vars = array();
		self::$notices = array();
		self::$errors = array();
		self::$renderedTemplate = null;
	}

	public function notice($notice)
	{
		self::$notices[] = $notice;
	}
	
	public function error($error)
	{
		self::$errors[] = $error;
	}

	public function assign($key, $value, $save = true)
	{
		if ($save) self::$vars[$key] = $value;
		parent::assign($key, $value);
	}
	
	public function clearAssign($key)
	{
		unset(self::$vars[$key]);
		parent::clearAssign($key);
	}
	
	public function display($tpl)
	{
		self::$renderedOutput = parent::fetch($tpl);
		self::$renderedTemplate = $tpl;
	}
	
	public function render($tpl)
	{
		return parent::fetch($tpl);
	}
	
	public function saveState()
	{
	}

	public function fakeRender($tpl)
	{
		self::$renderedTemplate = $tpl;
	}
	
	public function stylesheet($style)
	{
		$this->stylesheets[] = $style;
	}
}


?>
