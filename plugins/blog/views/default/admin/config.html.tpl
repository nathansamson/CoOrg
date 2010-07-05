{block name="title"}{'Configure blog'|_}{/block}

{block name="admin-content"}
	<h1>{'Configure blog'|_}</h1>

	{form request="admin/blog/configsave" instance=$blogConfig}
		{input for=enableComments label="Allow comments for new blogs" type=checkbox}
		{input for=enableCommentsFor  label="Allow comments for" options=$openForOptions type=select}
		
		{input for=moderationEmail label="Moderation email" type=email required size=wide}
		{input for=moderationTime label="Minimum time between 2 moderation mails" type=select required options=$moderationTimeOptions}
		
		{input type="submit" label="Save blog config"}
	{/form}
{/block}
