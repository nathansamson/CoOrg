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

class Controller {

	private $_smarty = null;
	private $_renderType = 'html';
	private $_tplPath;
	private $_appPath;
	private $_contentTypes;
	
	public function init($path, $appPath = 'app/', $renderType = 'html')
	{
		$this->_tplPath = $path;
		$this->_appPath = $appPath;
		$this->_renderType = $renderType;
		$this->_contentTypes = array('atom' => 'application/xml+atom');
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
		return CoOrg::createURL($params);
	}
	
	final public function beforeFilters($action, $filters, $parameters)
	{
		$reflectionClass = new ReflectionClass($this);
		$reflectionMethod = $reflectionClass->getMethod($action);
		$pNamesToValue = array();
		foreach ($reflectionMethod->getParameters() as $i=>$param)
		{
			if ($i < count($parameters))
			{
				$pNamesToValue[$param->getName()] = $parameters[$i];
			}
			else
			{
				$pNamesToValue[$param->getName()] = $param->getDefaultValue();
			}
		}
		
		$comment = $reflectionMethod->getDocComment();
		$loaded = array();
		$lines = explode("\n", $comment);
	
		foreach ($lines as $line)
		{
			$line = trim($line);
			if (strlen($line) == 0) continue;
			if ($line[0] == '*')
			{
				$line = trim(substr($line, 1));
				$parts = explode(' ', $line);
				$filterName = trim(array_shift($parts));
				if ($filterName[0] == '@')
				{
					$filter = substr($filterName, 1);
					if ($filter == 'post')
					{
						continue;
					}
					else if ($filter == 'before')
					{
						$params = array();
						for ($i = 1; $i < count($parts); $i++)
						{
							$p = $parts[$i];
							if ($p[0] == '$')
							{
								$params[] = $pNamesToValue[substr($p, 1)];
							}
							else
							{
								$params[] = $p;
							}
						}
						if (!call_user_func_array(array($this, $parts[0]), $params))
						{
							return false;
						}
					}

					if (array_key_exists($filter, $loaded))
					{
						$fClass = $loaded[$filter];
					}
					else if (array_key_exists($filter, $filters))
					{
						include_once $filters[$filter];
						$fClassName = $filter.'BeforeController';
						$fClass = new $fClassName;
						$fClass->init(dirname($filters[$filter]).'/views/', $this->_appPath);
						$fClass->_smarty = $this->smarty();
						$loaded[$filter] = $fClass;
					}
					else
					{
						continue;
					}
					$params = array();
					foreach ($parts as $part)
					{
						if ($part[0] == '$')
						{
							$params[] = $pNamesToValue[substr($part, 1)];
						}
						else
						{
							$params[] = $part;
						}
					}
					call_user_func_array(array($fClass, 'in'), $params);
				}
			}
		}
		foreach ($loaded as $l)
		{
			if (!$l->out())
			{
				return false;
			}
		}
		return true;
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

	protected function render($tpl, $app = false, $baseFile = 'base')
	{
		if (array_key_exists($this->_renderType, $this->_contentTypes))
		{
			Header::setContentType($this->_contentTypes[$this->_renderType]);
		}
		$file = $tpl .'.'. $this->_renderType . '.tpl';
		$fullPath = $app ? $file : $this->_tplPath . '/' .$file; 
		if ($app || file_exists($fullPath))
		{
			if ($baseFile != null)
			{
				$baseFile = $baseFile.'.'.$this->_renderType.'.tpl';
				$this->smarty()->display('extends:base.html.tpl|'.$fullPath);
			}
			else
			{
				$this->smarty()->display($fullPath);
			}
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
