{block name="title"}{'Content'|_}{/block}

{block name="content"}
	<h1>{'Manage content'|_}</h1>
	
	{a request="admin/page/create"}{'Create page'|_}{/a}
	<table>
		<tr>
			<th>{'Title'|_}</th>
			<th>{'Short fragment'|_}</th>
			<th>&nbsp;</th>
		</tr>
	{foreach $pages as $page}
		<tr>
			<td>
			{a request="admin/page/edit"
			   page=$page->ID
			   redirect="admin/page"}{$page->title|escape}{/a}</td>
			<td>{$page->content|format:none|truncate:100}</td>
			<td>
				{a request="admin/page/edit"
			       page=$page->ID
			       redirect="admin/page"
			       coorgStock=edit}{/a}
				{button request="admin/page/delete"
				        param_ID=$page->ID
				        param_language=$page->language
				        coorgStock=delete
				        coorgConfirm='Are you sure you want to delete "%p"?'|_:$page->title}{/button}
			</td>
		</tr>
	{/foreach}
	</table>
{/block}
