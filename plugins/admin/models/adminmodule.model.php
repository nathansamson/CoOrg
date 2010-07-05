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

class AdminModule
{
	private $_tabs = array();
	
	public function url($user)
	{
		$tabs = $this->tabs($user, null);
		return $tabs[0]->url;
	}

	public function isAllowed($user)
	{
		foreach ($this->_tabs as $tabClass)
		{
			$tab = new $tabClass;
			if ($tab->isAllowed($user))
			{
				return true;
			}
		}
		return false;
	}

	public function tabs($user, $current)
	{
		$tabs = array();
		foreach ($this->_tabs as $tabName)
		{
			$tab = new $tabName;
			$tab->current = $current == $tabName;
			if ($tab->isAllowed($user))
			{
				$tabs[] = $tab;
			}
		}
		usort($tabs, array('AdminModule', 'cmpTab'));
		return $tabs;
	}

	public function addTab($tabName)
	{
		$this->_tabs[] = $tabName;
	}
	
	public static function cmpTab($m1, $m2)
	{
		if ($m1->priority < $m2->priority)
		{
			return -1;
		}
		elseif ($m1->priority == $m2->priority)
		{
			if ($m1->name < $m2->name)
			{
				return -1;
			}
			elseif ($m1->name == $m2->name)
			{
				return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 1;
		}
	}
}

?>
