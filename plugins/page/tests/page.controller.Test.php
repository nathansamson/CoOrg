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

class PageControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/page.dataset.xml';
	}
	
	public function testShow()
	{
		$this->request('page/show/some-page');
		
		$this->assertRendered('show');
		$this->assertVarSet('page');
	}
	
	public function testShowLanguage()
	{
		$this->request('nl/page/show/aabbcc');
		
		$this->assertRendered('show');
		$this->assertVarSet('page');
		$page = CoOrgSmarty::$vars['page'];
		$this->assertEquals('nl', $page->language);
	}
	
	public function testShowNotFound()
	{
		$this->request('nl/page/show/i-do-not-exist');
		$this->assertRendered('notfound');
		$this->assertFlashError('Page not found');
	}
}

?>
