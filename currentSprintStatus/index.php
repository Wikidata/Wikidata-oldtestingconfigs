<?php
//error_reporting(0);

include "jsonRPCClient.php";
include "Fail.php";

function get_content($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function search_gerrit_for_changes() {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_URL, "https://gerrit.wikimedia.org/r/gerrit/rpc/ChangeListService");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POSTFIELDS, '{"jsonrpc":"2.0","method":"allQueryNext","params":["status:merged AND (project:mediawiki/extensions/Wikibase OR project:mediawiki/extensions/Diff OR mediawiki/extensions/DataValues)", z, "200"],"id":1}');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8', 'Accept: application/json,application/jsonrequest'));
	$data = curl_exec($ch);
	
	if($data === false) {
		echo 'Curl error: ' . curl_error($ch);
	}
	curl_close($ch);
	$response = json_decode($data,true);
	$changes = $response["result"]["changes"];

	$changes_array = array();
	for($i = 0, $c = count($changes); $i < $c; ++$i) {
		$changes_array[] = (object)$changes[$i];
	}
	
	return $changes_array;
}

$ignore_bugs = array("41520", "41506");

function bug ($id)
{
	$client = new jsonRPCClient("https://bugzilla.wikimedia.org/jsonrpc.cgi", true);

	try {
		$result = $client->__call('Bug.get', array(array('ids' => array($id))));
	} catch (\Exception $e) {
		Fail::log($e);
		return false;
	}

	// send back a stdClass for the first bug in the results
	return (object)($result[1]['bugs'][0]);
}

/**
 * Return an array of objects representing search results.
 *
 * See: http://www.bugzilla.org/docs/4.2/en/html/api/Bugzilla/WebService/Bug.html#search
 *
 * @param array $params search fields => values
 * @return array of stdClass bug objects for given search
 * @return boolean false if search failed altogether (I think)
 */
function search (array $params)
{
	$client = new jsonRPCClient("https://bugzilla.wikimedia.org/jsonrpc.cgi");
	try {
		$result = ($client->call('Bug.search', array($params)));
	} catch (\Exception $e) {
		Fail::log($e);
		return false;
	}

	$bugs = array();
	for($i = 0, $c = count($result[1]['bugs']); $i < $c; ++$i) {
		$bugs[] = (object)$result[1]['bugs'][$i];
	}

	return $bugs;
}

$changes = search_gerrit_for_changes();

$bugs = search(array("component" => "WikidataClient", /*"component" => "WikidataRepo",*/ "product" => "MediaWiki extensions", "status" => "RESOLVED", "resolution" => "FIXED"));
$bugs_repo = search(array("component" => "WikidataRepo", "product" => "MediaWiki extensions", "status" => "RESOLVED", "resolution" => "FIXED"));
//echo "Out of ".sizeof($bugs)." Bugzilla bugs with status RESOLVED FIXED,".PHP_EOL;
$bugs = array_merge($bugs, $bugs_repo);

$file = get_content("http://meta.wikimedia.org/wiki/Wikidata/Development/Current_sprint");

$current_sprint_bugs = array();
$resolved_bug_ids = array();
$current_sprint_descriptions = array();

preg_match_all("/<a[^>]*href=\"https:\/\/bugzilla\.wikimedia\.org\/show_bug\.cgi\?id=([^\"]*)[^>]*>[^<]*<\/a>([^<]*)<\/li>/s", $file, $bugzilla_id_matches);
foreach($bugzilla_id_matches[1] as $id => $bugzilla_id) {
	if (is_numeric($bugzilla_id)) {
		$current_sprint_bugs[] = $bugzilla_id;
		$current_sprint_descriptions[$bugzilla_id] = $bugzilla_id_matches[2][$id];
	}
}

echo "<b>Current sprint items that are marked RESOLVED FIXED:</b><ul>";
foreach ($bugs as $bug) {
	$bug_id = (int) $bug->id;
	if (in_array($bug_id, $current_sprint_bugs)) {
		echo "<li><a href=\"https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug_id."\">$bug_id</a> ".$bug->summary;
		$resolved_bug_ids[] = $bug_id;
		$gerrit_changes = array();
		foreach ($changes as $change) {
			if (preg_match("/^\s*\(bug\s*(".$bug_id.")\)/i", $change->subject, $matches)) {
				$gerrit_id = $change->id;
				$gerrit_changes[] = "<a href=\"https://gerrit.wikimedia.org/r/#/c/".$gerrit_id["id"]."\">".$gerrit_id["id"]."</a>";
			}
		}
		if (sizeof($gerrit_changes) == 0) {
			echo "<br><b>No Gerrit changesets found !</b>";
		} else {
			echo "<br>Gerrit changesets: ".implode(", ",$gerrit_changes);
		}
		echo "</li>";
	} else {
	}
}

echo "</ul><b>Current sprint items that are not marked RESOLVED FIXED:</b><ul>";
foreach($current_sprint_bugs as $bug) {
	if (!in_array($bug, $resolved_bug_ids)) {
		echo "<li><a href=\"https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug."\">$bug</a> ".$current_sprint_descriptions[$bug]."</li>";
	}
}


die();

$sprints = preg_split("/<h2><span class=\"editsection\">.*?>Sprint ([0-9]*)<\/span><\/h2>/s", $file, 0, PREG_SPLIT_DELIM_CAPTURE);
array_shift($sprints);

$accepted_bugs = array();
$accepted_in_sprint = array();
$not_accepted_bugs = array();
$commited_bugs = array();
$commited_in_sprint = array();

$current_sprint_nr = 0;
foreach($sprints as $sprint) {
	if (is_numeric($sprint)) {
		$current_sprint_nr = (int) $sprint;
		continue;
	}

	if (!preg_match_all("/.*?>Accepted item.*?<.*?<ul>(.*?)<h[23]>/si", $sprint, $matches)) {
		preg_match_all("/.*?>Accepted item.*?<.*?<ul>(.*)<\/ul>/si", $sprint, $matches);
	}

	foreach($matches[1] as $id => $match) {
		$match = preg_replace("/^\s*/s", "", $match);
		$match = preg_replace("/(.*)\s*<\/ul>.*$/s", "$1", $match);
	}

	if (!preg_match_all("/.*?>Not accepted item.*?<.*?<ul>(.*?)<h[23]>/si", $sprint, $matches)) {
		preg_match_all("/.*?>Not accepted item.*?<.*?<ul>(.*)<\/ul>/si", $sprint, $matches);
	}

	foreach($matches[1] as $match) {
		preg_match_all("/<a href=\"https:\/\/bugzilla\.wikimedia\.org\/show_bug\.cgi\?id=([^\"]*)/s", $match, $bugzilla_id_matches);
		foreach($bugzilla_id_matches[1] as $bugzilla_id) {
			if (is_numeric($bugzilla_id)) {
				$not_accepted_bugs[] = $bugzilla_id;
			}
		}
	}

	preg_match_all("/.*?>Committed item.*?<.*?<ul>(.*?)<h[23]>/si", $sprint, $matches);

	foreach($matches[1] as $id => $match) {
		preg_match_all("/<a href=\"https:\/\/bugzilla\.wikimedia\.org\/show_bug\.cgi\?id=([^\"]*)/s", $match, $bugzilla_id_matches);
		foreach($bugzilla_id_matches[1] as $bugzilla_id) {
			if (is_numeric($bugzilla_id)) {
				$commited_bugs[] = $bugzilla_id;
				$commited_in_sprint[$bugzilla_id] = $current_sprint_nr;
			}
		}
	}
}

$not_accepted_but_resolved_fixed_bugs = array();
$verified_bugs = array();
foreach ($bugs as $bug) {
	$bug_id = (string) $bug->id;
	if (in_array($bug_id, $accepted_bugs)) {
		$verified_bugs[] = $bug_id;
	} else {
		if (array_key_exists($bug_id, $commited_in_sprint)) {
			if ($commited_in_sprint[$bug_id] == 24) {
				continue;
			}
		}
		if (in_array($bug_id, $ignore_bugs)) {
			continue;
		}
		$not_accepted_but_resolved_fixed_bugs[] = $bug_id;
	}
}

echo "Would be set to VERIFIED FIXED (".sizeof($verified_bugs)."):".PHP_EOL;
foreach ($verified_bugs as $bug) {
	echo "*  https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug. " (accepted in sprint ".$accepted_in_sprint[$bug].")".PHP_EOL;
}

foreach($ignore_bugs as $ignore_bug) {
	if (in_array($ignore_bug, $not_accepted_but_resolved_fixed_bugs)) {
		unset($not_accepted_but_resolved_fixed_bugs[array_search($ignore_bug, $not_accepted_but_resolved_fixed_bugs)]);
	}
}

echo "RESOLVED FIXED but not accepted (".sizeof($not_accepted_but_resolved_fixed_bugs)."):".PHP_EOL;
foreach ($not_accepted_but_resolved_fixed_bugs as $bug) {
	echo "* https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug;
	if (array_key_exists($bug, $commited_in_sprint)) {
		echo " (committed in sprint ".$commited_in_sprint[$bug].")";
	}
	echo PHP_EOL;
}