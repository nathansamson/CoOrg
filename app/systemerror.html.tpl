{block name='title'}Error{/block}

{block name='content'}
<h1>An unexpected error occured..</h1>

<p>If you entered the URL manually, please check for any errors. If you clicked on a link on this website please try again later.</p>

<h2>More info</h2>
<p>
	<em>Request: </em>{$request}<br />
	<em>Referer: </em>{$referer}<br />
</p>

<h3>Debug info</h3>
<p>
	An exception was thrown with message <q>{$exception->getMessage()}</q>
</p>
{/block}
