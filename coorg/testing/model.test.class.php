<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'coorg/db.class.php';

abstract class CoOrgModelTest extends PHPUnit_Extensions_Database_TestCase
{
	protected $_dataset = '';
	private static $_classes = array();

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
