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
				{if UserSession::get() && ($comment->authorID == UserSession::get()->username ||
				    Acl::isAllowed(UserSession::get()->username,'admin'))}
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
					{if $comment->author}
						{'By %name on %date'|_:($comment->author->username|linkyfy:'user/profile/show':$comment->author->username):($comment->timePosted|date_format:'Y-m-d H:i:s')}
					{else if $comment->anonAuthor->website}
						{'By %name on %date'|_:($comment->anonAuthor->name|linkyfy:'e':$comment->anonAuthor->website):($comment->timePosted|date_format:'Y-m-d H:i:s')}
					{else}
						{'By %name on %date'|_:$comment->anonAuthor->name:($comment->timePosted|date_format:'Y-m-d H:i:s')}
					{/if}
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
					
					{if $anonProfileEdit}
						<fieldset>
							<legend>{'Personal information'|_}</legend>
							{subform instance=$anonProfileEdit}
								{input for=name label="Name" required}
								{input for=email label="Email" required type=email}
								{input for=website label="Website" type=url size=wide}
								{input for=IP label="IP" size=wide readonly}
							{/subform}
						</fieldset>
					{/if}
					
					{input for=comment label=comment type=textarea required editor=lite}
					
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
		
		{if $anonProfile}
			<fieldset>
				<legend>{'Personal information'|_}</legend>
				{subform instance=$anonProfile}
					{input for=name label="Name" required}
					{input for=email label="Email" required type=email}
					{input for=website label="Website" type=url size=wide}
				{/subform}
			</fieldset>
		{/if}
		
		{input for=comment label=comment type=textarea required editor=lite}
		
		{input type="submit" label="Comment"}
	{/form}
{/if}
{/block}
