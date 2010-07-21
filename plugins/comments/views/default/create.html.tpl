{form request=$commentRequests->save instance=$newComment id="comment_new"}
	{foreach $commentOn as $key => $value}
		{input value=$value name=$key}
	{/foreach}
	
	{if $newComment->anonAuthor}
		<fieldset>
			<legend>{'Personal information'|_}</legend>
			{subform instance=$newComment->anonAuthor}
				{input for=name label="Name" required}
				{input for=email label="Email" required type=email}
				{if !$nowebsite}
					{input for=website label="Website" type=url}
				{/if}
			{/subform}
		</fieldset>
	{/if}
	
	{if !$notitle}
		{input for=title label="Title" class=title required}
	{/if}
	{input for=comment label="Comment" type=textarea required editor=lite}
	
	{if $commentCaptcha}
		{foreign file="mollom.captcha.html.tpl" module="spam" captcha=$commentCaptcha}
	{/if}
	
	{input type="submit" label="Post comment"}
{/form}
