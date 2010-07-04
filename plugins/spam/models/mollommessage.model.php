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
 * @property title String('Title');
 * @property body String('Content');
 * @property authorName String('Author Name');
 * @property authorWebsite URL('Author URL');
 * @property authorEmail Email('Author Email');
 * @property authorOpenID String('OpenID');
 * @property authorID String('Author ID');
*/
class MollomMessage extends Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function check()
	{
		self::prepare();
		return $this->internalCheck();
	}
	
	public static function feedback($sessID, $feedback)
	{
		self::prepare();
		return self::sendFeedback($sessID, $feedback);
	}
	
	private function sendFeedback($sessID, $feedback, $retry = true)
	{
		try
		{
			return Mollom::sendFeedback($sessID, $feedback);
		}
		catch (KeyNotSetException $e)
		{
			return false;
		}
		catch (InternalException $e)
		{
			return false;
		}
		catch (OutdatedServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? self::sendFeedback($sessID, $feedback, false) : false;
		}
		catch (NoServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? self::sendFeedback($sessID, $feedback, false) : false;
		}
	}
	
	private function internalCheck($retry = true)
	{
		if (Session::has('mollom/sessionid'))
		{
			$sessID = Session::get('mollom/sessionid');
		}
		else
		{
			$sessID = null;
		}
		try
		{
			$result = Mollom::checkContent($sessID, $this->title, $this->body, 
			                  $this->authorName, $this->authorWebsite,
			                  $this->authorEmail, $this->authorOpenID,
			                  $this->authorID);
			Session::set('mollom/sessionid', $result['session_id']);
			if ($result['spam'] == 'ham')
			{
				return PropertySpamStatus::OK;
			}
			else if ($result['spam'] == 'spam')
			{
				return PropertySpamStatus::SPAM;
			}
			else
			{
				return PropertySpamStatus::UNKNOWN;
			}
		}
		catch (KeyNotSetException $e)
		{
			return PropertySpamStatus::UNKNOWN;
		}
		catch (InternalException $e)
		{
			return PropertySpamStatus::UNKNOWN;
		}
		catch (OutdatedServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? $this->internalCheck(false) : PropertySpamStatus::UNKNOWN;
		}
		catch (NoServerListException $e)
		{
			CoOrg::config()->set('mollom/serverlist', Mollom::getServerList());
			CoOrg::config()->save();
			return $retry ? $this->internalCheck(false) : PropertySpamStatus::UNKNOWN;
		}
	}
	
	private static function prepare()
	{
		Mollom::setPublicKey(CoOrg::config()->get('mollom/public'));
		Mollom::setPrivateKey(CoOrg::config()->get('mollom/private'));
		Mollom::setServerList(CoOrg::config()->get('mollom/serverlist'));
	}
}

?>
