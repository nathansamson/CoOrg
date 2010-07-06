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

	private $_errors = array();
	private $_notices = array();
	
	public static $_static_array = null;
	
	// public because handler must access it
	public $_stylesheets = array();

	public function __construct()
	{
		parent::__construct();
		if (array_key_exists('coorg_errors', $_COOKIE))
		{
			foreach ($_COOKIE['coorg_errors'] as $k=>$error)
			{
				$this->_errors[] = $error;
				setcookie('coorg_errors['.$k.']', null, time()-36000, '/');
			}
		}
		
		if (array_key_exists('coorg_notices', $_COOKIE))
		{
			foreach ($_COOKIE['coorg_notices'] as $k=>$notice)
			{
				$this->_notices[] = $notice;
				setcookie('coorg_notices['.$k.']', null, time()-36000, '/');
			}
		}
	}

	public function notice($notice)
	{
		$this->_notices[] = $notice;
	}
	
	public function error($error)
	{
		$this->_errors[] = $error;
	}

	public function display($tpl)
	{
		$this->assign('notices', $this->_notices);
		$this->assign('errors', $this->_errors);
		$this->register->outputFilter('add_stylesheets');
		parent::display($tpl);
		$this->_notices = array();
		$this->_errors = array();
	}
	
	public function assign($key, $value)
	{
		parent::assign($key, $value);
	}
	
	public function saveState()
	{
		foreach ($this->_notices as $i=>$notice)
		{
			setcookie('coorg_notices['.$i.']', $notice, 0, '/');
		}
		
		foreach ($this->_errors as $i=>$error)
		{
			setcookie('coorg_errors['.$i.']', $error, 0, '/');
		}
	}
	
	public function stylesheet($style)
	{
		if (self::$_static_array)
		{
			foreach (self::$_static_array as $s)
			{
				$this->_stylesheets[] = $s;
			}
			self::$_static_array = null;
		}
		else
		{
			$this->_stylesheets[] = $style;
		}
	}
}

function add_stylesheets($output, &$smarty)
{
	$r = '';
	
	$s = array_unique($smarty->_stylesheets);
	foreach ($s as $stylesheet)
	{
		$r .= '<link rel="stylesheet" href="'.$stylesheet.'" />'."\n";
	}
	
	return preg_replace('/^[[:space:]]*<!-- %%\$\$EXTRASTYLESHEETSCOMEHERE\$\$%% -->[[:space:]]*$/m', $r, $output);
}

?>
