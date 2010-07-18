{stylesheet file="{'comments.css'|static:'comments'}"}

{if $comment->spamStatus == PropertySpamStatus::OK}
<article class="comment" ID="comment{$comment->ID}">
{else}
<article class="comment moderation" ID="comment{$comment->ID}">
{/if}
	{if !($editComment && $editComment->ID == $comment->ID)}
	<header>
		{if Acl::owns(UserSession::get()->username, $comment) || Acl::owns(UserSession::get()->username, $commentOn)}
			<div class="page-actions">
				{if Acl::owns(UserSession::get()->username, $commentOn)}
					{if $comment->spamStatus == PropertySpamStatus::OK}
						{form request=$commentRequests->spam nobreaks id="comment_{$comment->ID}"}
							{input name=commentID value=$comment->ID}
						
							{input name=feedback type="select" options=$spamOptions nolabel}
							{input type="submit" stock="spam" nolabel}
						{/form}
					{else if $comment->spamStatus == PropertySpamStatus::UNKNOWN}
						{form request=$commentRequests->spam nobreaks id="comment_{$comment->ID}"}
							{input name=commentID value=$comment->ID}
							{input name=from value=$coorgRequest}
						
							{input name=feedback type="select" options=$spamOptions nolabel}
							{input type="submit" stock="spam" nolabel}
						{/form}
						{button request=$commentRequests->notspam
							param_commentID=$comment->ID
							param_from=$coorgRequest
							coorgStock="notspam"}{/button} 
					{else}
						{button request=$commentRequests->notspam
							param_commentID=$comment->ID
							param_from=$coorgRequest
							coorgStock="notspam"}{/button}  
					{/if}
				{/if}
			
				{a request=$commentRequests->edit
				   ID=$comment->ID
				  	coorgStock="edit"}{/a}
				{button request=$commentRequests->delete
					param_ID=$comment->ID
					param_from=$coorgRequest
					coorgStock="delete"}{/button}
			</div>
		{/if}
		{if !$notitle}
			<h1>{$comment->title}</h1>
		{/if}
		<h2>
			{if $comment->author}
				{if $comment->author->profile}
				<div class="author-avatar">
					<img src="{$comment->author->profile->avatar()}" />
				</div>
				{/if}
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
		{if !$notitle}
			<h1>{$comment->title}</h1>
		{/if}
		<h2>
			{if $comment->author}
				{if $comment->author->profile}
				<div class="author-avatar">
					<img src="{$comment->author->profile->avatar()}" />
				</div>
				{/if}
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
		{form request=$commentRequests->update instance=$editComment id="comment_edit"}
			{input for=ID}
			
			{if $editComment->anonAuthor}
				<fieldset>
					<legend>{'Personal information'|_}</legend>
					{subform instance=$editComment->anonAuthor}
						{input for=name label="Name" required}
						{input for=email label="Email" required type=email}
						{input for=website label="Website" type=url size=wide}
						{input for=IP label="IP" size=wide readonly}
					{/subform}
				</fieldset>
			{/if}
			
			{if !$notitle}
				{input for=title label="Title" class=title required}
			{/if}	
			{input for=comment label=comment type=textarea required editor=lite}
			
			{input type="submit" label="Save comment"}
		{/form}
	{/if}
</article>
