{block name="content"}
	<title>{'Comments on %title'|_:$blog->title}</title>
	<subtitle>{Coorg::config()->get('site/title')}</subtitle>
	<author>
		{if $blog->author->profile}
			<name>{$blog->author->profile->firstName}</name>
			<uri>{CoOrg::createURL(array('user/profile/show', $blog->author->username))}</uri>
		{else}
			<name>{$blog->author->username}</name>
		{/if}
	</author>
	<id>{CoOrg::tagURI('blog/comments', $blog->language, $blog->datePosted, $blog->ID)}</id>
	
	{include file="comment-list.atom.part.tpl" comments=$blog->comments->filter(PropertySpamStatus::OK)}
{/block}
