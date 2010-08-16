<?php

function smarty_modifier_format($text, $type)
{
	$types = array(
		'all' => '<strong><strike><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><br><p>',
		'small' => '<strong><strike><em><u><ul><ol><li><a><br><p>',
		'text' => '<br>',
		'none' => ''
	);
	
	$text = strip_tags($text, $types[$type]);
	
	$allowattributes = '(?<!'.implode(')(?<!', array('href', 'src')).')';
	$text = preg_replace_callback("/<[^>]*>/i",create_function(
            '$matches',
            'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'   
        ),$text); 
	
	// This fixes a problem for some feed readers (showing to much whitespace)
	// Feed readers that we are aware of that benefit from this change
	//  * Liferea 1.7 (Webkit based)
	$text = str_replace("\r\n", '', $text);
	
	return $text;
}

?>
