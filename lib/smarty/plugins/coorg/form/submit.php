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

class SubmitButton extends LabeledFormElement
{
	private $_stock;
	private $_tabindex;
	private $_disabled;

	public function setSpecificParameters(&$params)
	{
		if ($stock = self::getParameter($params, 'stock'))
		{
			$this->_stock = $stock;
		}
	}

	public function render()
	{
		if (! $this->_stock)
		{
			return '<input type="submit" name="'.$this->_name.'" value="'.$this->_label.'"'.$this->renderOptions().'/>';
		}
		else
		{
			$stockInfo = CoOrg::stocks($this->_stock);
			$button = '<img src="'.CoOrg::staticFile($stockInfo['img']).'"
			                alt="'.$stockInfo['alt'].'"
			                title="'.$stockInfo['title'].'"/>';
			return '<button type="submit" name="'.$name.'" value="_"'.$this->renderOptions().'>'.$button.'</button>';
		}
	}
	
	public function tabindex($i)
	{
		$this->_tabindex = $i;
	}
	
	public function disable()
	{
		$this->_disabled = true;
	}
	
	private function renderOptions()
	{
		$s = '';
		if ($this->_tabindex)
		{
			$s .= ' tabindex="'.$this->_tabindex.'"';
		}
		if ($this->_disabled)
		{
			$s .= ' disabled="disabled"';
		}
		return $s;
	}
}

?>
