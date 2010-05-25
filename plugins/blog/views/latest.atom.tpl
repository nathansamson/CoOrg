<?xml version="1.0" encoding="utf-8"?> 
<feed xmlns="http://www.w3.org/2005/Atom">
 
	<title>{Coorg::config()->get('site/title')}</title>
	<subtitle>{Coorg::config()->get('site/subtitle')}</subtitle>
	<author>
		<name>{Coorg::config()->get('site/author')}</name>
		<email>{Coorg::config()->get('site/email')}</email>
	</author>
	<id>urn:uuid:{Coorg::config()->get('site/uuid')}</id>
	
	{foreach $blogs as $blog}
		<entry>
			<title>{$blog->title}</title>
			<link href="" />
			{if $blog->timeEdited}
				<updated>{$blog->timeEdited|date_format:'c'}</updated>
			{else}
				<updated>{$blog->timePosted|date_format:'c'}</updated>
			{/if}
			<id></id>
			<summary>{$blog->text}</summary>
		</entry>
	{/foreach}
	
</feed>
