{block name='title'}{$blog->title|escape}{/block}

{block name='content'}
{if Acl::isAllowed(UserSession::get()->username,'admin')}
	<span class="page-actions">
	{a request="blog/edit"
	   year=$blog->year
	   month=$blog->month
	   day=$blog->day
	   id=$blog->ID
	   language=$blog->language
	   coorgStock="edit"}{/a}
	</span>
{/if}

<h1>{$blog->title|escape}</h1>
{$blog->text|format:all}
{/block}
