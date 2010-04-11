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
	
		{block name='content'}This space is intentionally left blank!{/block}
	</body>
</html>
