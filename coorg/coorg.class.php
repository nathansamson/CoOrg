<?php

require_once 'coorg/controller.class.php';
require_once 'coorg/config.class.php';

class CoOrg {

	private static $_controllers = array();
	private static $_models = array();
	private static $_site = null;
	private static $_referer = null;
	private static $_appdir;

	public static function init(Config $config, $appdir, $pluginsDir)
	{
		self::loadDir($pluginsDir, $config->get('enabled_plugins'));
		self::loadDir($appdir, null);
		self::$_appdir = $appdir;
		spl_autoload_register(array('CoOrg', 'loadModel'));
	}
	
	public static function clear()
	{
	}

	public static function run()
	{
		require_once 'coorg/democks.class.php';
		$config = new Config('config/config.php');
		CoOrg::init($config, 'app', 'plugins');
		
		if (array_key_exists('HTTP_REFERER', $_SERVER))
		{
			self::$_referer = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			self::$_referer = '';
		}
	
		$params = array();
		$post = false;
		if (array_key_exists('r', $_GET)) {
			$request = $_GET['r'];
			if (count($_GET) > 1) {
				$params = $_GET;
			}
		} else if (array_key_exists('r', $_POST)) {
			$request = $_POST['r'];
			$params = $_POST;
			$post = true;
		} else {
			$request = '';
		}
		
		self::process($request, $params, $post);
	}

	public static function process($request, $params = array(), $post = false)
	{
		error_reporting(E_ALL);
		self::normalizeRequest($request);
		if ($request == '') $request = 'home';
		$requestParams = explode('/', $request);
		
		$controllerName = ucfirst(array_shift($requestParams));
		
		try
		{
			list($controllerClass, $action, $params) = 
	                      self::findController($controllerName, $requestParams,
	                                           $params, $post);
			if (!$post && $controllerClass->isPost($action))
			{
				throw new WrongRequestMethodException();
			}
			
			if ($post && strpos(self::$_referer, self::$_site) === false)
			{
				throw new WrongRequestMethodException();
			}
		
			try
			{
				call_user_func_array(array($controllerClass, $action), $params);
			}
			catch (Exception $e)
			{
				$controllerClass->systemError($request, self::$_referer, $e);
			}
		}
		catch (RequestNotFoundException $e)
		{
			$controller = new Controller();
			$controller->init('.', self::$_appdir);
			
			$controller->notFound($request, self::$_referer, $e);
			return;
		}
		catch (Exception $e)
		{
			$controller = new Controller();
			$controller->init('.', self::$_appdir);
			
			$controller->systemError($request, self::$_referer, $e);
			return;
		}
	}
	
	public static function loadModel($name)
	{
		$name = strtolower($name);
		if (array_key_exists($name, self::$_models))
		{
			include_once self::$_models[$name];
		}
	}
	
	/* == These functions are only used for testing purposes == */
	
	public static function setSite($url)
	{
		self::$_site = $url;
	}
	
	public static function spoofReferer($referer)
	{
		self::$_referer = $referer;
	}
	
	/* == Private Functions == */
	
	private static function normalizeRequest(&$request)
	{
		if (strlen($request) == 0) return;
		while ($request[strlen($request)-1] == '/')
		{
			$request = substr($request, 0, strlen($request) - 1);
		}
	}

	private static function findController($controllerName, $requestParams,
	                                       $params, $post,
	                                       $controllerID = null)
	{
		if ($controllerID == null) $controllerID = strtolower($controllerName);

		if (array_key_exists($controllerID, self::$_controllers)) {
			include_once self::$_controllers[$controllerID]['fullpath'];
			$controllerClassName = $controllerName.'Controller';

			$controllerInfo = new ReflectionClass($controllerClassName);
			
			if (count($requestParams) > 0)
			{
				$actionName = array_shift($requestParams);
			}
			else
			{
				$actionName = 'index';
			}
			if ($controllerInfo->hasMethod($actionName) &&
			    ($methodInfo = $controllerInfo->getMethod($actionName)) &&
			    $methodInfo->isPublic())
			{
				$controllerClass = new $controllerClassName;
				$path = dirname(self::$_controllers[$controllerID]['fullpath']);
				$controllerClass->init($path.'/views/', self::$_appdir);
				
				if ($params)
				{
					$inputParams = $params;
					$params = array();
					$functionParams = $methodInfo->getParameters();
					
					foreach ($functionParams as $fParam)
					{
						if (array_key_exists($fParam->getName(), $inputParams))
						{
							$params[$fParam->getPosition()] =
							                  $inputParams[$fParam->getName()];
						}
						else if ($fParam->isDefaultValueAvailable())
						{
							$params[$fParam->getPosition()] =
							                        $fParam->getDefaultValue();
						}
						else
						{
							$params[$fParam->getPosition()] = '';
						}
					}
				}
				else
				{
					if (count($requestParams) <
					    $methodInfo->getNumberOfRequiredParameters())
					{
						throw new NotEnoughParametersException();
					}
					$params = $requestParams;
				}
				return array($controllerClass, $actionName, $params);
			}
			else
			{
				if ($actionName != 'index')
				{
					$subController = $controllerName.ucfirst($actionName);
					$subControllerID = $controllerID.'.'.$actionName;
					return self::findController($subController, $requestParams,
					                            $params, $post, $subControllerID);
				}
				else
				{
					throw new RequestNotFoundException();
				}
			}
		}
		else
		{
			throw new RequestNotFoundException($controllerName);
		}
	}
	
	private static function loadDir($basedir, $restrict)
	{
		if ($basedir == null) return;
		foreach (scandir($basedir) as $subdir)
		{
			if ($subdir[0] == '.') continue;
			$dir = $basedir.'/'.$subdir;
			if ($restrict != null && !in_array($subdir, $restrict)) continue;
			if (is_dir($dir))
			{
				// Scan files in dir
				foreach (scandir($dir) as $sfile)
				{
					if ($sfile[0] == '.') continue;
					$file = $dir . '/' . $sfile;
					if (is_file($file))
					{
						$pos = strrpos($sfile, '.controller.php');
						if ($pos !== false)
						{
							$firstPart = substr($sfile, 0, $pos);
							self::$_controllers[$firstPart] = array(
							        'file' => $sfile,
							        'path' => $subdir,
							        'fullpath' => $file);
						}
						$pos = strrpos($sfile, '.model.php');
						if ($pos !== false)
						{
							$firstPart = substr($sfile, 0, $pos);
							self::$_models[$firstPart] = $file;
						}
					}
					else if (is_dir($file) && $sfile == 'models')
					{
						foreach (scandir($file) as $smodel)
						{
							if ($smodel[0] == '.') continue;
							$model = $file . '/' . $smodel;
							$pos = strrpos($smodel, '.model.php');
							if ($pos !== false)
							{
								$firstPart = substr($smodel, 0, $pos);
								self::$_models[$firstPart] = $model;
							}
						}
					}
				}
			}
		}
	}
}

class WrongRequestMethodException extends Exception
{
	public function __construct()
	{
		parent::__construct('Wrong request method');
	}
}


class NotEnoughParametersException extends Exception
{
	public function __construct()
	{
		parent::__construct('Not enough parameters supplied');
	}
}

class RequestNotFoundException extends Exception
{
	public function __construct($request)
	{
		parent::__construct('Request not found: '. $request);
	}
}
?>
