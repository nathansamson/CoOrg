{block name="content"}
	<title>{"Unmoderated comments for %title"|_:{Coorg::config()->get('site/title')}}</title>
	<subtitle>{Coorg::config()->get('site/subtitle')}</subtitle>
	<author>
		<name>{Coorg::config()->get('site/author')}</name>
		<email>{Coorg::config()->get('site/email')}</email>
	</author>
	<id>{CoOrg::tagURI('blog/moderation')}</id>
	
	{include file="comment-list.atom.part.tpl" comments=$commentPager->execute(0, 0)}
{/block}
