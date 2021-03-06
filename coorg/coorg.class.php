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

error_reporting(E_ALL);

require_once 'coorg/controller.class.php';
require_once 'coorg/asidecontroller.class.php';
require_once 'coorg/config.class.php';
require_once 'coorg/db.class.php';
require_once 'coorg/files.class.php';
require_once 'coorg/model.class.php';
require_once 'coorg/i18n.class.php';
require_once 'coorg/pager.class.php';
require_once 'coorg/sortable.class.php';
require_once 'coorg/relations/genericmodel.variant.php';
require_once 'coorg/relations/relation.interface.php';
require_once 'coorg/relations/relationpart.interface.php';
require_once 'coorg/relations/one2one.class.php';
require_once 'coorg/relations/one2many.class.php';
require_once 'coorg/relations/many2many.class.php';
require_once 'coorg/relations/manycollection.class.php';
require_once 'coorg/relations/manymanycollection.class.php';
require_once 'coorg/relations/onerelation.class.php';
require_once 'coorg/relations/manyrelation.class.php';
require_once 'coorg/relations/manymanyrelation.class.php';
require_once 'coorg/normalize.class.php';
require_once 'coorg/header.interface.php';
require_once 'coorg/coorgsmarty.interface.php';
require_once 'coorg/state.interface.php';
require_once 'coorg/mail.interface.php';
require_once 'coorg/fileupload.interface.php';


class CoOrg {
	const PANEL_ORIENT_HORIZONTAL = 1;
	const PANEL_ORIENT_VERTICAL = 2;

	private static $_controllers = array();
	public static $_models = array();
	private static $_asides = array();
	private static $_beforeFilters = array();
	private static $_extras = array();
	
	private static $_appdir;
	private static $_pluginDir;
	private static $_config;
	private static $_resources = array();
	private static $_resourceTheme = 'default';
	
	private static $_request;
	private static $_requestParameters;

	public static function init(Config $config, $appdir, $pluginsDir)
	{
		self::loadDir($pluginsDir, $config->get('enabled_plugins'));
		self::loadDir($appdir, null);
		self::$_resources = array();		
		I18n::addSearchDir('coorg', 'coorg');
		self::$_pluginDir = $pluginsDir;
		self::$_appdir = $appdir;
		self::$_config = $config;
		spl_autoload_register(array('CoOrg', 'loadModel'));
		self::loadPluginInfo('relations');
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
		
		if (get_magic_quotes_gpc())
		{
			self::clearMessAfterMagicQuotes();
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
		self::$_resourceTheme = 'default';
		include 'static/default/resources.coorg.php';
		$theme = self::$_config->get('theme');
		if (!$theme) $theme = 'default';
		if ($theme != 'default')
		{
			self::$_resourceTheme = $theme;
			if (file_exists('static/'.$theme.'/resources.coorg.php'))
			{
				include 'static/'.$theme.'/resources.coorg.php';
			}
		}
	
		self::normalizeRequest($request);
		$url = $request;
		if ($request == '/' || $request == '') $request = 'home';
		$requestParams = explode('/', $request);
		
		$controllerName = ucfirst(array_shift($requestParams));
		$prefix = null;
		$requestWithoutPrefix = $request;
		if (strlen($controllerName) == 2)
		{
			$prefix = strtolower($controllerName).'/';
			I18n::setLanguage(strtolower($controllerName));
			$requestWithoutPrefix = implode('/', $requestParams);
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
			$found = false;
			//TODO: move Language DB to default install
			//      and unittest this... (in the CoOrg tests).
			if (class_exists('Language'))
			{
				$preferredLanguages = Session::getPreferredLanguages();
				$installedLanguages = Language::languageCodes(); // This is in the admin class, but should really be in the default instal
			
				foreach ($preferredLanguages as $lc)
				{
					if (in_array($lc, $installedLanguages))
					{
						$found = true;
						I18n::setLanguage($lc);
						break;
					}
					else if (strpos($lc, '-'))
					{
						$slc = substr($lc, 0, strpos($lc, '-'));
						if (in_array($slc, $installedLanguages))
						{
							$found = true;
							I18n::setLanguage($slc);
							break;
						}
					}
				}
			}
			
			if (!$found && self::$_config->has('defaultLanguage'))
			{
				I18n::setLanguage(self::$_config->get('defaultLanguage'));
			}
		}
		
		try
		{
			list($controllerClass, $action, $params, $request, $parentClasses) = 
	                      self::findController($controllerName, $requestParams,
	                                           $params, $post);
			$controllerClass->coorgRequest = $requestWithoutPrefix;
			$coorgUrl = self::config()->get('path').$prefix.$requestWithoutPrefix;
			self::normalizeRequest($coorgUrl);
			$controllerClass->coorgUrl = $coorgUrl;
			$controllerClass->staticPath = self::$_config->get('path').'static/';
			$controllerClass->coorgLanguage = self::getLanguage();
			if (!$post && $controllerClass->isPost($action))
			{
				throw new WrongRequestMethodException();
			}
			
			if ($post && strpos(Session::getReferrer(), Session::getSite()) === false)
			{
				throw new WrongRequestMethodException();
			}
		
			self::$_request = $request;
			self::$_requestParameters = $params;
			
			$continue = true;
			foreach ($parentClasses as $pClassName)
			{
				$pClass = new $pClassName;
				$continue = $pClass->beforeFilters(null, self::$_beforeFilters, $params);
				if (!$continue)
				{
					break;
				}
			}
			if ($continue && $controllerClass->beforeFilters($action, self::$_beforeFilters, $params))
			{
				try
				{
					call_user_func_array(array($controllerClass, $action), $params);
				}
				catch (Exception $e)
				{
					$controllerClass->systemError($request, Session::getReferrer(), $e);
				}
			}
			$controllerClass->done();
		}
		catch (RequestNotFoundException $e)
		{
			$controller = new Controller();
			$controller->init('.', self::$_appdir);
			
			$controller->notFound($request, Session::getReferrer(), $e);
			return;
		}
		catch (Exception $e)
		{
			$controller = new Controller();
			$controller->init('.', self::$_appdir);
			
			$controller->systemError($request, Session::getReferrer(), $e);
			return;
		}
	}
	
	public static function loadModel($fname)
	{
		$name = strtolower($fname);
		
		if (array_key_exists($name, self::$_models))
		{
			require_once self::$_models[$name];
		}
		else if (preg_match('/(.*)ControllerHelper/', $fname, $matches))
		{
			$ID = '';
			for ($i = 0; $i < strlen($matches[1]); $i++)
			{
				$char = $matches[1][$i];
				if (strtolower($char) == $char || $i == 0)
				{
					$ID .= strtolower($char);
				}
				else
				{
					$ID .= '.'.strtolower($char);
				}
			}

			if (array_key_exists($ID, self::$_controllers))
			{
				include_once self::$_controllers[$ID]['fullpath'];
			}
		}
	}
	
	public static function createFullURL($params, $language = null, $anchor = null)
	{
		return Session::getSite().self::createURL($params, $language, $anchor);
	}
	
	public static function createURL($params, $language = null, $anchor = null)
	{
		if (is_string($params))
		{
			$params = array($params);
		}
		if (count($params) == 1 && $params[0] == 'home/index') $params = array();
		
		$urlPrefix = '';
		if (self::$_config->has('urlPrefix'))
		{
			if (!$language)
			{
				$language = self::getLanguage();
			}
			
			$urlPrefix = self::$_config->get('urlPrefix').'/';
			$urlPrefix = str_replace(':language', $language, $urlPrefix);
			if ($urlPrefix == '/') $urlPrefix = '';
		}
		foreach ($params as $k=>&$p)
		{
			if ($k > 0)
			{
				$p = coorgencode($p);
			}
		}
	
		$url = self::$_config->get('path').$urlPrefix.implode('/', $params);
		self::normalizeRequest($url);
		if ($anchor)
		{
			$url .= '#'.$anchor;
		}
		return $url;
	}
	
	public static function staticFile($file, $app = null)
	{
		$external = self::$_config->get('staticpath');	
		$theme = self::$_config->get('theme');
		if (!$theme) $theme = 'default';
		if ($app == null)
		{
			$baseVersion = null;
			if (! array_key_exists($file, self::$_resources['/']))
			{
				$theme = 'default';
				$version = '';
			}
			else
			{
				if (!array_key_exists($theme, self::$_resources['/'][$file]))
				{
					$theme = 'default';
				}
				if (self::$_resources['/'][$file][$theme][1])
				{
					$baseVersion = self::$_resources['/'][$file]['default'][0];
				}
				$version = self::$_resources['/'][$file][$theme][0];
			}
			if (!$baseVersion)
			{
				return self::createStaticPath($file, $version, $theme, $external);
			}
			else
			{
				return array(
					self::createStaticPath($file, $baseVersion, 'default', $external),
					self::createStaticPath($file, $version, $theme, $external)
				);
			}
		}
		else
		{
			$isPlugin = in_array($app, self::$_config->get('enabled_plugins'));
			if (!array_key_exists($app, self::$_resources))
			{
				$path = $isPlugin ? self::$_pluginDir : self::$_appdir;
				$appPath = $path . '/'.$app.'/static/';
				self::$_resourceTheme = 'default';
				include $appPath.'default/resources.coorg.php';
				if ($theme != 'default')
				{
					self::$_resourceTheme = $theme;
					if (file_exists($appPath.$theme.'/resources.coorg.php'))
					{
						include $appPath.$theme.'/resources.coorg.php';
					}
				}
			}

			if (array_key_exists($file, self::$_resources[$app]))
			{
				$versions = self::$_resources[$app][$file];
				$defaultVersion = null;
				if (array_key_exists($theme, $versions))
				{
					$version = self::$_resources[$app][$file][$theme][0];
					if (self::$_resources[$app][$file][$theme][1])
					{
						$defaultVersion = self::$_resources[$app][$file]['default'][0];
					}
				}
				else
				{
					$theme = 'default';
					$version = self::$_resources[$app][$file][$theme][0];
				}
			}
			else
			{
				throw new Exception('No version specified for '.$file.'::'.$app);
			}
			if (!$defaultVersion)
			{
				return self::createStaticPath($file, $version, $theme, $external, $app, $isPlugin);
			}
			else
			{
				return array(self::createStaticPath($file, $defaultVersion, 'default', $external, $app, $isPlugin),
				             self::createStaticPath($file, $version, $theme, $external, $app, $isPlugin));
			}
		}
	}
	
	private static function createStaticPath($file, $version, $theme, $external, $app = null, $isPlugin = null)
	{
		if ($app)
		{
			if ($external && self::$_config->get('staticpath/'.$app))
			{
				$pluginPath = $external.$app.'/';
			}
			else
			{
				$pluginPath = self::$_config->get('path');
				if ($isPlugin)
				{
					$pluginPath .= self::$_pluginDir.'/'.$app;
				}
				else
				{
					$pluginPath .= self::$_appdir.'/'.$app;
				}
				$pluginPath .= '/static/';
			}
			return $pluginPath.$theme.'/'.$file.'?v='.$version;
		}
		else
		{
			if ($external)
			{
				$path = $external.'_root/';
			}
			else
			{
				$path = self::$_config->get('path').'static/';
			}
			return $path.$theme.'/'.$file.'?v='.$version;
		}
	}
	
	public static function getWidgetInstance($widgetName)
	{
		$p = explode('/', $widgetName, 2);

		include_once(self::$_asides[$p[0]][$p[1]]);
			
		$pluginName = '';
		foreach (explode('-',$p[0]) as $pluginNamePart)
		{
			$pluginName .= ucfirst($pluginNamePart);
		}
		
		$className = $pluginName.ucfirst($p[1]).'Aside';
		if (! class_exists($className))
		{
			$className = $pluginName.ucfirst($p[1]).'Widget';
		}
		
		return new $className(null, null);
	}
	
	public function tagURI()
	{
		$args = func_get_args();
		return 'tag:'.self::config()->get('site/taguri/host') . ',' .
		              self::config()->get('site/taguri/date') .
		          ':' . implode('/', array_map('rawurlencode', $args));
	}
	
	public static function aside($name, $smarty, $preview = false,
	                             $edit = false, $widgetID = null)
	{
		$siteWidgetsOnly = false;
		if ($name && $name != '__list_site__')
		{
			$siteWidgetsOnly = $name == '__site__';
			if ($name == 'main')
			{
				$orient = CoOrg::PANEL_ORIENT_VERTICAL;
			}
			else
			{
				$orient = CoOrg::PANEL_ORIENT_HORIZONTAL;
			}
			$items = self::$_config->get('aside/'.$name);
			if ($items == null) return '';
		}
		else
		{
			$orient = CoOrg::PANEL_ORIENT_VERTICAL;
			$siteWidgetsOnly = $name == '__list_site__';
			$preview = true;
			$items = array();
			foreach (self::$_asides as $plugin => $pWidgets)
			{
				foreach ($pWidgets as $pName => $pWidget)
				{
					$items[] = $plugin.'/'.$pName;
				}
			}
		}
		$s = '';
		foreach ($items as $key=>$item)
		{
			if (is_array($item))
			{
				$widget = $item['widgetID'];
				unset($item['widgetID']);
				$widgetParams = $item;
			}
			else
			{
				$widget = $item;
				$widgetParams = $name ? array() : null;
			}
			$p = explode('/', $widget, 2);
			
			include_once(self::$_asides[$p[0]][$p[1]]);
			
			$pluginName = '';
			foreach (explode('-',$p[0]) as $pluginNamePart)
			{
				$pluginName .= ucfirst($pluginNamePart);
			}
			$className = $pluginName.ucfirst($p[1]).'Aside';
			if (! class_exists($className))
			{
				$className = $pluginName.ucfirst($p[1]).'Widget';
			}
			$i = new $className($smarty, dirname(self::$_asides[$p[0]][$p[1]]).'/../views/');
			if (($i instanceof SiteWidgetController && !$siteWidgetsOnly) ||
			    (!($i instanceof SiteWidgetController) && $siteWidgetsOnly))
			{			
				continue;
			}
			
			
			if (!$preview)
			{
				$r = self::$_requestParameters;
				if ($r == null) $r = array();
				array_unshift($r, self::$_request);
				if (!($i instanceof SiteWidgetController))
				{
					array_unshift($r, $orient);
				}
				array_unshift($r, $widgetParams);
				$s .= call_user_func_array(array($i, 'run'), $r);
			}
			else
			{
				$i->widgetID = $key;
				$i->panelID = $name;
				$isRelocatable = $name && $name != '__list_site__';
				if ($isRelocatable && $key > 0) $i->widgetUp = $key - 1;
				if ($isRelocatable && $key < count($items) - 1) $i->widgetDown = $key + 1;
				if ($isRelocatable && $i instanceof AsideConfigurableController)
				{
					$i->widgetConfigure = true;
				}
				if (!$name)
				{
					$i->widgetName = $widget;
					$i->panels = array('main' => 'Main',
					                   'navigation-left' => 'Navigation (Left)',
					                   'navigation-right' => 'Navigation (Right)');
				}
				else if ($name == '__list_site__')
				{
					$i->widgetName = $widget;
					$i->panels = '__site__';
				}
				if (!($edit && $key == $widgetID))
				{
					$s .= $i->preview($widgetParams, $orient);
				}
				else
				{
					$s .= $i->configure($widgetParams, $orient);
				}
			}
		}
		
		return $s;
	}
	
	public static function setDefaultLanguage($l)
	{
		self::$_config->set('defaultLanguage', $l);
	}
	
	public static function getDefaultLanguage()
	{
		if (self::$_config->has('defaultLanguage'))
		{
			return self::$_config->get('defaultLanguage');
		}
		else
		{
			return 'en';
		}
	}

	public static function getLanguage()
	{
		$l = I18n::getLanguage();
		return ($l == '' ? 'en' : $l);
	}
	
	public static function getTheme()
	{
		$theme = self::$_config->get('theme');
		return $theme ? $theme : 'default';
	}
	
	public static function getDataPath($sub)
	{
		return 'data/'.$sub;
	}
	
	public static function getDataManager($sub)
	{
		if (! defined('COORG_UNIT_TEST'))
		{
			return new DataManager(self::getDataPath($sub));
		}
		else
		{
			return new MockDataManager(self::getDataPath($sub));
		}
	}
	
	public static function config()
	{
		return self::$_config;
	}
	
	public static function loadPluginInfo($id, $dir = null)
	{
		if (array_key_exists($id, self::$_extras))
		{
			if ($dir == null)
			{
				foreach (self::$_extras[$id] as $file)
				{
					include_once $file;
				}
			}
			else
			{
				if (array_key_exists($dir, self::$_extras[$id]))
				{
					include_once (self::$_extras[$id][$dir]);
				}
			}
		}
	}
	
	public static function stocks($stock)
	{
		$stocks = array(
			'edit' => array('img' => 'images/icons/edit.png', 'alt' => t('Edit'), 'title' => t('Edit')),
			'delete' => array('img' => 'images/icons/edit-delete.png', 'alt' => t('Delete'), 'title' => t('Delete')),
			'list-remove' => array('img' => 'images/icons/list-remove.png', 'alt' => t('Remove'), 'title' => t('Remove')),
			'list-add' => array('img' => 'images/icons/list-add.png', 'alt' => t('Add'), 'title' => t('Add')),
			'audio-captcha' => array('img' => 'images/icons/audio-captcha.png', 'alt' => t('Audio Captcha'), 'title' => t('Audio Captcha')),
			'image-captcha' => array('img' => 'images/icons/image-captcha.png', 'alt' => t('Image Captcha'), 'title' => t('Iamge Captcha')),
			'refresh-captcha' => array('img' => 'images/icons/refresh.png', 'alt' => t('Refresh Captcha'), 'title' => t('Refresh Captcha')),
			'spam' => array('img' => 'images/icons/spam.png', 'alt' => t('Mark as spam'), 'title' => t('Mark as spam')),
			'notspam' => array('img' => 'images/icons/notspam.png', 'alt' => t('Unmark as spam'), 'title' => t('Unmark as spam'))
		);
		
		return $stocks[$stock];
	}
	
	public static function resreg($app, $resource, $version, $extends = false)
	{
		if (!array_key_exists($app, self::$_resources))
		{
			self::$_resources[$app] = array();
		}
		if (array_key_exists($resource, self::$_resources[$app]))
		{
			self::$_resources[$app][$resource][self::$_resourceTheme] = array($version, $extends);
		}
		else
		{
			self::$_resources[$app][$resource] = array(self::$_resourceTheme => array($version, $extends));
		}
	}
	
	/* == Private Functions == */
	
	private static function normalizeRequest(&$request)
	{
		while (strlen($request) > 1 && $request[strlen($request)-1] == '/')
		{
			$request = substr($request, 0, strlen($request) - 1);
		}
	}

	private static function findController($controllerName, $requestParams,
	                                       $params, $post,
	                                       $controllerID = null, $request = null,
	                                       $parentClasses = array(),
	                                       $renderType = null)
	{
		if (strpos($controllerName, '.') !== false)
		{
			$renderType = substr($controllerName, strpos($controllerName, '.') + 1);
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
					if ($renderType)
					{
						$controllerClass->init($path.'/views/', self::$_appdir, $renderType);
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
						if ($fParam->getName() == '_')
						{
							$params[$fParam->getPosition()] = $inputParams;
							break;
						}
						if (array_key_exists($fParam->getName(), $inputParams))
						{
							$inputParam = $inputParams[$fParam->getName()]; 
							if (is_array($inputParam))
							{
								foreach ($inputParam as $k => $p)
								{
									if ($p == '')
										unset($inputParam[$k]);
								}
							}
							$params[$fParam->getPosition()] = $inputParam;
							unset($inputParams[$fParam->getName()]);
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
					$params = array();
					foreach ($requestParams as $p)
					{
						$params[] = coorgdecode($p);
					}
				}
				return array($controllerClass, $actionName, $params, $request.'/'.$actionName, $parentClasses);
			}
			else
			{
				if ($actionName != 'index')
				{
					$parentClasses[] = $controllerClassName;
					$subController = $controllerName.ucfirst($actionName);
					$subControllerID = $controllerID.'.'.$actionName;
					$subRequest = $request . '/'.$actionName;
					return self::findController($subController, $requestParams,
					                            $params, $post, $subControllerID,
					                            $subRequest, $parentClasses,
					                            $renderType);
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
				if (!array_key_exists($subdir, self::$_resources))
				{
					// In testing we clear loadDir a few times, but we only include
					// resources.coorg once for each run, so do not throw away this info
					self::$_resources[$subdir] = array();
				}
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
								self::$_extras[$ID][$subdir] = $file;
							}
							else
							{
								self::$_extras[$ID] = array($subdir => $file);
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
						I18n::addSearchDir($file, $subdir);
					}
				}
			}
		}
	}
	
	private static function clearMessAfterMagicQuotes()
	{
		$cleanups = array(&$_POST, &$_GET, &$_COOKIE);
		foreach ($cleanups as &$array)
		{
			foreach ($array as &$val)
			{
				if (is_array($val))
				{
					foreach ($val as &$r)
					{
						$r = stripslashes($r);
					}
				}
				else
				{
					$val = stripslashes($val);
				}
			}
		}
	}
}

function coorgencode($input)
{
	$toEncode = array('$', '?', '/', '&', '.', '#');
	foreach ($toEncode as $char)
	{
		$input = str_replace($char, '$'.dechex(ord($char)), $input);
	}
	return $input;
}

function coorgdecode($input)
{
	$toDecode = array('?', '/', '&', '.', '#', '$');
	foreach ($toDecode as $char)
	{
		$input = str_replace('$'.dechex(ord($char)), $char, $input);
	}
	return $input;
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

/**
	t('Save');
	t('Language');
*/
?>
