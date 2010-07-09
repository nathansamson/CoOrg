{block name="title"}{'Edit Profile'}{/block}

{block name="content"}
	{stylesheet file='profile.css'|static:'user'}
	<h1>{'Edit Profile'}</h1>
	
	{form request="user/profile/update" instance=$profile}
		{input name=from value=$from}
		{input for=firstName label="First Name"}
		{input for=lastName label="Last Name"}
		{input for=birthDate label="Birth Date" type="date"}
		{input for=website label="Website" type="url" size=wide}
		{input for=biography label="About you" type="textarea" size=wide}
		{input for=intrests label="Intrests" type="textarea" size="small-wide"}
		
		{input for=avatar label="Avatar" type="file" preview="image" previewClass="avatar-preview"}
		
		{input type="submit" label="Save profile"}
	{/form}
{/block}
