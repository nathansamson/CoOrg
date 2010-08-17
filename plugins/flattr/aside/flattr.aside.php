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

class FlattrFlattrWidget extends ConfigurableWidgetController
{
	public function run($widgetParams, $orient, $request)
	{
		if (Flattr::needsWidget($request))
		{
			$args = func_get_args();
			array_shift($args); // $widgetParams
			array_shift($args); // $orient
			$url = CoOrg::createFullURL($args);
			$widget = Flattr::widget($this, $request);
			$this->flattrTitle = $widget->title;
			$this->flattrDescription = $widget->description;
			$this->flattrLanguage = $widget->language;
			$this->flattrTags = $widget->tags;
			$this->flattrCategory = $widget->category;
			$this->flattrUID = $widgetParams['uid'];
			$this->flattrButton = $orient == CoOrg::PANEL_ORIENT_VERTICAL ? 'default' : 'compact';
			$this->flattrLink = $url;
			return $this->render('flattr-button');
		}
	}
	
	public function preview($widgetParams, $orient)
	{
		$this->buttonType = $orient == CoOrg::PANEL_ORIENT_VERTICAL ? 'default' : 'compact';
		return $this->renderPreview('flattr-button-preview');
	}
	
	public function configure($widgetParams, $orient)
	{
		$this->buttonType = $orient == CoOrg::PANEL_ORIENT_VERTICAL ? 'default' : 'compact';
		$this->uid = $widgetParams['uid'];
		return $this->renderConfigure('flattr-button-configure');
	}
}

?>
