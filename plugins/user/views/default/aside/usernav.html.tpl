{if UserSession::get()}
	{'Welcomen %u!'|_:(UserSession::get()->username|linkyfy:'user/profile/show':UserSession::get()->username)}
	<a href="{url request="user/logout"}">{'Logout'|_}</a>
{else}
	<a href="{url request="user/login"}">{'Login'|_}</a>
{/if}
