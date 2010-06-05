<section>
	<h1>{'Login'|_}</h1>
{form request="user/executeLogin" instance=sideUser id=asideLogin}
	{input for=username label="Username" required}
	{input for=password label="Password" required}
	
	<input type="hidden" name="redirect" value="{$coorgRequest|escape}"/>
	
	{input type="submit" label="Login"}
{/form}
	<a href="{url request="user/create"}">{'Create an account'|_}</a>
</section>
