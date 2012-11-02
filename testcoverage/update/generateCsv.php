<?php

function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}

$phpCoverageFolder = "../phpcoverage/";
$folders = scandir($phpCoverageFolder);
$coverage_lines = array();
$coverage_functions = array();
$coverage_classes = array();
$all_files = array();
foreach ($folders as $folder) {
	if (($folder == ".") || ($folder == "..")) continue;
	
	$files = scandir($phpCoverageFolder.$folder);
	foreach ($files as $file) {
		if (endsWith($file, ".html") && (!endsWith($file, ".dashboard.html"))) {
			if (!in_array($file, $all_files)) {
				$all_files[] = $file;
			}
			$filePath = $phpCoverageFolder.$folder."/".$file;
			$data = file_get_contents($filePath);
			preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})/", $folder, $matches);
			$date = $matches[1]."/".$matches[2]."/".$matches[3];
			preg_match("/<td class=\"coverDirectory\">Total<.*?<div class=\"coverBarOutline\" title=\"([^%]*).*?<div class=\"coverBarOutline\" title=\"([^%]*).*?<div class=\"coverBarOutline\" title=\"([^%]*)/s", $data, $matches);
			$coverage_lines[$file][$date] = $matches[1];
			$coverage_functions[$file][$date] = $matches[2];
			$coverage_classes[$file][$date] = $matches[3];
		}
	}
}

foreach($all_files as $file) {
	$coverage_lines_text = "Date,".implode(",",array_keys($coverage_lines[$file]))."\n"."Lines,".implode(",",array_values($coverage_lines[$file]));
	$coverage_lines_text .= "\n"."Functions,".implode(",",array_values($coverage_functions[$file]));
	$coverage_lines_text .= "\n"."Classes,".implode(",",array_values($coverage_classes[$file]));
	$file = fopen("coverage_".$file.".csv","w");
	echo fwrite($file,$coverage_lines_text);
	fclose($file);
}

?>
