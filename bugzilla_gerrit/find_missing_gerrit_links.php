<?php

$login = array();
$login[0] = "";
$pw = "";

include "jsonRPCClient.php";
include "gerritJsonRPCClient.php";
include "Fail.php";

$log_file = "../logs/gerrit_bugzilla_bot.log";

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


function search_gerrit_for_changes($merged = false) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_URL, "https://gerrit.wikimedia.org/r/gerrit/rpc/ChangeListService");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if ($merged) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"jsonrpc":"2.0","method":"allQueryNext","params":["(project:mediawiki/extensions/Wikibase OR project:mediawiki/extensions/Diff OR mediawiki/extensions/DataValues) AND branch:master AND status:merged", z, "50"],"id":1}');
	} else {
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"jsonrpc":"2.0","method":"allQueryNext","params":["(project:mediawiki/extensions/Wikibase OR project:mediawiki/extensions/Diff OR mediawiki/extensions/DataValues) AND branch:master AND status:open", z, "50"],"id":1}');
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8', 'Accept: application/json,application/jsonrequest'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	if($data === false) {
		echo 'Curl error: ' . curl_error($ch);
	}
	curl_close($ch);

	$response = json_decode($data,true);
	$changes = $response["result"]["changes"];

	$changes_array = array();
	echo sizeof($changes)." gerrit changes found".PHP_EOL;
	for($i = 0, $c = count($changes); $i < $c; ++$i) {
		$change = (object)$changes[$i];
		$change_id = $change->id;
		$change_id = (int) $change_id["id"];
		$changes_array[$change_id] = $change;
	}


	return $changes_array;
}

function bug ($id)
{
	$client = new jsonRPCClient("https://bugzilla.wikimedia.org/jsonrpc.cgi", faöse);

	try {
		$result = $client->call('Bug.get', array(array('ids' => array($id))));
	} catch (\Exception $e) {
		Fail::log($e);
		return false;
	}

	// send back a stdClass for the first bug in the results
	return (object)($result[1]['bugs'][0]);
}

function comments ($id)
{
	$client = new jsonRPCClient("https://bugzilla.wikimedia.org/jsonrpc.cgi", false);

	try {
		$result = $client->call('Bug.comments', array(array('ids' => array($id))));
	} catch (\Exception $e) {
		Fail::log($e);
		return false;
	}

	// send back a stdClass for the first bug in the results
	return (object)($result[1]['bugs'][(int) $id]["comments"]);
}

//var_dump(bug("41624"));
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
		$bug = (object)$result[1]['bugs'][$i];
		$bug_id = (int) $bug->id;
		$bugs[$bug_id] = $bug;
	}

	return $bugs;
}

function search_gerrit() {
	$client = new jsonRPCClient("https://gerrit.wikimedia.org/r/gerrit/rpc/ChangeListService");
	try {
		$result = ($client->call('allQueryNext', array()));
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

$bugs_client = search(array("component" => "WikidataClient", "product" => "MediaWiki extensions"));
$bugs_repo = search(array("component" => "WikidataRepo", "product" => "MediaWiki extensions"));
$bugs = $bugs_client + $bugs_repo;


$client = new jsonRPCClient("https://bugzilla.wikimedia.org/jsonrpc.cgi", false);
try {
	$result = $client->call('User.login', array(array("login" => $login[0], "password" => $pw, "remember" => false)));
	//$login[1] = $result[0];
	$login = $result[0];
	//$login[1] = $pw;
//	die();
} catch (\Exception $e) {
	Fail::log($e);
	die();
}

$changes = search_gerrit_for_changes(false);

foreach ($changes as $change_id => $change) {
	$change_hash = $change->key;
	$change_hash = $change_hash["id"];
	$short_change_hash = substr($change_hash, 0, 9);
	//echo $change->subject.PHP_EOL;
	if (preg_match("/^\(bug ([0-9]*)\) (.*)/i", trim($change->subject), $matches)) {
		$bug_id = (int) $matches[1];
		if (isset($bugs[$bug_id])) {
			$bug = $bugs[$bug_id];
			$comments = comments($bug_id);
			$comments_mentioning_commit = array();
			foreach ($comments as $comment) {
				$gerrit_url = "https:\/\/gerrit\.wikimedia\.org\/r\/#\/c\/";
				if (preg_match("/".$gerrit_url.$change_id."\//i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)".$change_id."($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)$change_hash($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)$short_change_hash($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
			}
			if (sizeof($comments_mentioning_commit) == 0) {
				$added_text =  "Adding https://gerrit.wikimedia.org/r/#/c/".$change_id."/ to https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug_id.".".PHP_EOL;
				echo $added_text;				
				file_put_contents($log_file, $added_text, FILE_APPEND | LOCK_EX);				
				var_dump($change);
				// .".\nChange merged."
				$result = $client->call('Bug.add_comment', array(array("id" => $bug_id, "comment" => "Change ".$short_change_hash.": ".$change->subject)), $login);
			}
		}
	} if (preg_match("/\(no bug\)/i", $change->subject, $matches) || preg_match("/\(minor\)/i", $change->subject, $matches)) {

	} else {

	}
}

$changes = search_gerrit_for_changes(true);

foreach ($changes as $change_id => $change) {
	$change_hash = $change->key;
	$change_hash = $change_hash["id"];
	$short_change_hash = substr($change_hash, 0, 9);
	//echo $change->subject.PHP_EOL;
	if (preg_match("/^\(bug ([0-9]*)\) (.*)/i", trim($change->subject), $matches)) {
		$bug_id = (int) $matches[1];
		if (isset($bugs[$bug_id])) {
			$bug = $bugs[$bug_id];
			$comments = comments($bug_id);
			$comments_mentioning_commit = array();
			foreach ($comments as $comment) {
				$gerrit_url = "https:\/\/gerrit\.wikimedia\.org\/r\/#\/c\/";
				if (preg_match("/".$gerrit_url.$change_id."\//i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)".$change_id."($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)$change_hash($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
				if (preg_match("/(^|\s|#)$short_change_hash($|\s|:|\.)/i", $comment["text"])) {
					$comments_mentioning_commit[] = $comment["text"];
				}
			}
			if (sizeof($comments_mentioning_commit) == 0) {
				$added_text = "Adding https://gerrit.wikimedia.org/r/#/c/".$change_id."/ to https://bugzilla.wikimedia.org/show_bug.cgi?id=".$bug_id.".".PHP_EOL;
				echo $added_text;				
				file_put_contents($log_file, $added_text, FILE_APPEND | LOCK_EX);
				var_dump($change);
				$result = $client->call('Bug.add_comment', array(array("id" => $bug_id, "comment" => "Change ".$short_change_hash.": ".$change->subject.".\nChange merged.")), $login);
			}
		}
	} if (preg_match("/\(no bug\)/i", $change->subject, $matches) || preg_match("/\(minor\)/i", $change->subject, $matches)) {

	} else {

	}
}

