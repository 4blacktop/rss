<?php
// Prepare
header("Content-Type: text/html; charset=utf-8");
set_time_limit(600);
$mtime = microtime(true);


// Create a stream
$opts = array(
	'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n" .
		"Cookie: ci%2Dnm=habarovsk; ci%2Dix=680000; cooked=1; updates=1; _ym_isad=0\r\n"
		)
	);
$context = stream_context_create($opts);

// echo '<pre><h1>Проверь кино - афиша должна быть на сегодня!</h1>' . date("Ymd-His", time()+14400) . '<br /><br />';
echo '<pre><h1>АФИША закеширована!!</h1>' . date("Ymd-His", time()+14400) . '<br /><br />';

// =====================================================================================
// ======================================= Settings  ===================================
// =====================================================================================
// Pause Settings
$pauseMin = 0;
$pauseMin = 1;

// Quantity of Post titles in each category. Counting from ZERO, but "if  >=" makes the Qty values actual!
$newsQty   = 30;
$cinemaQty = 30;
$eventsQty = 30; //29

// News XML URL

$arrXmlNews = file_get_contents('http://www.moigorod.ru/uploads/rss/_headlines/680000/news-main.xml', false, $context); // replace Category in URL
// $arrXmlNews = str_ireplace("<![CDATA[", "", $arrXmlNews);
// $arrXmlNews = str_ireplace("]]>", "", $arrXmlNews);
$arrXmlNews = objectsIntoArray(simplexml_load_string($arrXmlNews)); // replace Category in URL


// Events XML URL
$arrXmlEvents = file_get_contents('http://www.moigorod.ru/uploads/rss/_headlines/680000/events-all.xml', false, $context); // replace Category in URL
$arrXmlEvents = str_ireplace("<![CDATA[", "", $arrXmlEvents);
$arrXmlEvents = str_ireplace("]]>", "", $arrXmlEvents);
$arrXmlEvents = objectsIntoArray(simplexml_load_string($arrXmlEvents));	// replace Category in URL

// Cinema XML URL
$arrXmlCinema = file_get_contents('http://www.moigorod.ru/uploads/rss/_headlines/680000/cinema-newfilms.xml', false, $context); // replace Category in URL
$arrXmlCinema = str_ireplace("<![CDATA[", "", $arrXmlCinema);
$arrXmlCinema = str_ireplace("]]>", "", $arrXmlCinema);
// $arrXmlCinema = strip_tags($arrXmlCinema);
$arrXmlCinema = preg_replace('#<img[^>].*?/>#si', '', $arrXmlCinema);
// print_r($arrXmlCinema);
$arrXmlCinema = objectsIntoArray(simplexml_load_string($arrXmlCinema));// replace Category in URL

// Array to json convert
$arrayOut = array (
	"news" => array (
		"all" => array (
			"posts" => array (
			)
		)
	),
	"sale" => array (
		"all" => array (
			"posts" => array (
			)
		)
	),
	"cinema" => array (
		"all" => array (
			"posts" => array (
			)
		)
	),
	"events" => array (
		"all" => array (
			"posts" => array (
			)
		)
	),
	"currency" => array (
		"all" => array (
			"posts" => array (
			)
		)
	),
);


// =====================================================================================
// =============== Parsing XML RSS Channels and Save content to HTML Cache =============
// =====================================================================================
echo '<hr />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Parsing News RSS... " . count($arrXmlNews['channel']['item']) . " elements</strong>"; flush(); // replace Category in echo


// all news ============================================================================
foreach ($arrXmlNews['channel']['item'] as $key => $item) {
if ( $key >= $newsQty ) { 
	echo "<br />MAX # of News reached: Break!";
	break;
	}	
	if (file_exists("stop.txt")) {exit("<br />stop.txt!");}
	$filename = str_ireplace("http://habarovsk.MoiGorod.Ru/m/news/?n=", "", $item["pdalink"]); 														// replace URL in str_ireplace
	echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t$key. Checking news/" . $filename . ".html"; flush(); 									// replace Category in "checking "
	$path = "news/$filename.html"; 																													// replace folder in path variable
	if (file_exists($path)) {
		echo "\tCached ok"; flush();
	} else {
	rndSleep($pauseMin,$pauseMax);
	$contentHTML = file_get_contents($item["pdalink"], false, $context);
	echo "\tDownload " . strlen($contentHTML) ." bytes\tFrom: " . $item["pdalink"] . "\tTo: " .$path; flush();
	if ($contentHTML) { file_put_contents($path, $contentHTML); }
	else { echo "ERROR! NULL content: " . $path; }
	}
	
	
	// Parse local HTML files for JSON
	$localContent = file_get_contents($path);
	preg_match('#\<div.id\=\"nb\"\>(.*?)\<\/div\>#sim', $localContent, $arrayTextLocalContent);
	preg_match('#<img[^>]*>#si', $arrayTextLocalContent[1], $imgTag);
	$imgTag = str_ireplace('src="/', 'width="100%" src="http://www.moigorod.ru/', $imgTag[0]);
	
	preg_match('/src="([^"]*)"/', $imgTag, $matches);
	$imgUrl = $matches[1];

// <img src="/uploads/news/2146446564/ddt6ru_t.jpg">	
// src="http://www.moigorod.ru/uploads/news/2146446564/ddt6ru_t.jpg 	
	
	// <img src="/uploads/news/2146446564/ddt6ru_t.jpg">
	// $imgTag = 
	
	// print_r($imgTag);
	
	$textContent = strip_tags(($arrayTextLocalContent[1]), '<br><i>');
	$textContent = trim($textContent);
	$textContent = prepareJSON($textContent);
	$pubDate = date('d.m.Y',strtotime($item["pubDate"]));
	// echo "<br />" . $pubDate;
	
	// Add data to array
		// "pubDate" => date('d.m.Y',strtotime($item["pubDate"])),
		// "description" => $item["description"],
	$arrayOut['news']['all']['posts'][] = array(
		"id" => $filename,
		"title" => $item["title"],
		"link" => $item["link"],
		"pdalink" => $item["pdalink"],
		"pubDate" => $pubDate,
		"img" => $imgTag,
		"imgurl" => $imgUrl,
		"content"  => $textContent
	);
}
echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Done!</strong>"; flush();


// all city events parsing from desktop version to get pictures ====================================================
echo '<hr />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Parsing Events RSS... " . count($arrXmlEvents['channel']['item']) . " elements</strong>"; flush(); // replace Category in echo
foreach ($arrXmlEvents['channel']['item'] as $key => $item) {
	if ( $key >= $eventsQty ) { 
	echo "<br />MAX # of Events reached: Break!";
	break;
	}		
	if (file_exists("stop.txt")) {exit("<br />stop.txt!");}
	// $filename = str_ireplace("http://habarovsk.MoiGorod.Ru/m/events/?id=", "", $item["pdalink"]); 													// replace URL in str_ireplace
	$filename = str_ireplace("http://habarovsk.MoiGorod.Ru/events/?id=", "", $item["link"]); 													// replace URL in str_ireplace
	echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t$key. Checking events/" . $filename . ".html"; flush(); 								// replace Category in "checking "
	$path = "events/$filename.html"; 																												// replace folder in path variable
	if (file_exists($path)) {
		echo "\tCached ok"; flush();
	} else {
	rndSleep($pauseMin,$pauseMax);
	// $contentHTML = file_get_contents($item["pdalink"], false, $context);
	$contentHTML = file_get_contents($item["link"], false, $context);
	echo "\tDownload " . strlen($contentHTML) ." bytes\tFrom: " . $item["pdalink"] . "\tTo: " .$path; flush();
	if ($contentHTML) { file_put_contents($path, $contentHTML); }
	else { echo "ERROR! NULL content: " . $path; }
	}
	
	// Parse local HTML files for JSON
	$localContent = file_get_contents($path);
	preg_match('#\<div.class\=\"evt\".id\=\"nb\".itemprop\=\"description\"\>(.*?)\<\/div\>#sim', $localContent, $arrayTextLocalContent);
	
	// Parse place
	preg_match('#\<h1\>\<span.itemprop\=\"name\"\>(.*?)\<\/span\>#si', $localContent, $place);
	$place = '<h3>Место и время проведения:</h3>' . $place[1];
	$place = prepareJSON($place);
	$place = trim($place);
	
	// Parse schedule
	preg_match('#\<ul.class\=\"time.cascad\"\>(.*)\<\/ul\>#si', $localContent, $schedule);
	$schedule = $schedule[1];
	$schedule = str_ireplace('</li>', '<br/>', $schedule);
	$schedule = strip_tags($schedule, '<b><br><i><br/>');
	$schedule = trim($schedule);
	$schedule = prepareJSON($schedule);
	
	
	preg_match('#<img[^>]*>#si', $arrayTextLocalContent[0], $imgTag);
	$imgTag = $imgTag[0];
	$imgTag = str_ireplace(' style="margin:0 0 1em 1em; float:right"', '', $imgTag);
	$imgTag = str_ireplace('/uploads', 'http://www.moigorod.ru/uploads', $imgTag)  . '<br/>';
	$imgTag = trim($imgTag);
	
	preg_match('/src="([^"]*)"/', $imgTag, $matches);
	$imgUrl = $matches[1];
	
	$textContent = strip_tags(($arrayTextLocalContent[0]), '<br><i><br/>');
	$textContent = trim($textContent);
	$textContent = prepareJSON($textContent);
	$pubDate = date('d.m.Y',strtotime($item["pubDate"]));
	
	$desc = $item["description"];
	$desc = strip_tags($desc);
	$desc = prepareJSON($desc);
	
	preg_match_all("#\((.*)\)#U", $item["title"], $arrayBrackets, PREG_SET_ORDER);
	$eventDate = array_pop($arrayBrackets);
	$item["title"] = str_ireplace($eventDate[0], "", $item["title"]); 
	$shortTitle = $item["title"];
	$arrayEventDate = explode(" ", $eventDate[1]);
	$buf = $arrayEventDate[0];
	$arrayEventDate[0] = $arrayEventDate[1];
	$arrayEventDate[1] = $buf;
	$time24 = array_pop($arrayEventDate);
	$time24  = date("H:i", strtotime($time24));
	array_push($arrayEventDate, $time24);
	$eventDate = implode(" ", $arrayEventDate);
	$item["title"] = $item["title"] . "(" . $eventDate  . ")";


	// Add data to array
	$arrayOut['events']['all']['posts'][] = array(
		"id" => $filename,
		"title" => $item["title"],
		"link" => $item["link"],
		"pdalink" => $item["pdalink"],
		"description" => $item["description"],
		"pubDate" => $pubDate,
		"imgurl" => $imgUrl,
		"shortTitle" => $shortTitle,
		"eventDate" => $eventDate,
		"content"  => $imgTag . '<br />' . $textContent . '<br />' . $place . '<br />' .  $schedule 
	);
	
	// "content"  => ''
}
echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Done!</strong>"; flush();
 
 
 
// cinema today =========================================================================
echo '<hr />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Parsing Cinema RSS... " . count($arrXmlCinema['channel']['item']) . " elements</strong>"; flush(); 	// replace Category in echo
foreach ($arrXmlCinema['channel']['item'] as $key => $item) {
	if ( $key >= $cinemaQty ) { 
		echo "<br />MAX # of Cinema reached: Break!";
		break;
		}			
	if (file_exists("stop.txt")) {exit("<br />stop.txt!");}
	$filename = str_ireplace("http://habarovsk.MoiGorod.Ru/m/kino/movie.asp?m=", "", $item["pdalink"]); 											// replace URL in str_ireplace
	echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t$key. Checking cinema/" . $filename . ".html"; flush(); 								// replace Category in "checking "
	$path = "cinema/$filename.html"; // replace folder in path variable
	
	// COMMENT THIS LINE!!! DO NOT check existing files because every day cinema schedule must be actual UNCOMMENT LINES ONLY FOR DEBUG USE!!! 
	// if (file_exists($path)) {// COMMENT THIS LINE!!! 
		// echo "\t*** DO NOT CACHE! *** Cached ok"; flush(); // COMMENT THIS LINE!!! 
	// } else {// COMMENT THIS LINE!!! 
	
	rndSleep($pauseMin,$pauseMax);
	$contentHTML = file_get_contents($item["pdalink"], false, $context);
	echo "\tDownload " . strlen($contentHTML) ." bytes\tFrom: " . $item["pdalink"] . "\tTo: " .$path; flush();
	if ($contentHTML) { file_put_contents($path, $contentHTML); }
	else { echo "ERROR! NULL content: " . $path; }

	// } // COMMENT THIS LINE!!! DO NOT check existing files because every day cinema schedule must be actual
	
	// Parse local HTML files for JSON
	$localContent = file_get_contents($path);
	preg_match('#\<b\>Описание\:\<\/b\>(.*)\<form.method\=\"get\"#sim', $localContent, $arrayTextLocalContent);
	
	$textContent = trim($arrayTextLocalContent[1]);
	$textContent = strip_tags($textContent, '<br><b></b><i></i><br /><br/>');
	$textContent = prepareJSON($textContent);
	
	preg_match('#\<\/tr\>\<\/table\>(.*?)<a href="(.*?)\".style#sim', $localContent, $imgUrl);
	$imgTag = str_ireplace('/uploads', '<img src="http://www.moigorod.ru/uploads', $imgUrl[2]) . '" /><br />';
	preg_match('/src="([^"]*)"/', $imgTag, $matches);
	$imgUrl = $matches[1];
	
	preg_match('#\<fieldset\>\<legend\>(.*?)\<div.class\=\"dsc\"\>#sim', $localContent, $todaySchedule);
	$todaySchedule = $todaySchedule[1];
	$todaySchedule = '<h3>' . str_ireplace('</legend>', '</h3>', $todaySchedule);
	$todaySchedule = trim($todaySchedule);
	$todaySchedule = strip_tags($todaySchedule, '<h3></h3><br><b></b><i></i><br /><br/>');
	
	$pubDate = date('d.m.Y',strtotime($item["pubDate"]));

	
	
	
	
	// Add data to array
		// "pubDate" => date('d.m.Y',strtotime($item["pubDate"])),
		// "description" => $desc,
	$arrayOut['cinema']['all']['posts'][] = array(
		"id" => $filename,
		"title" => $item["title"],
		"link" => $item["link"],
		"pdalink" => $item["pdalink"],
		"description" => $item["description"],
		"pubDate" => $pubDate,
		"img" => $imgTag,
		"imgurl" => $imgUrl,
		"content"  => $textContent . $todaySchedule
	);
		// "content"  => $textContent
		// "content"  => 'Содержимое текст Контент'
		// "content"  => $textContent
		// "content"  => $arrayTextLocalContent[1]
}
echo '<br />' . round((microtime(true) - $mtime) * 1, 4) . "\t<strong>Done!</strong>"; flush();




// =====================================================================================
// ========================== Get Currency Information =================================
// =====================================================================================
// Get currency
$content = file_get_contents('http://www.moigorod.ru/m/info/currency.asp', false, $context);
preg_match('#\<ul.class\=\"curr\"\>(.*)\<div.id\=\"footMenu\"\>#sim', $content, $arrayContent);
$currencyContent = $arrayContent[1];
$currencyContent = str_ireplace("</li><li>", "<br />", $currencyContent);
$currencyContent = str_ireplace("<b>", "<h4>", $currencyContent);
$currencyContent = str_ireplace("</b>", "</h4>", $currencyContent);
$currencyContent = str_ireplace('<span class="m">', '<span style="color:red; font-weight:bold">', $currencyContent);
$currencyContent = strip_tags($currencyContent, '<br><h1><h2><h3><h4><h5><h6><span></span>');
$currencyContent = trim(str_ireplace("</h4><br />", "</h4>", $currencyContent));
	// Add data to array
		if ($currencyContent) { file_put_contents($path, $contentHTML); }
	else {echo "ERROR! NULL content: CURRENCY";}
	$arrayOut['currency']['all']['posts'][] = array(
		"content"  => $currencyContent
	);
// =====================================================================================
// ======================= Parsing HTML Content into JSON ==============================
// =====================================================================================



// print_r($arrayOut);
$result = _json_encode($arrayOut);
file_put_contents('json680000-' . time() . '.txt', $result);
file_put_contents('json680000.txt', $result);
// echo $result;
echo '<br /><br />Exec time: ' . round((microtime(true) - $mtime) * 1, 4) . ' s.</pre>';


// =====================================================================================
// ================================= Functions =========================================
// =====================================================================================


// Parsing XML into Array source code from xml_parse page php.chm
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
$arrData = array();
if (is_object($arrObjData)) { // if input is object, convert into array
	$arrObjData = get_object_vars($arrObjData);
}
if (is_array($arrObjData)) {
	foreach ($arrObjData as $index => $value) {
		if (is_object($value) || is_array($value)) {
			$value = objectsIntoArray($value, $arrSkipIndices); // recursive call
		}
		if (in_array($index, $arrSkipIndices)) {
			continue;
		}
		$arrData[$index] = $value;
	}
}
return $arrData;
}

// optimize array
function optimizeArray($array)
	{
	$array = array_unique($array);
	$array = array_map('trim', $array);
	$array = array_filter($array);
	return $array;
	}	

// pause rnd seconds
function rndSleep($pauseMin,$pauseMax) {
	$pause = rand ($pauseMin,$pauseMax); 
	echo "\tPause $pause s.";
	flush();
	sleep($pause);
	}

// alternative json_encode
function _json_encode($val)
 {
     if (is_string($val)) return '"'.addslashes($val).'"';
     if (is_numeric($val)) return $val;
     if ($val === null) return 'null';
     if ($val === true) return 'true';
     if ($val === false) return 'false';

     $assoc = false;
     $i = 0;
     foreach ($val as $k=>$v){
         if ($k !== $i++){
             $assoc = true;
             break;
         }
     }
     $res = array();
     foreach ($val as $k=>$v){
         $v = _json_encode($v);
         if ($assoc){
             $k = '"'.addslashes($k).'"';
             $v = $k.':'.$v;
         }
         $res[] = $v;
     }
     $res = implode(',', $res);
     return ($assoc)? '{'.$res.'}' : '['.$res.']';
 }


// Getting rid of farting symbols
function prepareJSON($textContent)
 {
	$textContent = str_ireplace("'", "", $textContent);
	$textContent = str_ireplace('\'', "", $textContent);
	$textContent = str_ireplace("\'", "", $textContent);
	$textContent = str_ireplace('`', "", $textContent);
	$textContent = str_ireplace('`', "", $textContent);
	$textContent = str_ireplace('‘', "", $textContent);
	$textContent = str_ireplace('’', "", $textContent);
	$textContent = str_ireplace('”', "", $textContent);
	$textContent = str_ireplace('"', "", $textContent);
	$textContent = str_ireplace('\\', "", $textContent);
	$textContent = str_ireplace('«', "", $textContent);
	$textContent = str_ireplace('»', "", $textContent);
	$textContent = str_ireplace(':', "", $textContent);
	$textContent = str_ireplace('…', "", $textContent);
	// $textContent = str_ireplace('/', "", $textContent);
	$textContent = str_ireplace("\n", "", $textContent);
	$textContent = str_ireplace("\r", "", $textContent);
	$textContent = str_ireplace("\f", "", $textContent);
	$textContent = str_ireplace("\t", "", $textContent);
	$textContent = str_ireplace("\b", "", $textContent);
	// $textContent = clear_string($textContent);
	
	// $textContent = iconv("utf-8", "windows-1251//IGNORE", $textContent);
	// $textContent = iconv("windows-1251", "utf-8//ignore", $textContent);
// iconv("UTF-8", "ISO-8859-1//IGNORE", $text), 

	// preg_replace('%([\\x00-\\x1f\\x22\\x5c])%e','sprintf("\\\\u%04X", ord("$1"))',$textContent) . '"';
	return ($textContent);
 }


// function clear_string($var) {
	// $var = (string) (phpversion() > '5.2.0') ? preg_replace('/[^\w\pL_-\s]/ui', '', $var) : filter_var($var, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
// }
































/* 
// suspended function due to rss parsing version improved and cookie support!
// получаем ссылки для разделов: новости, афиша
function getCatURLs($catURL, $regexp, $mtime)
	{
	echo '<hr />' . round((microtime(true) - $mtime) * 1, 4) . "\tParsing URLs from $catURL..." . '<br />'; flush();
	$content = file_get_contents($catURL);
	echo $content;
	echo round((microtime(true) - $mtime) * 1, 4) . "\tDone " . strlen($content) . ' bytes!<br />'; flush();
	preg_match($regexp, $content, $arrayContent);
	// print_r($arrayContent);
	// file_put_contents('out.html', $arrayContent[1]);
	preg_match_all('#href\=\"(.*?)\"#sim', $arrayContent[1], $shortURLs);
	$shortURLs[1] = optimizeArray($shortURLs[1]);
	foreach ($shortURLs[1] as $url) {
		$longURLs[] = "http://www.moigorod.ru" . $url;
		}
	echo round((microtime(true) - $mtime) * 1, 4) . "\tURLs found: " . sizeof($longURLs) . '<br />'; flush();
	print_r($longURLs);
	echo '<hr />';
	return($longURLs);		
	}
 */
// Новости основные http://www.moigorod.ru/uploads/rss/_headlines/680000/news-main.xml
// Новости спорта http://www.moigorod.ru/uploads/rss/_headlines/680000/news-sport.xml
// Объявления http://www.moigorod.ru/uploads/rss/_headlines/680000/board-main.xml
// Работа - Вакансии http://www.moigorod.ru/uploads/rss/_headlines/680000/board-vac.xml
// Работа - Резюме http://www.moigorod.ru/uploads/rss/_headlines/680000/board-res.xml
// Знакомства - парни http://www.moigorod.ru/uploads/rss/_headlines/680000/board-lovm.xml
// Знакомства - девушки http://www.moigorod.ru/uploads/rss/_headlines/680000/board-lovf.xml
// Городская афиша http://www.moigorod.ru/uploads/rss/_headlines/680000/events-all.xml
// Каталог фирм и заведений http://www.moigorod.ru/uploads/rss/_headlines/680000/catalog-all.xml
// Призовой клуб - Новые конкурсы http://www.moigorod.ru/uploads/rss/_headlines/680000/prizeclub-newcompet.xml
// Новости кино http://www.moigorod.ru/uploads/rss/_headlines/0/news-cinema.xml
// Форум - Новые темы http://www.moigorod.ru/uploads/rss/_headlines/680000/board-forum.xml
// Кино - Сегодня в кино http://www.moigorod.ru/uploads/rss/_headlines/680000/cinema-newfilms.xml
// Конференции с известными людьми http://www.moigorod.ru/uploads/rss/_headlines/680000/f2f-newconf.xml
// Домашние странички http://www.moigorod.ru/uploads/rss/_headlines/0/hp-newpages.xml
// Валюты http://www.moigorod.ru/m/info/currency.asp

// http://www.moigorod.ru/m/news/ - GOOD!!!!!!!
// $arrayNewsURLs = getCatURLs('http://www.moigorod.ru/m/news/', '#\<div.class\=\"mainnews\"\>(.*)\<div.id\=\"footMenu\"\>#sim', $mtime);

// http://www.moigorod.ru/m/kino/
// $arrayCinemaURLs = getCatURLs('http://www.moigorod.ru/m/kino/', '#\<\/h1\>\<ul.class\=\"list\"\>(.*)\<div.id\=\"footMenu\"\>#sim', $mtime);
// $arrayCinemaURLs = getCatURLs('content-cinema.txt', '#\<\/h1\>\<ul.class\=\"list\"\>(.*)\<div.id\=\"footMenu\"\>#sim', $mtime);


?>