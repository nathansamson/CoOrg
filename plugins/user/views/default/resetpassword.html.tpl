{block name="title"}{'Password reset'|_}{/block}

{block name="content"}
	<h1>{'Password reset'|_}</h1>
	
	{form request="user/password/confirmreset" instance=$resetPassword}
		<div class="captcha-hide">
			{input type=submit label="Reset password"}
		</div>
	
		{input for=username label="Username"}
		{input for=email label="Email" type="email"}
		
		{foreign file="mollom.captcha.html.tpl" module="spam" captcha=$resetCaptcha}
		
		{input type=submit label="Reset password"}
	{/form}
{/block}
