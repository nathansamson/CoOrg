{block name="widget-title"}
	User Navigation
{/block}
{block name="widget-preview"}
	{'Welcome, %u!'|_:UserSession::get()->username}
	<a href="#">{'Logout'|_}</a>
{/block}
