<?php

class Controller {

	private $_smarty = null;
	private $_renderType = 'html';
	private $_tplPath;
	private $_appPath;

	protected $post = array();
	
	public function init($path, $appPath = 'app/')
	{
		$this->_tplPath = $path;
		$this->_appPath = $appPath;
	}
	
	public function isPost($name)
	{
		return in_array($name, $this->post);
	}
	
	public function notFound($request, $referer, $exception)
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
