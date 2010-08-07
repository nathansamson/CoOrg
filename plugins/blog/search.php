<?php

$blogSearch = new stdClass;
$blogSearch->title = t('Blogs');
$blogSearch->module = 'blog';
$blogSearch->file = 'search-results.html.tpl';

Searchable::registerSearch('Blog', $blogSearch);

?>
