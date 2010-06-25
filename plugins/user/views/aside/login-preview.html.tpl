{block name="widget-title"}
	User Area
{/block}

{block name="widget-preview"}
		<h1>{'Login'|_}</h1>
{form request="#" instance=sideUser id=$ID}
	{input for=username label="Username" required}
	{input for=password label="Password" required}
	
	<input type="hidden" name="redirect" value="{$coorgRequest|escape}"/>
	
	{input type="submit" label="Login"}
{/form}
{/block}
