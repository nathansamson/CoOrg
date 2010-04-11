{block name='title'}Create new account{/block}
{block name='content'}
	{form request="user/save" instance=$user}
		{input label="Username" for=username}
		{input label="Email" for=email}
		{input label="Password" for=password type=password}
		{input label="Password (repeat)" for=passwordConfirmation type=password}
		
		{input label="Create Account" type="submit"}
	{/form}
{/block}
