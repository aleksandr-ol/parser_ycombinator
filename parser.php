<?php
header("Content-disposition: attachment; filename=file.csv");
header("Content-type: application/octet-stream");
header("Content-Description: File Transfer");
require_once 'vendor/autoload.php';
use voku\helper\HtmlDomParser;

//Получаем DOM-объект страницы с самыми новыми 30 новостями
$dom = HtmlDomParser::file_get_html('https://news.ycombinator.com/newest');
//Создаем массив, в который будем записывать данные новостей
$list = array(array("Title", "Domain", "Link", "Points count"));
//Описываем функцию, которая будет находить на странице все строки таблицы
//в которых хранятся новости и передавать их в функцию, которая будет ее парсить
 function loops ($dom_, $last = false)
{
	//находим все новости на странице
	$elements = $dom_->findMulti('.athing');
	//если мы уже спарсили 3 страницы по 30 новостей, передаем со страницы первые 10
	if($last)
		for ($i=0; $i < 10; $i++) 
			parse($elements[$i], $dom_);
	//иначе - передаем все новости со страницы
	else
		foreach ($elements as $element) 
			parse($element, $dom_);
}
//описываем функцию, которая парсит одну новость
function parse ($el, $dom){
	global $list;

	$title = $el->findMulti('.title')[1]->findOne('a')->innertext();
	$domain = $el->findMulti('.title')[1]->findOne('.sitestr')->innertext();
	$href = $el->findMulti('.title')[1]->findOne('a')->href;
	$points = explode(" ", $dom->findOne('#score_' . $el->id)->innertext())[0];
	//записываем полученные данные в массив
	array_push($list, array($title, $domain, $href, $points));
}
//передаем первую страницу на обработку
loops($dom);

for ($i=0; $i < 3; $i++) {
	//Получаем DOM-объект следующей страницы (кликаем на ссылку "Еще")
	$dom = HtmlDomParser::file_get_html('https://news.ycombinator.com/newest?' . 
		explode("?", $dom->findOne('.morelink')->href)[1]);
	//передаем страницы на обработку
	if ($i == 2)
		loops($dom, true);
	else
		loops($dom);
}

//записываем результат в файл
$fp = fopen('file.csv', 'w');

foreach ($list as $fields) 
    fputcsv($fp, $fields, ";");

fclose($fp);
//записываем результат в файл

//скачываем файл
readfile("file.csv");

?>
