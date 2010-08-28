{block name='content'}
	<title>An unexpected error occured..</title>

<subtitle>If you entered the URL manually, please check for any errors. If you clicked on a link on this website please try again later.</subtitle>

	<entry>
		<title>More info</title>
		<content>
			Request: {$request}
			Referer: {$referer}
		</content>
	</entry>

	<entry>
		<title>Debug info</title>
		<content>
			An exception was thrown with message "{$exception->getMessage()}"
		</content>
	</entry>
	<entry>
		<title>Stack trace</title>
		<content>
			{$exception->getTraceAsString()}
		</content>
	</entry>
{/block}
