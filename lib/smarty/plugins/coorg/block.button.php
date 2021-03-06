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

function smarty_block_button($params, $contents, $smarty)
{
	if ($contents !== NULL)
	{
		$confirm = @$params['coorgConfirm'];
		unset($params['coorgConfirm']);
		
		if ($confirm)
		{
			$confirm = htmlspecialchars($confirm);
			$confirm = str_replace('\'', '\\\'', $confirm);
		}
		
		$form = '<form action="'.CoOrg::createURL(explode('/', $params['request'])).
		              '" method="post" class="normal-post-url"'.
		              ($confirm ? '  onsubmit="return confirm(\''.$confirm.'\');"' : '').
		              '>';
		
		foreach ($params as $key => $value)
		{
			if (strpos($key, 'param_') === 0)
			{
				$name = substr($key, 6);
				$form .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'"/>';
			}
		}
		
		$stock = @$params['coorgStock'];
		unset($params['coorgStock']);
		if ($stock)
		{
			$stockInfo = CoOrg::stocks($stock);
			$contents = '<img src="'.CoOrg::staticFile($stockInfo['img']).'"
			            alt="'.$stockInfo['alt'].'"
			            title="'.$stockInfo['title'].'"/>';
			if ($stockInfo['text'])
			{
				$contents = $stockInfo['text'];
			}
		}
		
		$form .= '<button type="submit">'.$contents.'</button>';
		$form .= '</form>';
		return $form;
	}
}

?>
