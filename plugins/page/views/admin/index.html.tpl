{block name="title"}{'Content'|_}{/block}

{block name="content"}
	<h1>{'Manage content'|_}</h1>
	
	<a href="{url request="admin/page/create"}">Nieuwe pagina maken</a>
	<table>
		<tr>
			<th>{'Title'|_}</th>
			<th>{'Short fragment'|_}</th>
			<th>&nbsp</th>
		</tr>
	{foreach $pages as $page}
		<tr>
			<td>
			<a href="{url request="admin/page/edit"
			                  page=$page->ID
			                  redirect="admin/page"}">{$page->title}</a></td>
			<td>{$page->content}</td>
			<td>
				<a href="{url request="admin/page/edit"
			                  page=$page->ID
			                  redirect="admin/page"}">
					<img src="{'images/icons/edit.png'|static}" />
				</a>
				{button request="admin/page/delete"
				        param_ID=$page->ID
				        param_language=$page->language}
					<img src="{'images/icons/edit-delete.png'|static}" />
				{/button}
			</td>
		</tr>
	{/foreach}
	</table>
{/block}
