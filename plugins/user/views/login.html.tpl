{block name='title'}Login{/block}
{block name='content'}

{form request='user/executeLogin' instance=$session}
	{input for=username label="Username"}
	{input for=password label="Password" type="password"}
	
	{input type="submit" label="Login"}
{/form}

{/block}
