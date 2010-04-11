<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'coorg/db.class.php';

abstract class CoOrgModelTest extends PHPUnit_Extensions_Database_TestCase
{
	protected $_dataset = '';

	protected function getConnection()
	{
		 return $this->createDefaultDBConnection(DB::pdo(), 'testdb');
	}
	
	protected function getDataSet()
	{
		return $this->createFlatXMLDataSet($this->_dataset);
	}
	
	public function setUp()
	{
		if (DB::acceptTransactions())
		{
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
