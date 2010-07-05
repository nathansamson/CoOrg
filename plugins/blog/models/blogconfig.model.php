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

/**
 * @property enableComments Bool(t('Enable comments'));
 * @property enableCommentsFor Integer(t('Enable comments for'));
*/
class BlogConfig extends Model
{
	protected function __construct()
	{
		parent::__construct();
		$this->enableComments = CoOrg::config()->get('blog/enableComments');
		$this->enableCommentsFor = CoOrg::config()->get('blog/enableCommentsFor');
	}
	
	public function save()
	{
		parent::validate('');
		CoOrg::config()->set('blog/enableComments', $this->enableComments);
		CoOrg::config()->set('blog/enableCommentsFor', $this->enableCommentsFor);
		CoOrg::config()->save();
	}
	
	public static function get()
	{
		return new BlogConfig;
	}
	
	public static function openForOptions()
	{
		return array(
			'0' => t('Unlimited'),
			'7' => t('One week'),
			'14' => t('a forthnight'),
			'30' => t('One month'),
			'365' => t('One year')
		);
	}
}

?>
