{block name="widget-title"}
	{'Flattr this!'|_}
{/block}


{block name="widget-configure"}
	{form request="admin/layout/update"}
		{input name=panelID value=$panelID}
		{input name=widgetID value=$widgetID}
		
		{input value=$uid label="Flattr UID" placeholder="12345" name="uid"}
		
		{input type=submit label="coorg|Save"}
	{/form}
{/block}

{block name="widget-preview"}
	<a href="#"><img src="{"flattr-{$buttonType}.png"|static:'flattr'}" alt="Flattr" /></a>
{/block}
