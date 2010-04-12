<?php

abstract class AsideController
{

	private $_viewsPath;
	private $_smarty;
	private $_variablesSetByMe = array();
	
	public function __construct($smarty, $viewsPath)
	{
		$this->_smarty = $smarty;
		$this->_viewsPath = $viewsPath;
	}
	
	abstract function run($request);
	
	protected function render($tpl)
	{
		$s = $this->_smarty->fetch($this->_viewsPath.$tpl.'.html.tpl');
		foreach ($this->_variablesSetByMe as $var)
		{
			$this->_smarty->clearAssign($var);
		}
		return $s;
	}

	final public function __set($var, $value)
	{
		if ($this->_smarty->getVariable($var) instanceof Undefined_Smarty_Variable)
		{
			$this->_variablesSetByMe[] = $var;
			$this->_smarty->assign($var, $value);
		}
		else
		{
			throw new Exception('Can not overwrite template variable!');
		}
	}
}

?>
