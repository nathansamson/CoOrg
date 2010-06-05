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

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'coorg/db.class.php';

abstract class CoOrgModelTest extends PHPUnit_Extensions_Database_TestCase
{
	private $_dataset = '';
	private static $_classes = array();

	public function __construct()
	{
		$refl = new ReflectionClass(get_class($this));
		$this->_dataset = dirname($refl->getFileName()).'/'.$refl->getConstant('dataset');
	}

	protected function getConnection()
	{
		 return $this->createDefaultDBConnection(DB::pdo(), 'testdb');
	}
	
	protected function getDataSet()
	{
		return $this->createFlatXMLDataSet($this->_dataset);
	}
	
	protected function getSetUpOperation()
	{
		if (DB::acceptTransactions())
		{
			return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
		}
		else
		{
			return new PHPUnit_Extensions_Database_Operation_Null();
		}
	}
	
	public function setUp()
	{
		if (DB::acceptTransactions())
		{
			if (!array_key_exists(get_class($this), self::$_classes))
			{
				// Clean insert
				parent::setUp();
				self::$_classes[get_class($this)] = true;
			}
			DB::beginTransaction();
		}
		else
		{
			parent::setUp();
		}
	}
	
	public function tearDown()
	{
		if (DB::acceptTransactions())
		{
			DB::rollback();
		}
		else
		{
			parent::tearDown();
		}
	}
}

?>
