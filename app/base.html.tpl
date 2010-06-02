<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>{block name='title'}Default title{/block}</title>
		<link rel="stylesheet" href="{'styles/main.css'|static}" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
		
		{block name='head'}{/block}
	</head>
	<body>
		<header>
			<h1><a href="{url request='/'}">{Coorg::config()->get('site/title')}</a></h1>
			<h2>{Coorg::config()->get('site/subtitle')}</h2>
			
			<nav class="main-navigation">
				<span class="navigation-left">
					{aside name='navigation-left'}
				</span>

				<span class="navigation-right">
					{aside name='navigation-right'}
				</span>
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
