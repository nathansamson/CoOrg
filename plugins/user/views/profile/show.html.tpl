{block name="title"}{'Profile of %u'|_:$profile->username}{/block}

{block name="content"}
	{if $profile->username == UserSession::get()->username}
		<div class="page-actions">
			{a request="user/profile/edit"
			   username=$profile->username coorgStock=edit}{/a}
		</div>
	{/if}
	<h1>{'Profile of %u'|_:$profile->username}</h1>
	
	{if $profile->firstName || $profile->lastName}
		<h2>Naam</h2>
		{$profile->firstName|escape} {$profile->lastName|escape}
	{/if}
	
	<h2>{'About'}</h2>
	{if $profile->website}
		<p><a href="{$profile->website|escape}" target="_blank">{'Visit the site of %u'|_:$profile->username}</a></p>
	{/if}
	
	{if $profile->birthDate}
		{'%u is born at %d'|_:$profile->username:($profile->birthDate|date_format:'Y-m-d')}
	{/if}
	
	{if $profile->biography}
		<h3>{'Biography'|_}</h3>
		<p>
			{$profile->biography|format:'small'}
		</p>
	{/if}
	
	{if $profile->intrests}
		<h3>{'Intrests'|_}</h3>
		<p>
			{$profile->intrests|format:'none'}
		</p>
	{/if}
{/block}
