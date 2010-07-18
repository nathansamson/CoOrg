{block name='title'}{$blog->title|escape}{/block}

{block name='content'}
{stylesheet file={'styles/blog.css'|static:'blog'}}
<article class="fullpage">
<header>
	{if Acl::owns(UserSession::get()->username, $blog)}
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
	<p>{'By %user @ %date'|_:($blog->authorID|linkyfy:'user/profile/show':$blog->authorID):($blog->timePosted|date_format)}</p>
</header>
{$blog->text|format:all}


{if Acl::owns(UserSession::get()->username, $blog)}
	{assign var=comments value=$blog->comments}
{else}
	{assign var=comments value=$blog->comments->filter(PropertySpamStatus::OK)}
{/if}
{if count($comments)}
	<h2>{'Replies'}</h2>
	{foreach $comments as $comment}
		{foreign file="comment.html.tpl" module="comments" comment=$comment
		         commentOn=$blog notitle="notitle"}
	{/foreach}
{/if}

<h2>
{'Leave a reply'|_}
</h2>
{foreign file="create.html.tpl" module="comments" commentOn=[
                                    'blogID' => $blog->ID,
                                    'blogDate' => $blog->datePosted|date_format:'Y-m-d',
                                    'blogLanguage' => $blog->language]
          notitle="notitle"}

{/block}
