{if UserSession::get()}
	Welcome, {UserSession::get()->username}!
	<a href="{url request="user/logout"}">{'Logout'|t}</a>
{else}
	<a href="{url request="user/login"}">{'Login'|t}</a>
{/if}
