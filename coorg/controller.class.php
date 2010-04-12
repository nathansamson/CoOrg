<?php

class Controller {

	private $_smarty = null;
	private $_renderType = 'html';
	private $_tplPath;
	private $_appPath;
	
	public function init($path, $appPath = 'app/')
	{
		$this->_tplPath = $path;
		$this->_appPath = $appPath;
	}
	
	public function isPost($name)
	{
		$reflectionClass = new ReflectionClass($this);
		$reflectionMethod = $reflectionClass->getMethod($name);
		
		$comment = $reflectionMethod->getDocComment();
		
		$lines = explode("\n", $comment);
		
		foreach ($lines as $line)
		{
			$line = trim($line);
			if (strlen($line) == 0) continue;
			if ($line[0] == '*')
			{
				$line = trim(substr($line, 1));
				if ($line == '@post')
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function notFound($request = null, $referer = null, $exception = null)
	{
		$this->exception = $exception;
		$this->referer = $referer;
		$this->request = $request;
		
		Header::setErrorCode('404 Not Found');
		$this->render('notfound', true);
	}
	
	public function systemError($request, $referer, $exception)
	{
		$this->exception = $exception;
		$this->referer = $referer;
		$this->request = $request;
		
		Header::setErrorCode('500 Internal Server Error');
		$this->render('systemerror', true);
	}
	
	public function createURL($request)
	{
		$params = func_get_args();
		return '/~nathan/coorg-ng/'.implode('/', $params);
	}
	
	final public function done()
	{
		$this->smarty()->saveState();
	}
	
	protected function notice($msg)
	{
		$this->smarty()->notice($msg);
	}
	
	protected function error($msg)
	{
		$this->smarty()->error($msg);
	}
	
	protected function redirect($to)
	{
		$args = func_get_args();
		$to = implode('/', $args);
		Header::redirect($to);
	}

	protected function render($tpl, $app = false)
	{
		$file = $tpl .'.'. $this->_renderType . '.tpl';
		$fullPath = $app ? $file : $this->_tplPath . '/' .$file; 
		if ($app || file_exists($fullPath))
		{
			$this->smarty()->display('extends:base.html.tpl|'.$fullPath);
			return;
		}
		throw new TemplateNotFoundException($file);
	}

	public function __set($key, $value)
	{
		self::smarty()->assign($key, $value);
	}
	
	private function smarty()
	{
		if ($this->_smarty == null)
		{
			$this->_smarty = new CoOrgSmarty;
			$this->_smarty->addTemplateDir($this->_appPath);
			$this->_smarty->addPluginsDir('lib/smarty/plugins/coorg');
			
			//TODO: Use anonymous functions/closures (only available in PHP 5.3)
			$this->_smarty->_coorg_createURL = array($this, 'createURL');
		}
		return $this->_smarty;
	}
}

class TemplateNotFoundException extends Exception
{
	public function __construct($name)
	{
		parent::__construct("Template $name not found");
	}
}

?>
