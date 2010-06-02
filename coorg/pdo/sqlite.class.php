<?php

class SQLItePDO extends GenericPDO
{
	public function __construct($dsn)
	{
		parent::__construct($dsn);
		$this->exec('PRAGMA foreign_keys = ON;');
	}
}

?>
