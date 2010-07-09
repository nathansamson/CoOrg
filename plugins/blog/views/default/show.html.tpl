{block name='title'}{$blog->title|escape}{/block}

{block name='content'}
{stylesheet file={'styles/blog.css'|static:'blog'}}
<article class="fullpage">
<header>
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
	<p>{'By %user @ %date'|_:($blog->authorID|linkyfy:'user/profile/show':$blog->authorID):($blog->timePosted|date_format)}</p>
</header>
{$blog->text|format:all}

{if UserSession::get() && Acl::isAllowed(UserSession::get()->username,'blog-writer')}
	{assign var=comments value=$blog->comments}
{else}
	{assign var=comments value=$blog->comments->filter(PropertySpamStatus::OK)}
{/if}

{if count($comments)}
	<h2 id="comments">{'Comments'|_}</h2>
	
	{foreach $comments as $comment}
		{if $comment->spamStatus == PropertySpamStatus::OK}
		<article class="comment" ID="comment{$comment->ID}">
		{else}
		<article class="comment moderation" ID="comment{$comment->ID}">
		{/if}
			{if !($blogCommentEdit && $blogCommentEdit->ID == $comment->ID)}
			<header>
				{if UserSession::get() && ($comment->authorID == UserSession::get()->username ||
				    Acl::isAllowed(UserSession::get()->username,'admin'))}
					<div class="page-actions">
						{if Acl::isAllowed(UserSession::get()->username,'blog-writer')}
							{if $comment->spamStatus == PropertySpamStatus::OK}
								{form request="blog/comment/spam" nobreaks id="comment_{$comment->ID}"}
									{input name=commentID value=$comment->ID}
								
									{input name=feedback type="select" options=$spamOptions nolabel}
									{input type="submit" stock="spam" nolabel}
								{/form}
							{else if $comment->spamStatus == PropertySpamStatus::UNKNOWN}
								{form request="blog/comment/spam" nobreaks id="comment_{$comment->ID}"}
									{input name=commentID value=$comment->ID}
								
									{input name=feedback type="select" options=$spamOptions nolabel}
									{input type="submit" stock="spam" nolabel}
								{/form}
								{button request="blog/comment/notspam"
									param_commentID=$comment->ID
									coorgStock="notspam"}{/button} 
							{else}
								{button request="blog/comment/notspam"
									param_commentID=$comment->ID
									coorgStock="notspam"}{/button}  
							{/if}
						{/if}
					
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
						<div class="author-avatar">
							<img src="{$comment->author->profile->avatar()}" />
						</div>
						{'By %name on %date'|_:($comment->author->username|linkyfy:'user/profile/show':$comment->author->username):($comment->timePosted|date_format:'Y-m-d H:i:s')}
					{else}
						<div class="author-avatar">
							<img src="{$comment->anonAuthor->avatar()}" />
						</div>
						{if $comment->anonAuthor->website}
							{'By %name on %date'|_:($comment->anonAuthor->name|linkyfy:'e':$comment->anonAuthor->website):($comment->timePosted|date_format:'Y-m-d H:i:s')}
						{else}
							{'By %name on %date'|_:$comment->anonAuthor->name:($comment->timePosted|date_format:'Y-m-d H:i:s')}
						{/if}
					{/if}
				</h2>
			</header>
			{$comment->comment|format:'small'}
			{else}
				<header>
				<h1>{$comment->title}</h1>
				<h2>
					{*{'By %name on %date'|_:$comment->author->username:($comment->timePosted|date_format:'Y-m-d H:i:s')}*}
				</h2>
				</header>
				{form request="blog/comment/update" instance=$blogCommentEdit id="comment_edit"}
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
</article>
{if $blog->allowComments()}
	<h2>
	{'Leave a reply'|_}
	</h2>
	{form request="blog/comment/save" instance=$blogComment id="comment_new"}
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
		
		{input for=comment label="Comment" type=textarea required editor=lite}
		
		{input type="submit" label="Post comment"}
	{/form}
{/if}
{/block}
