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

class SQLIteTransformer implements ISQLTransformer
{
	public function transform($q)
	{
		$q = preg_replace('/YEAR\(([\w]*)\)/U', 'strftime(\'%Y\', $1)', $q);
		$q = preg_replace('/MONTH\(([\w]*)\)/U', 'strftime(\'%m\', $1)', $q);
		return $q;
	}
}

class SQLItePDO extends GenericPDO
{
	public function __construct($dsn)
	{
		parent::__construct($dsn);
		$this->exec('PRAGMA foreign_keys = ON;');
		$this->_transformer = new SQLIteTransformer;
	}
}

?>
