<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>{block name='title'}Default title{/block}</title>
		
		{block name='head'}
			<link rel="stylesheet" href="{'styles/main.css'|static}" />
		{/block}
	</head>
	<body>
		<header>
			<h1><a href="{url request='/'}">CoOrg Example Site</a></h1>
			<h2>With A SubTitle</h2>
			
			<nav>
				<ol class="site">
					<li><a href="{url request='/'}">Home</a></li>
					<li><a href="{url request='blog'}">Blog</a></li>
				</ol>

				
				<ol class="user">
				{if UserSession::get()}
					Welcome, {UserSession::get()->username}!.
					<a href="{url request="user/logout"}">Logout</a>
				{else}
					<a href="{url request="user/login"}">Login</a>
				{/if}
				</ol>
			</nav>
		</header>
	
		<div id="main">
			<aside>{aside name='main'}</aside>
	
			<div id="content">
				{foreach $notices as $notice}
					<div class="notice">{$notice}</div>
				{/foreach}
		
				{foreach $errors as $error}
					<div class="error">{$error}</div>
				{/foreach}
		
				{block name='content'}This space is intentionally left blank!{/block}
			</div>
		</div>
	</body>
</html>
