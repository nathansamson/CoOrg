{foreach $comments as $comment}
	<entry>
		<title>{$comment->title|escape}</title>
		<link href="{CoOrg::createFullURL(array('blog/show', $blog->year,
		                                         $blog->month, $blog->day,
		                                         $blog->ID), CoOrg::getLanguage(), $comment->ID)}" />
		<updated>{$comment->timePosted|date_format:'c'}</updated>
		<published>{$comment->timePosted|date_format:'c'}</published>
		<id>{CoOrg::tagURI('blog/comment', $comment->ID)}</id>
		<summary type="html">{$comment->comment|format:none|truncate:100|escape}</summary>
		<content type="html">
				{$comment->comment|format:small|escape}
		</content>
		
		<author>
			{if $comment->author && $comment->author->profile}
				<name>
					{if $comment->author->profile->firstName || $comment->author->profile->lastName}
						{$comment->author->profile->firstName} {$comment->author->profile->lastName}
					{else}
						{$comment->author->username}
					{/if}
				</name>
				<uri>{CoOrg::createFullURL(array('user/profile/show', $comment->author->username))}</uri>
			{else if $comment->author}
				<name>{$comment->author->username}</name>
			{else}
				<name>{$comment->anonAuthor->name}</name>
				{if $comment->anonAuthor->website}
					<uri>{$comment->anonAuthor->website}</uri>
				{/if}
			{/if}
		</author>
	</entry>
{/foreach}
