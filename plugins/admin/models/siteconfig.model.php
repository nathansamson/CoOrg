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
 * @property title String(t('Site Title')); required
 * @property subtitle String(t('SubTitle'));
 * @property siteAuthor String(t('Site Author')); required
 * @property siteContactEmail Email(t('Site Contact Email')); required
 * @property databaseConnection String(t('Database connection')); required
 * @property databaseUser String(t('Database user'));
 * @property databasePassword String(t('Database password'));
 * @property friendlyURL Bool(t('Friendly URLs'));
 * @property sitePath String(t('Site path'));
 * @property UUID String(t('Site Identifier'));
*/
class SiteConfig extends Model
{
	public function __construct()
	{
		parent::__construct();
		$config = CoOrg::config();
		$this->title = $config->get('site/title');
		$this->subtitle = $config->get('site/subtitle');
		$this->siteAuthor = $config->get('site/author');
		$this->siteContactEmail = $config->get('site/email');
		$this->databaseConnection = $config->get('dbdsn');
		$this->databaseUser = $config->get('dbuser');
		$this->databasePassword = $config->get('dbpass');
		$this->friendlyURL = true;
		$this->sitePath = $config->get('path');
		$this->UUID = $config->get('site/uuid');
	}
	
	public function save()
	{
		$this->validate('');
		$config = CoOrg::config();
		$config->set('site/title', $this->title);
		$config->set('site/subtitle', $this->subtitle);
		$config->set('site/author', $this->siteAuthor);
		$config->set('site/email', $this->siteContactEmail);
		$config->set('dbdsn', $this->databaseConnection);
		$config->set('dbuser', $this->databaseUser);
		$config->set('dbpass', $this->databasePassword);
		$this->friendlyURL = true;
		$config->set('path', $this->sitePath);
		$config->set('site/uuid', $this->UUID);
		$config->save();
	}
}

?>
