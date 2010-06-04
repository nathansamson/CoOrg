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

class Mail implements IMail
{
	public static $sentMails = array();
	
	private $_from;
	private $_subject;
	private $_to;
	private $_smarty;

	public function __construct($smarty)
	{
		$this->_smarty = $smarty;
	}
	
	public function to($email)
	{
		$this->_to = $email;
		return $this;
	}
	
	public function subject($subject)
	{
		$this->_subject = $subject;
		return $this;
	}
	
	public function send($tpl)
	{
		$content = $this->_smarty->fetch($tpl.'.mail.plain.tpl');
		mail($this->_to, $this->_subject, $content);
	}
	
	public function from($email)
	{
		$this->_from = $email;
		return $this;
	}
	
	public function __set($var, $value)
	{
		$this->_smarty->assign($var, $value);
	}
}

?>
