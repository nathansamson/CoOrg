<?php

$pageSearch = new stdClass;
$pageSearch->title = t('Content');
$pageSearch->module = 'page';
$pageSearch->file = 'search-results.html.tpl';

Searchable::registerSearch('Page', $pageSearch);

?>
