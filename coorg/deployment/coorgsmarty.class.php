<?php

include_once 'lib/smarty/Smarty.class.php';

class CoOrgSmarty extends Smarty implements ICoOrgSmarty
{

	private $_errors = array();
	private $_notices = array();

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
}

?>
