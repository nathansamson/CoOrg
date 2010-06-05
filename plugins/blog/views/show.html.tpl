{block name='title'}{$blog->title|escape}{/block}

{block name='content'}
<h1>{$blog->title|escape}</h1>
{$blog->text|format:all}
{/block}
