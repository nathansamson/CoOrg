{block name="title"}
	{'All tags'|_}
{/block}

{block name="content"}
	{stylesheet file={'styles/tagcloud.css'|static:'search'}}

	<h1>{'All tags'|_}</h1>

	<ul class="tag-cloud">
		{foreach $tagcloud as $tag}
			<li class="tag-b{$tag->size}">
				{a request="reportages/tag"
				   tag={$tag->name}}
				   {$tag->name}
				{/a}
			</li>
		{/foreach}
	</ul>
{/block}
