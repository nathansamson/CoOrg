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
/* The pagination implementation is based on ideas and initial code by
   Wouter Bolsterlee, outlined on
   http://uwstopia.nl/blog/2010/02/calculating-the-contents-of-fixed-size-pagination-controls
*/

abstract class Pager
{
	private $_queryString;
	private $_params;
	private $_page = 1;
	private $_elementsPerPage = 1;
	
	public function __construct($query, $params = array())
	{
		$this->_queryString = $query;
		$this->_params = $params;
	}

	public function execute($page, $elementsPerPage, $isSpecialFirstPage = false)
	{
		if (!$isSpecialFirstPage)
			$this->_page = $page;
		else
			$this->_page = -1;
		$this->_elementsPerPage = $elementsPerPage;
		$q = DB::prepare($this->getSelectQuery($page, $elementsPerPage));
		$q->execute($this->_params);		
		
		$elements = array();
		foreach ($q->fetchAll() as $row)
		{
			$elements[] = $this->fetch($row);
		}
		return $elements;
	}
	
	public function prev()
	{
		if ($this->_page > 1)
		{
			return $this->_page - 1;
		}
		else
		{
			return null;
		}
	}
	
	public function next()
	{
		$elements = $this->getNumberOfElements();
		$noPages = ceil($elements / $this->_elementsPerPage);
		if ($this->_page < $noPages)
		{
			if ($this->_page > 0)
				return $this->_page + 1;
			else
				return 1; // Special case for $isSpecialPage ($this->_page < 1)
		}
		else
		{
			return null;
		}
	}
	
	/* The pagination implementation is based on ideas and initial code by
       Wouter Bolsterlee, outlined on
       http://uwstopia.nl/blog/2010/02/calculating-the-contents-of-fixed-size-pagination-controls
    */
    /**
     *
     * This function works ok for $width >= 7 you should have at least (First gap prev current next gap last)
     * Their is also a special $width of 2 to view only the prev and the next (for example older / newer blog posts)
    */
	public function pages($width)
	{
		if ($this->_page == 0)
		{
			return null;
		}
		$elements = $this->getNumberOfElements();
		$noPages = ceil($elements / $this->_elementsPerPage);
	
		// Return early if no ellipsization is needed
		if ($width > $noPages)
		{
			$pages = array();
			for ($page = 1; $page <= $noPages; $page++)
			{
				$pages[] = array(
					'id' => $page,
					'current' => $this->_page == $page
				);
			}
			return $pages;
		}

		// Begin and end of range surrounding the current page
		$nSurrounding = ($width - 3) / 2;
		$begin = $this->_page - floor($nSurrounding);
		$end = $this->_page + ceil($nSurrounding);

		// Shift right within bounds
		if ($begin <= 2)
		{
			$offset = 2 - $begin;
			$begin += $offset;
			$end += $offset;
		}
		else if ($end >= $noPages - 1)
		{
			// Shift left within bounds
			$offset = $noPages - $end - 1;
			$begin += $offset;
			$end += $offset;
		}
		
		$pages = array();
		$pages[] = array(
			'id' => 1,
			'current' => $this->_page == 1
		);
		for ($page = $begin; $page <= $end; $page++)
		{
			if ($page == $begin && $page != 2)
			{
				/*if pages[1] != 2:
				pages[1] = 2 - pages[2]*/
				// Left ellipsization if needed (with size of gap as negative number)
				$pages[] = array(
					'id' => '...',
					'current' => false,
					'gap' => -(1 - $page)
				);
			}
			else if ($page == $end && $page != $noPages - 1)
			{
				/* Right ellipsization if needed
				if pages[-2] != n_pages - 1:
				pages[-2] = 1 + pages[-3] - n_pages*/
				// Right gap
				$pages[] = array(
					'id' => '...',
					'current' => false,
					'gap' => -($page - $noPages)
				);
			}
			else
			{
				// Normal case
				$pages[] = array(
					'id' => $page,
					'current' => $this->_page == $page
				);
			}
		}
		$pages[] = array(
			'id' => $noPages,
			'current' => $this->_page == $noPages
		);

		return $pages;
	}
	
	abstract protected function fetch($row);
	
	private function getSelectQuery($page, $elementsPerPage)
	{
		if ($page == 0) return $this->_queryString;
		return $this->_queryString . ' LIMIT ' . ($page-1)*$elementsPerPage . ', '.$elementsPerPage;
	}
	
	private function getNumberOfElements()
	{
		$qs = 'SELECT COUNT(*) AS cnt FROM (' . $this->_queryString . ') AS tmp';
		$q = DB::prepare($qs);
		$q->execute($this->_params);
		
		$row = $q->fetch();
		return (int)$row['cnt'];
	}
}

?>
