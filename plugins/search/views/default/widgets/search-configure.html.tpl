{block name="widget-title"}
	{'Search'|_}
{/block}

{block name="widget-configure"}
	{form request="admin/layout/update"}
		{input name=panelID value=$panelID}
		{input name=widgetID value=$widgetID}
		
		{foreach $allIncludes as $name=>$include}
			{if in_array($name, $includeSearch)}
				{input type="checkbox" name="includes[]" value=$name label={$include->title} checked}
			{else}
				{input type="checkbox" name="includes[]" value=$name label={$include->title}}
			{/if}
		{/foreach}
		
		{input type=submit label="coorg|Save"}
	{/form}
{/block}

{block name="widget-preview"}
	{form id="preview-search"}
		{input type="search" placeholder={"Search"|_} name="s" nolabel}
	{/form}
{/block}
