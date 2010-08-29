{block name="title"}{'Tagged'|_}{/block}

{block name="content"}
	<h1>{'Search results'|_}</h1>
	{foreach $searchResults as $search}
		{assign value=$search->results->execute(1, 10) var=results}
		{if ($results)}
			{assign var=hasResults value=true}
			<h1>{$result->title}</h1>
			{foreign file=$search->file module=$search->module searchResults=$results}
		{/if}
	{/foreach}
{/block}
