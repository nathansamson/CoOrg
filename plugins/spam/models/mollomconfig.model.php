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

if (!defined('COORG_UNITTEST'))
{
	require_once 'lib/mollom.php';
}
else
{
	require_once 'plugins/spam/mollom.testing.php';
}

/**
 * @property publicKey String(t('Public Key')); required
 * @property privateKey String(t('Private Key')); required
*/
class MollomConfig extends Model
{
	protected function __construct()
	{
		parent::__construct();
		$this->publicKey = CoOrg::config()->get('mollom/public');
		$this->privateKey = CoOrg::config()->get('mollom/private');
	}
	
	public function save()
	{
		parent::validate('');
		Mollom::setPublicKey($this->publicKey);
		Mollom::setPrivateKey($this->privateKey);
		Mollom::setServerList(CoOrg::config()->get('mollom/serverlist'));
		try
		{
			if (!Mollom::verifyKey())
			{
				$this->publicKey_error = t('Invalid keys');
				throw new ValidationException($this);
			}
		}
		catch (ServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			if (!Mollom::verifyKey())
			{
				$this->publicKey_error = t('Invalid keys');
				CoOrg::config()->save(); // Save the new serverlist
				throw new ValidationException($this);
			}
		}
		CoOrg::config()->set('mollom/public', $this->publicKey);
		CoOrg::config()->set('mollom/private', $this->privateKey);
		CoOrg::config()->save();
	}
	
	public static function get()
	{
		return new MollomConfig;
	}
}

?>
