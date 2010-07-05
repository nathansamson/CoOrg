{block name=title}{'Site configuration'|_}{/block}

{block name=admin-content}
	<h1>{'Site configuration'|_}</h1>
	
	{form request="admin/system/update" instance=$config}
		{input for=title label="Site Title" class=title required}
		{input for=subtitle label="Site subtitle"}
		{input for=siteAuthor label="Site Contact" required}
		{input for=siteContactEmail label="Site Email Contact" type=email required}
		
		{input type=submit label="Save Site configuration"}
		
		<p class="warning">
			{'These are advanced configuration settings. Only modify them when you know what you are doing. If you modify them, chances are that your site may not work anymore, and you can\'t access this page anymore.'}
		</p>
		<fieldset>
			<legend>{'Advanced settings'|_}</legend>
			{input for=friendlyURL label="Use friendly urls" type=checkbox}
			{input for=sitePath label="Site path"}
			{input for=UUID label="Site UUID" readonly size=wide}
		
			<fieldset>
				<legend>{'Database settings'|_}</legend>
				{input for=databaseConnection label="Database connection" required}
				{input for=databaseUser label="Database username" required}
				{input for=databasePassword label="Database password" type=password required}
			</fieldset>
		</fieldset>
		
		{input type=submit label="Save Site configuration"}
	{/form}
{/block}
