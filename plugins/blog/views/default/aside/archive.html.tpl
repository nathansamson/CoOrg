<section>
<h1>{'Archive'|_}</h1>

<ol class="archive">
	{assign var=lastyear value="0"}
	{foreach $blogArchive as $archive}
		{if $lastyear == "0"}
			<li>
				<h2>{a request="blog/archive"
			           year=$archive->year}{$archive->year}{/a}</h2>
				<ol>
		{elseif $archive->year != $lastyear}
				</ol>
			</li>
			<li>
				<h2>{a request="blog/archive"
			           year=$archive->year}{$archive->year}{/a}</h2>
				<ol>
		{/if}
		{assign var=lastyear value=$archive->year}
		<li>
			{a request="blog/archive"
			   year=$archive->year
			   month=$archive->month}{$archive->month|date_format:'_month'}<span class="count">({$archive->posts})</span>{/a}</li>
	{/foreach}
		</ol>
	</li>
</ol>
</section>

{stylesheet file={'styles/blog.css'|static:'blog'}}
