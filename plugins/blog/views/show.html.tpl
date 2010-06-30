{block name='title'}{$blog->title|escape}{/block}

{block name='head' append}
	<link rel="stylesheet" href="{'styles/blog.css'|static:'blog'}" />
{/block}

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

{if count($blog->comments)}
	<h2>{'Comments'|_}</h2>
	{foreach $blog->comments as $comment}
		<article class="comment" ID="comment{$comment->ID}">
			{if !($blogCommentEdit && $blogCommentEdit->ID == $comment->ID)}
			<header>
				{if $comment->authorID == UserSession::get()->username}
					<div class="page-actions">
						{a request="blog/comment/edit" 
						   ID=$comment->ID
						  	coorgStock="edit"}{/a}
						{button request="blog/comment/delete"
							param_ID=$comment->ID
							coorgStock="delete"}{/button}  	
					</div>
				{/if}
				<h1>{$comment->title}</h1>
				<h2>
					{'By %name on %date'|_:($comment->author->username|linkyfy:'user/profile/show':$comment->author->username):($comment->timePosted|date_format:'Y-m-d H:i:s')}
				</h2>
			</header>
			{$comment->comment|format:'none'}
			{else}
				<header>
				<h1>{$comment->title}</h1>
				<h2>
					{'By %name on %date'|_:$comment->author->username:($comment->timePosted|date_format:'Y-m-d H:i:s')}
				</h2>
				</header>
				{form request="blog/comment/update" instance=$blogCommentEdit}
					{input for=ID}
					
					{input for=comment label=comment type=textarea required}
					
					{input type="submit" label="Save comment"}
				{/form}
			{/if}
		</article>
	{/foreach}
{/if}
{if $blog->allowComments()}
	<h2>
	{if count($blog->comments)}
		{'Comment'|_}
	{else}
		{'Post first comment'|_}
	{/if}
	</h2>
	{form request="blog/comment/save" instance=$blogComment}
		{input value=$blog->ID name="blogID"}
		{input value=$blog->datePosted|date_format:'Y-m-d' name="blogDate"}
		{input value=$blog->language name="blogLanguage"}
		
		{input for=comment label=comment type=textarea required}
		
		{input type="submit" label="Comment"}
	{/form}
{/if}
{/block}
