<?php

error_reporting(E_ALL);

require_once 'coorg/controller.class.php';
require_once 'coorg/asidecontroller.class.php';
require_once 'coorg/config.class.php';
require_once 'coorg/db.class.php';
require_once 'coorg/model.class.php';
require_once 'coorg/i18n.class.php';
require_once 'coorg/header.interface.php';
require_once 'coorg/coorgsmarty.interface.php';
require_once 'coorg/state.interface.php';

class CoOrg {

	private static $_controllers = array();
	private static $_models = array();
	private static $_asides = array();
	private static $_beforeFilters = array();
	private static $_extras = array();
	
	private static $_site = null;
	private static $_referer = null;
	private static $_appdir;
	private static $_config;
	
	private static $_request;
	private static $_requestParameters;

	public static function init(Config $config, $appdir, $pluginsDir)
	{
		self::loadDir($pluginsDir, $config->get('enabled_plugins'));
		self::loadDir($appdir, null);
		self::$_appdir = $appdir;
		self::$_config = $config;
		spl_autoload_register(array('CoOrg', 'loadModel'));
	}
	
	public static function clear()
	{
	}

	public static function run()
	{
		$config = new Config('config/config.php');
		CoOrg::init($config, 'app', 'plugins');
		DB::open($config->get('dbdsn'), $config->get('dbuser'),
		         $config->get('dbpass'));
		
		self::$_site = 'http://gamma/';
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
			} else if (count($_POST) > 0) {
				$params = $_POST;
				$post = true;
			}
		} else {
			$request = '';
		}
		self::process($request, $params, $post);
	}

	public static function process($request, $params = array(), $post = false)
	{
		self::normalizeRequest($request);
		if ($request == '') $request = 'home';
		$requestParams = explode('/', $request);
		
		$controllerName = ucfirst(array_shift($requestParams));
		if (strlen($controllerName) == 2)
		{
			I18n::setLanguage(strtolower($controllerName));
			
			if (count($requestParams) > 0)
			{
				$controllerName = ucfirst(array_shift($requestParams));
			}
			else
			{
				$controllerName = 'Home';
			}
		}
		else
		{
			if (self::$_config->has('defaultLanguage'))
			{
				I18n::setLanguage(self::$_config->get('defaultLanguage'));
			}
		}
		
		try
		{
			list($controllerClass, $action, $params, $request) = 
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
		
			self::$_request = $request;
			self::$_requestParameters = $params;	
			if ($controllerClass->beforeFilters($action, self::$_beforeFilters, $params))
			{
				try
				{
					call_user_func_array(array($controllerClass, $action), $params);
				}
				catch (Exception $e)
				{
					$controllerClass->systemError($request, self::$_referer, $e);
				}
			}
			$controllerClass->done();
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
	
	public static function createURL($params)
	{
		$urlPrefix = '';
		if (self::$_config->has('urlPrefix'))
		{
			$language = I18n::getLanguage();
			
			if ($language == null)
			{
				$language = 'en';
			}
			
			$urlPrefix = self::$_config->get('urlPrefix').'/';
			$urlPrefix = str_replace(':language', $language, $urlPrefix);
		}
	
		return self::$_config->get('path').$urlPrefix.implode('/', $params);
	}
	
	public static function staticFile($file)
	{
		return self::$_config->get('path').'static/'.$file;
	}
	
	public static function aside($name, $smarty)
	{
		$items = self::$_config->get('aside/'.$name);
		if ($items == null) return '';
		$s = '';
		foreach ($items as $item)
		{
			$p = explode('/', $item, 2);
			
			include_once(self::$_asides[$p[0]][$p[1]]);
			
			$className = ucfirst($p[0]).ucfirst($p[1]).'Aside';
			$i = new $className($smarty, dirname(self::$_asides[$p[0]][$p[1]]).'/../views/');
			$r = self::$_requestParameters;
			array_unshift($r, self::$_request);
			$s .= call_user_func_array(array($i, 'run'), $r);
		}
		
		return $s;
	}

	public static function getLanguage()
	{
		$l = I18n::getLanguage();
		return ($l == '' ? 'en' : $l);
	}
	
	public static function config()
	{
		return self::$_config;
	}
	
	public static function loadPluginInfo($id)
	{
		if (array_key_exists($id, self::$_extras))
		{
			foreach (self::$_extras[$id] as $file)
			{
				include_once $file;
			}
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
	                                       $controllerID = null, $request = null)
	{
		if (strpos($controllerName, '.') !== false)
		{
			$type = substr($controllerName, strpos($controllerName, '.') + 1);
			$controllerName = substr($controllerName, 0, strpos($controllerName, '.'));
		}
		if ($controllerID == null) $controllerID = strtolower($controllerName);
		if ($request == null) $request = $controllerID;

		if (array_key_exists($controllerID, self::$_controllers) ||
		    (class_exists($controllerName.'Controller') && substr($controllerName,0, 4) == 'Mock')) {

			$mock = false;
		    if (class_exists($controllerName.'Controller') && substr($controllerName,0, 4) == 'Mock')
		    {
		    	$mock = true;
		    	$controllerClassName = $controllerID.'Controller';
		    }
		    else
		    {
				include_once self::$_controllers[$controllerID]['fullpath'];
				$controllerClassName = $controllerName.'Controller';
			}

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
				if (!$mock)
				{
					$path = dirname(self::$_controllers[$controllerID]['fullpath']);
					if (isset($type))
					{
						$controllerClass->init($path.'/views/', self::$_appdir, $type);
					}
					else
					{
						$controllerClass->init($path.'/views/', self::$_appdir);
					}
				}
				else
				{
					$controllerClass->init('', self::$_appdir);
				}
				
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
				return array($controllerClass, $actionName, $params, $request.'/'.$actionName);
			}
			else
			{
				if ($actionName != 'index')
				{
					$subController = $controllerName.ucfirst($actionName);
					$subControllerID = $controllerID.'.'.$actionName;
					$subRequest = $request . '/'.$actionName;
					return self::findController($subController, $requestParams,
					                            $params, $post, $subControllerID,
					                            $subRequest);
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
				self::$_asides[$subdir] = array();
				// Scan files in dir
				foreach (scandir($dir) as $sfile)
				{
					if ($sfile[0] == '.') continue;
					$file = $dir . '/' . $sfile;
					if (is_file($file))
					{
						$builtin = false;
						$pos = strrpos($sfile, '.controller.php');
						if ($pos !== false)
						{
							$builtin = true;
							$firstPart = substr($sfile, 0, $pos);
							self::$_controllers[$firstPart] = array(
							        'file' => $sfile,
							        'path' => $subdir,
							        'fullpath' => $file);
						}
						$pos = strrpos($sfile, '.model.php');
						if ($pos !== false)
						{
							$builtin = true;
							$firstPart = substr($sfile, 0, $pos);
							self::$_models[$firstPart] = $file;
						}
						
						$pos = strrpos($sfile, '.before.php');
						if ($pos !== false)
						{
							$builtin = true;
							$firstPart = substr($sfile, 0, $pos);
							self::$_beforeFilters[ucfirst($firstPart)] = $file;
						}
						
						if (! $builtin)
						{
							$ID = substr($sfile, 0, -4);
							if (array_key_exists($ID, self::$_extras))
							{
								self::$_extras[$ID][] = $file;
							}
							else
							{
								self::$_extras[$ID] = array($file);
							}
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
					else if (is_dir($file) && $sfile == 'aside')
					{
						foreach (scandir($file) as $saside)
						{
							if ($saside[0] == '.') continue;
							$aside = $file . '/' . $saside;
							$pos = strrpos($saside, '.aside.php');
							if ($pos !== false)
							{
								$firstPart = substr($saside, 0, $pos);
								self::$_asides[$subdir][$firstPart] = $aside;
							}
						}
					}
					else if (is_dir($file) && $sfile == 'lang')
					{
						I18n::addSearchDir($file);
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
