{block name="title"}{'Configure blog'|_}{/block}

{block name="admin-content"}
	<h1>{'Configure blog'|_}</h1>

	{form request="admin/blog/configsave" instance=$blogConfig}
		{input for=enableComments label="Allow comments for new blogs" type=checkbox}
		{input for=enableCommentsFor  label="Allow comments for" options=$openForOptions type=select}
		
		{input type="submit" label="Save blog config"}
	{/form}
{/block}
