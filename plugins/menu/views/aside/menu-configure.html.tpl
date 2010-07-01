{block name="widget-title"}
	Menu
{/block}
{block name="widget-preview"}
<ol class="menu">
	<li>
			<a href="#">Lorem</a>
	</li>
	<li>
			<a href="#">Ipsum</a>
	</li>
	<li>
			<a href="#">Dolor</a>
	</li>
</ol>
{stylesheet file={'styles/menu.css'|static:menu}}
{/block}

{block name="widget-configure"}
	{form request="admin/layout/update"}
		{input name=panelID value=$panelID}
		{input name=widgetID value=$widgetID}
		
		{input name=menu value=$menu options=$menus type=select label="Menu"}
		
		{input type=submit label="Save"}
	{/form}
{/block}
