<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
        if (isset($_GET['folder'])) {
		                $folder = $_GET['folder'].".html";
				        } else {
						                $folder = "extensions.html";
								        }
?>
		<title>Wikidata Test Coverage - <?php echo str_replace(".html", "", $folder); ?></title>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script type="text/javascript">
			var options = {
				chart: {
						           renderTo: 'container',
								             defaultSeriesType: 'line'
										         },
					       title: {
						       text: 'Wikidata Test Coverage - <?php echo str_replace(".html", "", $folder); ?>'
										        },
xAxis: 
						      {
categories: [],
	    tickInterval: 3 
												         },
								     yAxis: {
									     min: 0, max: 100,

									     title: {
													               text: '%'
																             }
												       },
									        series: []
			};
			$.get('update/coverage_<?php echo $folder; ?>.csv', function(data) {
    			// Split the lines
        		var lines = data.split('\n');
	    
	    		// Iterate over the lines and add categories or series
	        	$.each(lines, function(lineNo, line) {
		        var items = line.split(',');
			        
			// header line containes categories
			if (lineNo == 0) {
				$.each(items, function(itemNo, item) {
					if (itemNo > 0) options.xAxis.categories.push(item);
				});
			}
			
			// the rest of the lines contain data with their name in the first position
			else {
				var series = {
					data: []
				};
				$.each(items, function(itemNo, item) {
				if (itemNo == 0) {
					series.name = item;
				} else {
					series.data.push(parseFloat(item));
				}
			});
			options.series.push(series);
			}
			});
																																			    
		    // Create the chart
		    var chart = new Highcharts.Chart(options);
		});
		</script>
	</head>
	<body>
		<script src="js/js/highcharts.js"></script>
		<script src="js/js/modules/exporting.js"></script>
		<a href="http://meta.wikimedia.org/wiki/Wikidata"><img src="http://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Wikidata-logo-en.svg/160px-Wikidata-logo-en.svg.png" /></a>
		<br/>
		This diagram depicts the development of test coverage for Wikidata over time.<br/>
		Current directory: <?php


		function startsWith($haystack, $needle)
		{
			$length = strlen($needle);
			return (substr($haystack, 0, $length) === $needle);
		}

		function endsWith($haystack, $needle)
		{
			    $length = strlen($needle);
			        if ($length == 0) {
					        return true;
						    }

			        return (substr($haystack, -$length) === $needle);
		}



		$partPath = "";
		function create_links($folder) {
			global $partPath;

			$folders = explode("_", str_replace(".html", "", $folder));
			foreach ($folders as $id => $part) {
				$partPath .= $part;
				echo "<a href=\"index.php?folder=$partPath\">$part</a>";
				if ($id < (sizeof($folders) - 1)) {
					echo "/";
	                $partPath .= "_";
				}
			}
		}
		create_links($folder);
		$date="20120919";
	        $csv = file_get_contents("update/coverage_".$folder.".csv");
	        $csv_lines = explode("\n", $csv);
	        $dates = explode(",",$csv_lines[0]);
	        $last_date= $dates[sizeof($dates)-1];
	        $last_date = str_replace("/", "", $last_date);
		?> (<a href="phpcoverage/<?php echo $last_date."/".$partPath; ?>.html">PHP CodeCoverage</a>)
		<br/>
	<?php
		$subfolderLinks = array();
			$subfolders = scandir("update/");
			foreach($subfolders as $subfolder) {
				if (!endsWith($subfolder, ".csv")) continue;
				$subfolder = str_replace(".html.csv", "", $subfolder);
				if (endsWith($subfolder, ".php")) {
					continue;
				}
				$subfolder = substr($subfolder, strlen("coverage_"), strlen($subfolder)-1);
				if (startsWith($subfolder, $partPath)) {
					$subfolder = substr($subfolder, strlen($partPath)+1, strlen($subfolder)-strlen($partPath));
					//echo $subfolder."<br/>";
					if ((strlen($subfolder) > 0) && (strpos($subfolder, '_') === false)) {
						$subfolderLinks[] = "<li><a href=\"index.php?folder=$partPath"."_$subfolder\">$subfolder</a></li>";
					}
				}
			}
		if (sizeof($subfolderLinks) > 0) {
			echo "Subfolders:
				        <ul>".implode($subfolderLinks)."</ul>";

		}
		?>

		<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

