<?php
/**
	Return a List of QA Results
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 203;

$obj_name = 'qa';

$age = RCE_Sync::age($obj_name);

if ($age >= RCE_Sync::MAX_AGE) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$rce = \RCE::factory($_SESSION['rce']);

	$res_source = $rce->qa()->all();
	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rce->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result']['data'];

} else {

	// From Cache
	$res_cached = array();
	$res_source = array();

	$sql = "SELECT hash, meta FROM {$obj_name}";
	$res = SQL::fetch_all($sql);

	foreach ($res as $rec) {

		$x = json_decode($rec['meta'], true);
		$x['hash'] = $rec['hash'];

		$res_cached[ $x['global_id'] ] = $x['hash'];
		$res_source[] = $x;
	}

}

$ret = array();

foreach ($res_source as $src) {

	if (empty($src['hash'])) {
		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		'guid' => $src['global_id'],
		'hash' => $src['hash'],
		'name' => trim($src['name']),
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		$ret_code = 200;

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

		unset($src['hash']);

		RCE_Sync::save($obj_name, $guid, $hash, $src);

		SQL::query($sql, $arg);

	}

	$ret[] = $rec;

}


RCE_Sync::age($obj_name, time());

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);
