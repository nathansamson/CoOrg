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

class GenericModelVariant implements IPropertyVariant
{
	private $_p;
	private $_i = false;
	private $_c;
	private $_k;
	
	private function __construct(IProperty $p, $class, $key)
	{
		$this->_p = $p;
		$this->_c = $class;
		$this->_k = $key;
	}
	
	public function get()
	{
		if ($this->_i === false)
		{
			$class = $this->_c;
			$this->_i = $class::get($this->_p->get());
		}
		return $this->_i;
	}
	
	public function set($i)
	{
		$this->_i = $i;
		$key = $this->_k;
		$this->_p->set($i->$key);
	}
	
	public function update()
	{
		$this->_i = false;
	}
	
	public static function instance(IProperty $p, $args)
	{
		return new GenericModelVariant($p, $args[0], $args[1]);
	}
}


?>
