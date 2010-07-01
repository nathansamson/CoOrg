{block name="title"}{'User admin'|_}{/block}

{block name="content"}
	<h1>{'User admin'|_}</h1>
	<table>
		<tr>
			<th>{'Username'|_}</th>
			<th>{'Email'|_}</th>
			<th>&nbsp;</th>
		</tr>
		{foreach $users as $user}
			<tr>
				<td>{$user->username}</td>
				<td>{$user->email}</td>
				<td>{a request="admin/user/edit"
				       username=$user->username
				       from=$coorgRequest
				       coorgStock=edit}{/a}</td>
			</tr>
		{/foreach}
	</table>

	{pager pager=$userPager
		request="admin/user/index"
		page=".*."
		coorgWidth=9}
{/block}
