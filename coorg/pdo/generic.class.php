<?php

class GenericPDO extends PDO
{
	protected $_transformer = null;

	public function prepare($queryString)
	{
		$q = parent::prepare(self::transformQuery($queryString));
		$q->setFetchMode(PDO::FETCH_ASSOC);
		return $q;
	}
	
	private function transformQuery($q)
	{
		if ($this->_transformer)
		{
			return $this->_transformer->transform($q);
		}
		else
		{
			return $q;
		}
	}
}

?>
