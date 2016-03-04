<?php


// Create a stream
$opts = array(
	'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n" .
		"Cookie: ci%2Dnm=habarovsk; ci%2Dix=680000; cooked=1; updates=1; _ym_isad=0\r\n"
		)
	);
$context = stream_context_create($opts);
$rssContent = file_get_contents($_GET['url'], false, $context); // replace Category in URL

echo $rssContent;

?>