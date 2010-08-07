<section>
	<ul class="tag-cloud">
		{foreach $tagcloud as $tag}
			<li class="tag-{$tag->size}">
				{a request="search/tag"
				   tag={$tag->name}}
				   {$tag->name}
				{/a}
			</li>
		{/foreach}
	</ul>
</section>
