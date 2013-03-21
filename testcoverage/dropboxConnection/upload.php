<?php

/**
 * Upload a file to the authenticated user's Dropbox
 * @link https://www.dropbox.com/developers/reference/api#files-POST
 * @link https://github.com/BenTheDesigner/Dropbox/blob/master/Dropbox/API.php#L80-110
 */

$wikibaseCoverageFolder = '';

// Require the bootstrap
require_once('bootstrap.php');

$metaData = $dropbox->metaData();
$filesinDropbox = array();
foreach($metaData['body']->contents as $folder){
	$folderName = str_replace("/", "", $folder->path);

	$filesinDropbox[] = $folderName;
}

$files = scandir($wikibaseCoverageFolder);

foreach($files as $file) {
	if (($file != ".") && ($file != "..") && (!in_array($file, $filesinDropbox))) {
		$h = fopen($wikibaseCoverageFolder.'\\'.$file, 'r');
		$put = $dropbox->putFile($wikibaseCoverageFolder.'\\'.$file, $file);
	}
}

echo "<p id=\"done\">Done uploading.</p>";