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
 * @property moderationEmail Email(t('Moderation Email'));
 * @property moderationTime Integer(t('Minimum time between emails'));
*/
class BlogConfig extends Model
{
	protected function __construct()
	{
		parent::__construct();
		$this->enableComments = CoOrg::config()->get('blog/enableComments');
		$this->enableCommentsFor = CoOrg::config()->get('blog/enableCommentsFor');
		$this->moderationEmail = CoOrg::config()->get('blog/moderation-email');
		$this->moderationTime = Coorg::config()->get('blog/moderation-time');
	}
	
	public function save()
	{
		parent::validate('');
		CoOrg::config()->set('blog/enableComments', $this->enableComments);
		CoOrg::config()->set('blog/enableCommentsFor', $this->enableCommentsFor);
		CoOrg::config()->set('blog/moderation-email', $this->moderationEmail);
		CoOrg::config()->set('blog/moderation-time', $this->moderationTime);
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
	
	public static function moderationTimeOptions()
	{
		return array(
			'1' => t('One day'),
			'2' => t('Two days'),
			'7' => t('A week')
		);
	}
}

?>
