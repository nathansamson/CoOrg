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

class Textarea extends UserInput
{
	public function setSpecificParameters(&$params)
	{
		if ($size = self::getParameter($params, 'size'))
		{
			switch($size)
			{
				case 'small':
					$cols = 40;
					$rows = 2;
					break;
				case 'big':
					$cols = 60;
					$rows = 25;
					break;
				case 'wide':
					$cols = 60;
					$rows = 10;
					break;
				case 'small-wide':
					$cols = 60;
					$rows = 4;
					break;
			}
			$this->_inputAttributes->cols = $cols;
			$this->_inputAttributes->rows = $rows;
		}
		if ($editor = self::getParameter($params, 'editor'))
		{
			$this->_inputClasses[] = $editor . '-editor';
			
			/*
	    	 * When required=true for an empty textarea, ckeditor does not get
	    	 * the chanche to fill the textarea with data in browsers that
	    	 * validates the input. (webkit as of this writing) 
	    	 * (and people does not get the chance to post
	    	 *  comments, blogposts, pages and more).
	    	*/
			$this->_inputAttributes->required = false;
		}
	}

	public function render()
	{
		$textarea = '<textarea name="'.$this->_name.'" id="'.$this->getID().'"';
		$textarea .= $this->renderOptions();
		$textarea .= '>'.$this->_value.'</textarea>';
		return $this->renderLabel() . $textarea;
	}
}

?>
