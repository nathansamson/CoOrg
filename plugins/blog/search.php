<?php

$blogSearch = new stdClass;
$blogSearch->title = t('Blogs');
$blogSearch->module = 'blog';
$blogSearch->file = 'search-results.html.tpl';

Taggable::registerSearch('Blog', $blogSearch);

?>
