{block name='title'}Login{/block}
{block name='content'}

{form request='user/executeLogin' instance=$session}
	{input for=username label="Username" required}
	{input for=password label="Password" type="password" required}
	
	{input type="submit" label="Login"} <a href="{url request='user/create'}">No account?</a>
{/form}

{/block}
