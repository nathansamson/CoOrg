<?php

require_once 'coorg/controller.class.php';

class CoOrg {

	private static $_controllers = array();
	private static $_site = null;
	private static $_referrer = null;

	public static function init($basedir)
	{
		foreach (scandir($basedir) as $subdir)
		{
			if ($subdir[0] == '.') continue;
			$dir = $basedir.'/'.$subdir;
			
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
					}
				}
			}
		}
	}
	
	public static function clear()
	{
	}

	public static function run()
	{
		$requestContainsFullParams = false;
		if (array_key_exists('r', $_GET)) {
			$request = $_GET['r'];
			if (count($_GET) > 1) {
				$requestContainsFullParams = true;
				$params = $_GET;
			}
		} else if (array_key_exists('r', $_POST)) {
			$request = $_POST['r'];
			$params = $_POST;
		} else {
			$request = '';
		}
		
		if ($requestContainsFullParams) {
			$params = array();
		}
		self::process($request, $params, !$requestContainsFullParams);
	}

	public static function process($request, $params = array(), $post = false)
	{
		self::normalizeRequest($request);
		if ($request == '') $request = 'home';
		$requestParams = explode('/', $request);
		
		$controllerName = ucfirst(array_shift($requestParams));
		list($controllerClass, $action, $params) = 
		  self::findController($controllerName, $requestParams, $params, $post);
		
		if ($controllerClass && $action)
		{
			if (!$post && $controllerClass->isPost($action))
			{
				throw new RequestNotFoundException('');
			}
			
			if ($post && strpos(self::$_referrer, self::$_site) === false)
			{
				throw new RequestNotFoundException('');
			}
		
			call_user_func_array(array($controllerClass, $action), $params);
		}
		else
		{
			var_dump($action);
			var_dump($controllerClass);
			die('SHIT');
			// Error 404
		}
	}
	
	/* == These functions are only used for testing purposes == */
	
	public static function setSite($url)
	{
		self::$_site = $url;
	}
	
	public static function spoofReferrer($referrer)
	{
		self::$_referrer = $referrer;
	}
	
	/* == Private Functions == */
	
	private static function normalizeRequest(&$request)
	{
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
}

class NotEnoughParametersException extends Exception {}

class RequestNotFoundException extends Exception
{
	public function __construct($request)
	{
		parent::__construct('Request not found: '. $request);
	}
}
?>
