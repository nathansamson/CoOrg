<section>
	<h1>Login</h1>
{form request="user/executeLogin" instance=sideUser id=asideLogin}
	{input for=username label="Username" required}
	{input for=password label="Password" required}
	
	{input type="submit" label="Login"}
{/form}
	<a href="{url request="user/create"}">Create an account</a>
</section>
