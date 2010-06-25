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

function smarty_block_a($params, $contents, $smarty)
{
	if ($contents !== NULL)
	{
		$stock = @$params['coorgStock'];
		unset($params['coorgStock']);
		$title = @$params['coorgTitle'];
		unset($params['coorgTitle']);
		$l = @$params['coorgLanguage'];
		unset($params['coorgLanguage']);
		$request = $params['request'];
		if ($request[0] != '#')
		{
			$urlParams = array($request);
			unset($params['request']);
			$urlParams = array_merge($urlParams, array_values($params));
			$url = CoOrg::createURL($urlParams, $l ? $l : null);
		}
		else
		{
			$url = $request;
		}
		
		$a = '<a href="'.htmlspecialchars($url).'"'.
		         ($title ? ' title="'.$title.'"' : '').'>';
		if ($stock)
		{
			$stockInfo = CoOrg::stocks($stock);
			$a .= '<img src="'.CoOrg::staticFile($stockInfo['img']).'"
			            alt="'.$stockInfo['alt'].'"
			            title="'.$stockInfo['title'].'"/>';
			if ($stockInfo['text'])
			{
				$a .= $stockInfo['text'];
			}
			else if ($contents)
			{
				$a .= $contents;
			}
		}
		else
		{
			$a .= $contents;
		}
		$a .= '</a>';
		return $a;
	}
}

?>
