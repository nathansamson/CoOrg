{if UserSession::get()}
	{'Welcome, %u!'|_:UserSession::get()->username}
	<a href="{url request="user/logout"}">{'Logout'|_}</a>
{else}
	<a href="{url request="user/login"}">{'Login'|_}</a>
{/if}
