<?php

interface ICoOrgSmarty
{
	public function notice($notice);
	public function error($error);

	public function assign($key, $value);
	public function clearAssign($key);
	public function getVariable($key);

	public function display($tpl);
	public function fetch($tpl);
	
	public function saveState();
}

?>
