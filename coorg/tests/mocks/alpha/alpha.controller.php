<?php

class AlphaController extends Controller {
	public static $betaExecuted = false;
	public static $betaParams = array();
	
	public static $indexExecuted = false;
	public static $indexParams = array();
	
	public static $zetaExecuted = false;
	public static $zetaParams = array();
	
	public static $postExecuted = false;
	public static $postParams = array();
	
	public static $postRequiredExecuted = false;
	
	protected $post = array('postrequired');
	
	public function index($p1 = '', $p2 = '') {
		self::$indexExecuted = true;
		self::$indexParams = array($p1, $p2);
	}
	
	public function beta($p1, $p2) {
		self::$betaExecuted = true;
		self::$betaParams = array($p1, $p2);
	}
	
	public function zeta($p1, $p2, $p3, $p4 = 'Default1', $p5 = 1,
	                            $p6 = null) {
	
		self::$zetaExecuted = true;
		self::$zetaParams = array($p1, $p2, $p3, $p4, $p5, $p6);
	}
	
	public function fiveparameters($p1, $p2, $p3, $p4, $p5)
	{
	}
	
	public function post($p1, $p2, $p3, $p4 = 'default1')
	{
		self::$postExecuted = true;
		self::$postParams = array($p1, $p2, $p3, $p4);
	}
	
	public function postrequired()
	{
		self::$postRequiredExecuted = true;
	}
}

?>
