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

function smarty_helper_pager_generateLink($params, $page)
{
	foreach ($params as &$p)
	{
		if ($p == '.*.')
		{
			$p = $page;
		}
	}
	return CoOrg::createURL(array_values($params));
}

function smarty_function_pager($params)
{
	$pager = $params['pager'];
	unset($params['pager']);
	
	$next = $params['coorgNext'] ? $params['coorgNext'] : '➡';
	$prev = $params['coorgPrev'] ? $params['coorgPrev'] : '⬅';
	$width = $params['coorgWidth'] ? $params['coorgWidth'] : 7;
	
	unset($params['coorgNext']);
	unset($params['coorgPrev']);
	unset($params['coorgWidth']);
	
	$ol = '<ol class="pager">';
	$empty = true;
	if ($pager->prev())
	{
		$empty = false;
		$ol .= '<li class="prev"><a href="'.smarty_helper_pager_generateLink($params, $pager->prev()).'">'.$prev.'</a></li>';
	}
	else
	{
		$ol .= '<li class="prev"><span class="gap">'.$prev.'</span></li>';
	}
	
	foreach ($pager->pages($width) as $page)
	{
		if ($page['id'] != '...')
		{
			$ol .= '<li'.($page['current'] ? ' class="current"' : '').'><a href="'.smarty_helper_pager_generateLink($params, $page['id']).'">'.$page['id'].'</a></li>';
		}
		else
		{
			$ol .= '<li class="prev"><span class="gap">'.$page['id'].'</span></li>';
		}
	}
	
	if ($pager->next())
	{
		$empty = false;
		$ol .= '<li class="prev"><a href="'.smarty_helper_pager_generateLink($params, $pager->next()).'">'.$next.'</a></li>';
	}
	else
	{
		$ol .= '<li class="prev"><span class="gap">'.$next.'</span></li>';
	}
	
	$ol .= '</ol>';
	
	if (!$empty)
	{
		return $ol;
	}
}

?>
