{block name='title'}{'Login'|_}{/block}
{block name='content'}

{form request='user/executeLogin' instance=$session}
	{input for=username label="Username" required}
	{input for=password label="Password" type="password" required}
	{if $redirect}
		<input type="hidden" name="redirect" value="{$redirect}" />
	{/if}
	
	{input type="submit" label="Login"} <a href="{url request='user/create'}">{'No account?'|_}</a>
{/form}

{/block}
