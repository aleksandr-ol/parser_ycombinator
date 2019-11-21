<?php
header("Content-disposition: attachment; filename=file.csv");
header("Content-type: application/octet-stream");
header("Content-Description: File Transfer");
require_once 'vendor/autoload.php';
use voku\helper\HtmlDomParser;

$dom = HtmlDomParser::file_get_html('https://news.ycombinator.com/newest');

$list = array(array("Title", "Domain", "Link", "Points count"));
 function loops ($dom_, $last = false)
{
	$elements = $dom_->findMulti('.athing');

	if($last)
		for ($i=0; $i < 10; $i++) 
			parse($elements[$i], $dom_);
	else
		foreach ($elements as $element) 
			parse($element, $dom_);
}

function parse ($el, $dom){
	global $list;

	$title = $el->findMulti('.title')[1]->findOne('a')->innertext();
	$domain = $el->findMulti('.title')[1]->findOne('.sitestr')->innertext();
	$href = $el->findMulti('.title')[1]->findOne('a')->href;
	$points = explode(" ", $dom->findOne('#score_' . $el->id)->innertext())[0];

	array_push($list, array($title, $domain, $href, $points));
}

loops($dom);

for ($i=0; $i < 3; $i++) {
	$dom = HtmlDomParser::file_get_html('https://news.ycombinator.com/newest?' . 
		explode("?", $dom->findOne('.morelink')->href)[1]);

	if ($i == 2)
		loops($dom, true);
	else
		loops($dom);
}

$fp = fopen('file.csv', 'w');

foreach ($list as $fields) 
    fputcsv($fp, $fields, ";");

fclose($fp);

readfile("file.csv");

?>