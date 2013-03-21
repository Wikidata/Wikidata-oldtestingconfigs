<?php
require_once 'Services/W3C/HTMLValidator.php';

$domain = "http://wikidata-dev-repo.wikimedia.de/wiki/";
$urls = array("Special:ItemByTitle/enwiki/Helium:", "Special:CreateItem", "Special:ItemDisambiguation&language=en&label=Helium", "Property:P2", "Special:NewProperty", "Special:EntitiesWithoutLabel", "Special:SetLabel");

$whiteList= array("Bad value ResourceLoaderDynamicStyles for attribute name on element meta: Keyword resourceloaderdynamicstyles is not registered.");

$v = new Services_W3C_HTMLValidator();

function checkArrayValues($text) {
	global $whiteList;
	
	foreach ($whiteList as $key => $value) {
		if (strpos($value, $text) === 0) {
			return true;
		}
	}
	return false;
}

$validation_page_header = '<!DOCTYPE HTML>
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>Wikidata HTML Validation</title>
        </head>
        <body>
                <a href="http://meta.wikimedia.org/wiki/Wikidata"><img src="http://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Wikidata-logo-en.svg/160px-Wikidata-logo-en.svg.png" /></a> <h2>Wikidata HTML Validation</h2>
                <p>
                This page lists all HTML validation errors in Wikidata found by the <a href="http://validator.w3.org">W3C Markup Validation Service</a>.</p>
';
$validation_page_content = '';
$validation_page_footer = '
        </body>
</html>';

$errorsPerPage = array();

foreach ($urls as $url) {
        $r = $v->validate($domain.$url);

        if ($r->isValid()) {
                echo $domain.$url.' is valid.'.PHP_EOL;

        } else {
                if (sizeof($r->errors) > 0) {
                        foreach ($r->errors as $nr => $error) {
							if (!checkArrayValues(trim($error->message))) {
								
								if (!array_key_exists($domain.$url, $errorsPerPage)) {
									$errorsPerPage[$domain.$url] = 1;
								} else {
									$errorsPerPage[$domain.$url] = $errorsPerPage[$domain.$url]+1;
								}
								
								if ($errorsPerPage[$domain.$url] == 1) {
									echo $domain.$url.' is not valid:'.PHP_EOL;
									$validation_page_content .= '<p><a href="'.$domain.$url.'">'.$domain.$url.'</a>:
									<ul>';
									echo PHP_EOL;
								}
								
                                echo $errorsPerPage[$domain.$url].". ".$error->message." (Line ".$error->line.", Column ".$error->col.")".PHP_EOL;
                                $explanation = trim(htmlspecialchars_decode(strip_tags(str_replace(array("<i>", "</i>"), array("_", "_"), $error->explanation))));
                                $source = trim(utf8_decode(html_entity_decode(strip_tags($error->source))));
								$source = preg_replace("/\?$/", "...", $source);
								$source = preg_replace("/^\?/", "...", $source);
								echo "   ".$explanation.PHP_EOL;
                                echo "   ".$source.PHP_EOL;
                                $validation_page_content .= "<li>".$error->message." (Line ".$error->line.", Column ".$error->col.")<br/>".$error->explanation."<code>".$error->source."</code></li>";
							}
						}
						if (!array_key_exists($domain.$url, $errorsPerPage)) {
							$validation_page_content .= "</ul></p>";
						}
                }
        }
}

if (sizeof($errorsPerPage) > 0) {
	echo PHP_EOL.PHP_EOL."See also: http://wikidata-docs.wikimedia.de/htmlvalidation/".PHP_EOL;
}
$fp = fopen('index.html', 'w');
fwrite($fp, $validation_page_header.$validation_page_content.$validation_page_footer);
fclose($fp);

if (sizeof($errorsPerPage) > 0) exit(1);