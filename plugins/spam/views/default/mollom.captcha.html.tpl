{stylesheet file='mollom-captcha.css'|static:'spam'}

{if $captcha instanceof MollomInvalidConfigCaptcha}
	<div class="error">
		{'Creating the captcha failed'|_}
	</div>
{else}
	<div class="mollom-captcha">
	{if $captcha->type == "image"}
		<img src="{$captcha->url|escape}" />
		{input name="refresh" stock="refresh-captcha" type="submit" nolabel tabindex=32000}
		{input name="audio" stock="audio-captcha" type="submit" nolabel tabindex=32000}
	{else}
		<object type="audio/mpeg" data="{$captcha->url|escape}" width="150" height="32">
			<param name="autoplay" value="false" />
			<param name="controller" value="true" />
		</object>
		{input name="refresh" stock="refresh-captcha" type="submit" nolabel tabindex=32000}
		{input name="image" stock="image-captcha" type="submit" nolabel tabindex=32000}
	{/if}
	</div>
	{input name="response" type="text" label="Captcha"}
{/if}
