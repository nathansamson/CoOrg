<!DOCTYPE HTML>
<html>
	<head>
		<title>{block name='title'}Default title{/block}</title>
	</head>
	<body>
		{foreach $notices as $notice}
			<div class="notice">{$notice}</div>
		{/foreach}
		
		{foreach $errors as $error}
			<div class="error">{$error}</div>
		{/foreach}
	
		<div>
			{if UserSession::get()}
				Welcome, {UserSession::get()->username}!.
				<a href="{url request="user/logout"}">Logout</a>
			{else}
				<a href="{url request="user/login"}">Inloggen</a>
			{/if}
		</div>
	
		{block name='content'}This space is intentionally left blank!{/block}
	</body>
</html>
