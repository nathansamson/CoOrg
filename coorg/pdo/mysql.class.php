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
