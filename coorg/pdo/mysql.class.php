<?php

include_once 'coorg/pdo/itransformer.interface.php';

class MySQLQueryTransformer implements ISQLTransformer
{
	public function transform($q)
	{
		$q = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $q);
		$q = preg_replace('/CREATE TABLE (.*\(.*\))$/sm', 'CREATE TABLE $1 ENGINE=InnoDB COLLATE=UTF8_bin', $q);
		
		/* Work around limitations of MySQL and InnoDB... */
		$q = preg_replace('/VARCHAR\(([0-9]*)\) UNIQUE/', 'VARCHAR($1) COLLATE latin1_general_cs UNIQUE', $q);
		$q = preg_replace('/ID VARCHAR\(([0-9]*)\)/', 'ID VARCHAR($1) COLLATE latin1_general_cs', $q);
		
		return $q;
	}
}

class MySQLPDO extends GenericPDO
{
	public function __construct($dsn, $user, $pass)
	{
		parent::__construct($dsn, $user, $pass);
		$this->_transformer = new MySQLQueryTransformer();
	}
}

?>
