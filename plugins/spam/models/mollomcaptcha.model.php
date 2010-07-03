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

class MollomInvalidConfigCaptcha {}

class MollomCaptcha
{
	public $type;
	public $url;

	private function __construct($type, $url)
	{
		$this->type = $type;
		$this->url = $url;
	}

	public static function create()
	{
		Session::set('mollom/type', 'image');
		return self::createCaptcha();
	}
	
	public static function refresh($type = null)
	{
		if ($type == null)
		{
			$type = Session::get('mollom/type');
		}
		else
		{
			Session::set('mollom/type', $type);
		}
		return self::createCaptcha();
	}
	
	public static function check($code)
	{
		self::prepare();
		try
		{
			if (Mollom::checkCaptcha(Session::get('mollom/sessionid'), $code))
			{
				Session::delete('mollom/sessionid');
				return null;
			}
			else
			{
				return self::createCaptcha();
			}
		}
		catch (Exception $e) 
		{
			return new MollomInvalidConfigCaptcha;
		}
	}
	
	private function createCaptcha()
	{
		if (Session::has('mollom/sessionid'))
		{
			$sessID = Session::get('mollom/sessionid');
		}
		else
		{
			$sessID = null;
		}
		$type = Session::get('mollom/type');
		self::prepare();
		$info = self::getCaptcha($sessID, $type);
		if ($info != null)
		{
			Session::set('mollom/sessionid', $info['session_id']);
			return new MollomCaptcha($type, $info['url']);
		}
		else
		{
			return new MollomInvalidConfigCaptcha;
		}
	}
	
	private function getCaptcha($sessID, $type, $retry = true)
	{
		try
		{
			if ($type == 'image')
			{
				$info = Mollom::getImageCaptcha($sessID);
			}
			else
			{
				$info = Mollom::getAudioCaptcha($sessID);
			}
			return $info;
		}
		catch (KeyNotSetException $e)
		{
			return null;
		}
		catch (InternalException $e)
		{
			return null;
		}
		catch (OutdatedServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? self::getCaptcha($sessID, $type, false) : null;
		}
		catch (NoServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? self::getCaptcha($sessID, $type, false) : null;
		}
	}
	
	private function prepare()
	{
		Mollom::setPublicKey(CoOrg::config()->get('mollom/public'));
		Mollom::setPrivateKey(CoOrg::config()->get('mollom/private'));
		Mollom::setServerList(CoOrg::config()->get('mollom/serverlist'));
	}
}

?>
