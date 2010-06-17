{block name="title"}{'Manage blog'|_}{/block}

{block name="content"}
	<h1>{'Manage blog'|_}</h1>
	
	<table>
		{foreach $blogs as $blog}
			<tr>
				<td>
					{a request="blog/edit"
					   year=$blog->datePosted|date_format:'Y'
					   month=$blog->datePosted|date_format:'m'
					   day=$blog->datePosted|date_format:'d'
					   blog=$blog->ID}{$blog->title|escape}{/a}
				</td>
				<td>
					{$blog->text|format:none|truncate:100}
				</td>
				<td>
					{foreach $blog->translations() as $translation}
						{a request="blog/edit"
						   year=$blog->datePosted|date_format:'Y'
						   month=$blog->datePosted|date_format:'m'
						   day=$blog->datePosted|date_format:'d'
						   blog=$translation->ID
						   language=$translation->language}{$translation->language}{/a}
					{/foreach}
				</td>
				<td>
					{a request="blog/edit"
					   coorgStock="edit"
					   year=$blog->datePosted|date_format:'Y'
					   month=$blog->datePosted|date_format:'m'
					   day=$blog->datePosted|date_format:'d'
					   blog=$blog->ID}{/a}
				</td>
			</tr>
		{/foreach}
	</table>
	
	{pager pager=$blogpager
		request="admin/blog/index"
		page=".*."
		width=9}
{/block}