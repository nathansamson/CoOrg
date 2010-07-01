{block name="title"}{'Edit %u'|_:$user->username}{/block}

{block name="content"}
	{if $from}
		<div class="page-actions">
			{a request=$from}{'⬅ Go back to index'|_}{/a}
		</div>
	{/if}
	<h1>{'Edit %u'|_:$user->username}</h1>
	{form request="admin/user/save" instance=$user}
		{input name=from value=$from}
		{input for=username label="Username" readonly required}
		
		{input for=email type="email" label="Email" required}
		
		<fieldset>
			<legend>{'Password change'|_}</legend>
			{input name=password type=password label="New password"}
			{input name=passwordConfirmation type=password label="New password (confirm)"}
		</fieldset>
		
		{input type="submit" label="Save user"}
	{/form}
	
	{if $user->isLocked()}
		{button request="admin/user/unlock"
		        param_username=$user->username
		        param_from=$coorgRequest}{'Unlock the user'|_}{/button}
	{/if}
{/block}
