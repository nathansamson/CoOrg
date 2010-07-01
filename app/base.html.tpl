<!DOCTYPE HTML>
<html lang="{CoOrg::getLanguage()}">
	<head>
		<meta charset="UTF-8" />
		<title>{block name='title'}Default title{/block}</title>
		<link rel="stylesheet" href="{'styles/main.css'|static}" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
		
		{block name='head'}{/block}
		
		<script type="text/javascript" src="{'ckeditor/ckeditor.js'|static}"></script>
		<script type="text/javascript" src="{'ckeditor/adapters/jquery.js'|static}"></script>
		<script>
			var COORGLANG = '{$coorgLanguage}'; 
			$(document).ready(function() {
				$('.full-editor').ckeditor(function() {}, {
					language: '{$coorgLanguage}',
					customConfig: '{'ckeditor/coorgConfig.js'|static}'
				});
				
				$('.lite-editor').ckeditor(function() {}, {
					language: '{$coorgLanguage}',
					toolbar: 'Lite',
					customConfig: '{'ckeditor/coorgConfig.js'|static}'
				});
			});
			
			function staticFile(file)
			{
				return "{$staticPath}"+file;
			}
		</script>
	%%$$EXTRASTYLESHEETSCOMEHERE$$%%</head>
	<body>
		<header>
			<h1><a href="{url request='/'}">{Coorg::config()->get('site/title')}</a></h1>
			<h2>{Coorg::config()->get('site/subtitle')}</h2>
			
			<nav class="main-navigation horizontal">
				<div class="navigation-left">
					{aside name='navigation-left'}
				</div>

				<div class="navigation-right">
					{aside name='navigation-right'}
				</div>
			</nav>
		</header>
	
		<div id="main">
			<aside class="vertical">{aside name='main'}</aside>
	
			<div id="content">
				{foreach $notices as $notice}
					<div class="notice">{$notice|escape}</div>
				{/foreach}
		
				{foreach $errors as $error}
					<div class="error">{$error|escape}</div>
				{/foreach}
		
				{block name='content'}This space is intentionally left blank!{/block}
			</div>
		</div>
		
		<footer>
			{'Powered by @%coorg:CoOrg@!'|_:'http://launchpad.net/coorg'}
			{'CoOrg is @%fsf:free software@ download the @%source:source@.'|_:'http://gnu.org/':'http://code.launchpad.net/coorg/'}
		</footer>
	</body>
</html>
