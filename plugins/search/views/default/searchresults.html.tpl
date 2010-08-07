{block name="title"}{'Search results'|_}{/block}

{block name="content"}
	<h1>{'Search results'|_}</h1>
	{form request="search" id="search-results"}
		{input name=s type=search value=$searchQuery placeholder={'Search'|_} nolabel}
		
		{foreach $searchIncludes as $i}
			{input name="i[]" value=$i}
		{/foreach}
	{/form}
	

	{foreach $searchResults as $search}
		{assign value=$search->results->execute(1, 10) var=results}
		{if ($results)}
			{assign var=hasResults value=true}
			<h1>{$result->title}</h1>
			{foreign file=$search->file module=$search->module searchResults=$results}
		{/if}
	{/foreach}
	
	{if !$hasResults}
		<p class="notice">
			{'Your search query "%query" did not return any results'|_:$searchQuery}
		</p>
	{/if}
{/block}
