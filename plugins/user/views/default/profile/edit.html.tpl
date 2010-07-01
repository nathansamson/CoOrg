{block name="title"}{'Edit Profile'}{/block}

{block name="content"}
	<h1>{'Edit Profile'}</h1>
	
	{form request="user/profile/update" instance=$profile}
		{input for=firstName label="First Name"}
		{input for=lastName label="Last Name"}
		{input for=birthDate label="Birth Date" type="date"}
		{input for=website label="Website" type="url" size=wide}
		{input for=biography label="About you" type="textarea" size=wide}
		{input for=intrests label="Intrests" type="textarea" size="small-wide"}
		
		{input type="submit" label="Save profile"}
	{/form}
{/block}
