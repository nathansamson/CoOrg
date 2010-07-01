{block name='title'}My Title{/block}

{block name='content'}
This is an object with id: {$object} and the page is called with an extra parameter {$param}
{if $myActionVar}From controller:{$myActionVar}:{/if}
{/block}
