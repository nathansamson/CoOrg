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

class AclBeforeController extends Controller
{
	private $_allowed = null;
	private $_onlyDenied = true;

	public function in($what, $key)
	{
		if ($this->_allowed !== null) return;
		
		if ($what == 'allow')
		{
			$this->_onlyDenied = false;
			if ($key[0] == ':') // Pseudo key
			{
				if ($key == ':loggedIn')
				{
					if (UserSession::get() != null)
					{
						$this->_allowed = true;
					}
				}
			}
			else
			{
				if ($u = UserSession::get())
				{
					if (Acl::isAllowed(UserSession::get()->username, $key))
					{
						$this->_allowed = true;
					}
				}
			}
		}
		else if ($what == 'deny')
		{
			if ($key[0] == ':') // Pseudo key
			{
				if ($key == ':anonymous')
				{
					if (UserSession::get() == null)
					{
						$this->_allowed = false;
					}
				}
			}
			else
			{
				if (Acl::isAllowed(UserSession::get()->username, $key))
				{
					$this->_allowed = false;
				}
			}
		}
		else if ($what == 'owns')
		{
			if ($this->_allowed !== null) return;
			$this->_onlyDenied = false;
			$this->_allowed = Acl::owns(UserSession::get()->username, $key) ? true : $this->_allowed;
		}
	}
	
	public function out()
	{
		if ($this->_allowed === null)
		{
			$this->_allowed = $this->_onlyDenied;
		}
		if (!$this->_allowed && !UserSession::get())
		{
			$this->error('You should be logged in to view this page');
			$this->redirect = $this->coorgRequest;
			$this->render('login');
			return false;
		}
		else if (!$this->_allowed)
		{
			$this->error('You don\'t have the rights to view this page');
			$this->redirect('/');
			return false;
		}
		return true;
	}
}

?>
