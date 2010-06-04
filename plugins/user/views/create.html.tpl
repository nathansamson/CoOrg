{block name='title'}{'Create new account'|_}{/block}
{block name='content'}
	<h1>{'Create a new user account'|_}</h1>

	{form request="user/save" instance=$user}
		{input label="Username" for=username required}
		{input label="Email" for=email required}
		{input label="Password" for=password type=password required}
		{input label="Password (repeat)" for=passwordConfirmation type=password required}
		
		{input label="Create Account" type="submit"}
	{/form}
{/block}
