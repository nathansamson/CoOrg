<?php

interface ICoOrgSmarty
{
	public function notice($notice);
	public function error($error);
	public function assign($key, $value);
	public function display($tpl);
	public function saveState();
}

?>
