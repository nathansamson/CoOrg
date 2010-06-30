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
	private static $_oldDataset = null;

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
		if (self::$_oldDataset == null)
		{
			return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
		}
		else if (self::$_oldDataset != $this->_dataset)
		{
			$truncate = PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE(true);
			$truncate->execute($this->getConnection(),
			                   $this->createFlatXMLDataSet(self::$_oldDataset));
			
			return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
		}
		else
		{
			return PHPUnit_Extensions_Database_Operation_Factory::NONE();
		}
	}
	
	public function setUp()
	{
		parent::setUp();
		DB::beginTransaction();
	}
	
	public function tearDown()
	{
		DB::rollback();
		parent::tearDown();
		self::$_oldDataset = $this->_dataset;
	}
}

?>
