<?php

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
