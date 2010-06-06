<?php

/**
 * @before set alphasub
*/
class AlphaSubController extends Controller
{
	public static $indexExecuted = true;

	public static $actionExecuted = false;
	public static $actionParams = array();
	
	public static $i18ntest1;
	public static $i18ntest2;
	public static $i18nfromAlpha;
	public static $notFoundWithParams;
	
	public static $executed;
	public static $set = array();
	
	public function index()
	{
		self::$indexExecuted = true;
	}
	
	public function action($p1, $p2)
	{
		self::$actionExecuted = true;
		self::$actionParams = array($p1, $p2);
	}
	
	public function i18ntest()
	{
		self::$i18ntest1 = t('Google is nice');
		self::$i18ntest2 = t('%name is %what', array('name' => 'Google', 'what' => 'shit'));
		self::$i18nfromAlpha = t('This message comes from alpha');
		self::$notFoundWithParams = t('Message not found with %n paramaters', array('n' => 1));
	}
	
	public function doredirect()
	{
		$this->redirect('alpha/sub/google');
	}
	
	/**
	 * @before set propagate
	*/
	public function beforeFilterPropagation()
	{
		self::$executed = true;
	}
	
	protected function set($v)
	{
		self::$set[] = $v;
		return true;
	}
}

?>
