<?php

function getColorLevel($percent) {
	$lowUpperBound = 35;
	$highLowerBound = 70;
	
	if ($percent < $lowUpperBound) {
		$color = 'scarlet_red';
		$level = 'Lo';
	}

	else if ($percent >= $lowUpperBound &&
			 $percent <  $highLowerBound) {
		$color = 'butter';
		$level = 'Med';
	}

	else {
		$color = 'chameleon';
		$level = 'Hi';
	}

	return array($color, $level);
}

function getColorLevelNew($percent) {
	$lowUpperBound = 35;
	$highLowerBound = 70;
	
	if ($percent < $lowUpperBound) {
		$color = 'danger';
	}

	else if ($percent >= $lowUpperBound &&
			 $percent <  $highLowerBound) {
		$color = 'warning';
	}

	else {
		$color = 'success';
	}

	return $color;
}

function replaceTotalColumn($file, $numbers) {
	$lines_covered = array_sum($numbers[1]);
	$lines_total = array_sum($numbers[2]);
	$functions_covered = array_sum($numbers[3]);
	$functions_total = array_sum($numbers[4]);
	$classes_covered = array_sum($numbers[5]);
	$classes_total = array_sum($numbers[6]);
	
	$lines_percent = round($lines_covered/$lines_total*100, 2);
	$functions_percent = round($functions_covered/$functions_total*100, 2);
	$classes_percent = round($classes_covered/$classes_total*100, 2);
	
	list($lines_class_color, $lines_class_level) = getColorLevel($lines_percent);
	list($functions_class_color, $functions_class_level) = getColorLevel($functions_percent);
	list($classes_class_color, $classes_class_level) = getColorLevel($classes_percent);
	
	$wikibase_coverage = '
	<td class="coverBar">
		<div class="coverBarOutline" title="'.$lines_percent.'%">
		  <div class="size '.$lines_class_color.'"  style="width:'.$lines_percent.'%" title="'.$lines_percent.'%"></div>
		</div>
	  </td>
	  <td class="coverPer'.$lines_class_level.'">'.$lines_percent.'%</td>
	  <td class="coverNum'.$lines_class_level.'">'.$lines_covered.' / '.$lines_total.'</td>
	  <td class="coverBar">
		<div class="coverBarOutline" title="'.$functions_percent.'%">
		  <div class="size '.$functions_class_color.'"  style="width:'.$functions_percent.'%" title="'.$functions_percent.'%"></div>
		</div>
	  </td>
	  <td class="coverPer'.$functions_class_level.'">'.$functions_percent.'%</td>
	  <td class="coverNum'.$functions_class_level.'">'.$functions_covered.' / '.$functions_total.'</td>
	  <td class="coverBar">
		<div class="coverBarOutline" title="'.$classes_percent.'%">
		  <div class="size '.$classes_class_color.'"  style="width:'.$classes_percent.'%" title="'.$classes_percent.'%"></div>
		</div>
	  </td>
	  <td class="coverPer'.$classes_class_level.'">'.$classes_percent.'%</td>
	  <td class="coverNum'.$classes_class_level.'">'.$classes_covered.' / '.$classes_total.'</td>
	</tr>';

	$total_column = '        <tr>;
	  <td class="coverDirectory">Total</td>'.$wikibase_coverage;
	$file = preg_replace("/<tr>\s*<td class=\"coverDirectory\">Total<\/td>.*?<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<\/tr>/s", $total_column, $file);
	return array($file, $wikibase_coverage);
}

function replaceTotalColumnNew($file, $numbers) {
	$lines_covered = array_sum($numbers[1]);
	$lines_total = array_sum($numbers[2]);
	$functions_covered = array_sum($numbers[3]);
	$functions_total = array_sum($numbers[4]);
	$classes_covered = array_sum($numbers[5]);
	$classes_total = array_sum($numbers[6]);
	
	$lines_percent = round($lines_covered/$lines_total*100, 2);
	$functions_percent = round($functions_covered/$functions_total*100, 2);
	$classes_percent = round($classes_covered/$classes_total*100, 2);
	
	$lines_class_color = getColorLevelNew($lines_percent);
	$functions_class_color = getColorLevelNew($functions_percent);
	$classes_class_color = getColorLevelNew($classes_percent);
	
	$wikibase_coverage = '
      <td class="'.$lines_class_color.' big">       <div class="progress progress-'.$lines_class_color.'" style="width: 100px;">
        <div class="bar" style="width: '.$lines_percent.'%;"></div>
       </div>
</td>
      <td class="'.$lines_class_color.' small"><div align="right">'.$lines_percent.'%</div></td>
      <td class="'.$lines_class_color.' small"><div align="right">'.$lines_covered.' / '.$lines_total.'</div></td>
      <td class="'.$functions_class_color.' big">       <div class="progress progress-'.$functions_class_color.'" style="width: 100px;">
        <div class="bar" style="width: '.$functions_percent.'%;"></div>
       </div>
</td>
      <td class="'.$functions_class_color.' small"><div align="right">'.$functions_percent.'%</div></td>
      <td class="'.$functions_class_color.' small"><div align="right">'.$functions_covered.' / '.$functions_total.'</div></td>
      <td class="'.$classes_class_color.' big">       <div class="progress progress-'.$classes_class_color.'" style="width: 100px;">
        <div class="bar" style="width: '.$classes_percent.'%;"></div>
       </div>
</td>
      <td class="'.$classes_class_color.' small"><div align="right">'.$classes_percent.'%</div></td>
      <td class="'.$classes_class_color.' small"><div align="right">'.$classes_covered.' / '.$classes_total.'</div></td>
     </tr>';

	$total_column = '        <tr>
      <td class="success">Total</td>'.$wikibase_coverage;
	$file = preg_replace("/<tr>\s*<td[^>]*>Total<\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>\s*<\/tr>/s", $total_column, $file);
	return array($file, $wikibase_coverage);
}

$date = date("Ymd");
$coverage_directory = 'C:\\xampp\\htdocs\\wikibaseCoverage\\'.$date.'\\';
$client_coverage_directory = 'C:\\xampp\\htdocs\\wikibaseCoverageClient\\'.$date.'\\';

$wikibaseHtmlName = "extensions_Wikibase.html";
$extensionsHtmlName = "extensions.html";
$indexHtmlName = "index.html";

$handle = fopen($client_coverage_directory.$wikibaseHtmlName, "r");
$read = fread($handle, filesize($client_coverage_directory.$wikibaseHtmlName));

$new_coverage = true;
if (is_int(strstr($read, "PHP_CodeCoverage 1.1.2"))) {
	if (preg_match("/(<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"extensions_Wikibase_client\.html\">client<\/a><\/td>.*?<\/tr>)/s", $read, $matches)) {
		echo $coverage_directory.$wikibaseHtmlName;
		$handle_coverage = fopen($coverage_directory.$wikibaseHtmlName, "r");
		$read_coverage = fread($handle_coverage, filesize($coverage_directory.$wikibaseHtmlName));
		$read_coverage = preg_replace("/(<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"extensions_Wikibase_client\.html\">client<\/a><\/td>.*?<\/tr>)/s", "", $read_coverage);
		$read_coverage = preg_replace("/(<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"extensions_Wikibase_lib\.html\">lib<\/a><\/td>.*?<\/tr>)/s", $matches[1]."\n$1", $read_coverage);
		
		if (preg_match_all("/<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"[^\"]*\">[^<]*<\/a><\/td>.*?<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<\/tr>/s", $read_coverage, $matches)) {
			list($read_coverage, $wikibase_coverage) = replaceTotalColumn($read_coverage, $matches);
			
			$handle_coverage_new = fopen($coverage_directory.$wikibaseHtmlName, "w");
			if (fwrite($handle_coverage_new, $read_coverage) === FALSE) {
				echo "Cannot write to file $coverage_directory$wikibaseHtmlName";
			} else {
				echo "Written $coverage_directory$wikibaseHtmlName".PHP_EOL;
				
				$handle_coverage_extensions = fopen($coverage_directory.$extensionsHtmlName, "r");
				$read_coverage_extensions = fread($handle_coverage_extensions, filesize($coverage_directory.$extensionsHtmlName));
				
				$read_coverage_extensions = preg_replace("/(<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"extensions_Wikibase\.html\">Wikibase<\/a><\/td>).*?<\/tr>/s", "$1".$wikibase_coverage, $read_coverage_extensions);

				if (preg_match_all("/<tr>\s*<td class=\"coverDirectory\"><img alt=\"directory\" src=\"directory\.png\"\/>\s*<a href=\"[^\"]*\">[^<]*<\/a><\/td>.*?<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<td class=\"coverBar\">\s*<div class=\"coverBarOutline\" title=\"[^%]*%\">\s*<div class=\"size [^\"]*\"  style=\"width:[^%]*%\" title=\"[^%]*%\"><\/div>\s*<\/div>\s*<\/td>\s*<td class=\"coverPer[^\"]*\">[^%]*%<\/td>\s*<td class=\"coverNum[^\"]*\">([0-9]*) \/ ([0-9]*)<\/td>\s*<\/tr>/s", $read_coverage_extensions, $matches)) {
					list($read_coverage_extensions, $wikibase_coverage) = replaceTotalColumn($read_coverage_extensions, $matches);

					$handle_coverage_extensions_new = fopen($coverage_directory.$extensionsHtmlName, "w");
					if (fwrite($handle_coverage_extensions_new, $read_coverage_extensions) === FALSE) {
						echo "Cannot write to file $coverage_directory$extensionsHtmlName";
					} else {
						echo "Written $coverage_directory$extensionsHtmlName".PHP_EOL;
					}	
				}
			}
		}
		
		fclose($handle);
		fclose($handle_coverage);
		fclose($handle_coverage_new);
	}
} else {
	if (preg_match("/(<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"extensions_Wikibase_client\.html\">client<\/a><\/td>.*?<\/tr>)/s", $read, $matches)) {
		echo $coverage_directory.$wikibaseHtmlName;
		$handle_coverage = fopen($coverage_directory.$wikibaseHtmlName, "r");
		$read_coverage = fread($handle_coverage, filesize($coverage_directory.$wikibaseHtmlName));
		$read_coverage = preg_replace("/(<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"extensions_Wikibase_client\.html\">client<\/a><\/td.*?<\/tr>)/s", "", $read_coverage);
		$read_coverage = preg_replace("/(<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"extensions_Wikibase_lib\.html\">lib<\/a><\/td.*?<\/tr>)/s", $matches[1]."\n$1", $read_coverage);
		
		if (preg_match_all("/<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"[^\"]*\">[^<]*<\/a><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>\s*<\/tr>/s", $read_coverage, $matches)) {
			list($read_coverage, $wikibase_coverage) = replaceTotalColumnNew($read_coverage, $matches);
			
			$handle_coverage_new = fopen($coverage_directory.$wikibaseHtmlName, "w");
			if (fwrite($handle_coverage_new, $read_coverage) === FALSE) {
				echo "Cannot write to file $coverage_directory$wikibaseHtmlName";
			} else {
				echo "Written $coverage_directory$wikibaseHtmlName".PHP_EOL;
				
				$handle_coverage_extensions = fopen($coverage_directory.$extensionsHtmlName, "r");
				$read_coverage_extensions = fread($handle_coverage_extensions, filesize($coverage_directory.$extensionsHtmlName));
				
				$read_coverage_extensions = preg_replace("/(<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"extensions_Wikibase\.html\">Wikibase<\/a><\/td>).*?<\/tr>/s", "$1".$wikibase_coverage, $read_coverage_extensions);

				if (preg_match_all("/<tr>\s*<td[^>]*><i class=\"icon-folder-open\"><\/i>\s*<a href=\"[^\"]*\">[^<]*<\/a><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>.*?<td[^>]*>.*?<\/td>.*?<td[^>]*>.*?<\/td>\s*<td[^>]*><div align=\"right\">([0-9]*) \/ ([0-9]*)<\/div><\/td>\s*<\/tr>/s", $read_coverage_extensions, $matches)) {
					list($read_coverage_extensions, $wikibase_coverage) = replaceTotalColumn($read_coverage_extensions, $matches);

					$handle_coverage_extensions_new = fopen($coverage_directory.$extensionsHtmlName, "w");
					if (fwrite($handle_coverage_extensions_new, $read_coverage_extensions) === FALSE) {
						echo "Cannot write to file $coverage_directory$extensionsHtmlName";
					} else {
						echo "Written $coverage_directory$extensionsHtmlName".PHP_EOL;
					}	
				}
			}
		}
		
		fclose($handle);
		fclose($handle_coverage);
		fclose($handle_coverage_new);
	}
}