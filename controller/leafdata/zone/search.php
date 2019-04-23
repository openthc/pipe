<?php
/**
 * Return a List of Zones
 */

use Edoceo\Radix\DB\SQL;

$obj_name = 'zone';

$age = RCE_Sync::age($obj_name);
// If client requested no-cache
if (!empty($_SERVER['HTTP_CACHE_CONTROL'])) {
	if ('no-cache' == $_SERVER['HTTP_CACHE_CONTROL']) {
		$age = RCE_Sync::MAX_AGE + 1;
	}
}


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rce']);

	$res_source = new RCE_Iterator_LeafData($rce->zone());

	foreach ($res_source as $src) {

		$guid = $src['global_id'];
		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $guid ]) {

			$idx_update++;

			RCE_Sync::save($obj_name, $guid, $hash, $src);

		}
	}

	RCE_Sync::age($obj_name, time());

}


// Now Fetch all from DB and Send Back
$res_output = array();
$sql = "SELECT guid, hash, meta FROM {$obj_name} ORDER BY guid DESC";
$res_source = SQL::fetch_all($sql);

foreach ($res_source as $src) {

	$out = array(
		'guid' => $src['guid'],
		'hash' => $src['hash'],
	);

	if ($out['hash'] != $res_cached[ $out['guid'] ]) {
		$out['_updated'] = 1;
		$out['_source'] = json_decode($src['meta'], true);
	}

	$res_output[] = $out;

}


$ret_code = ($idx_update ? 200 : 203);

$RES = $RES->withHeader('x-openthc-update', $idx_update);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res_output,
), $ret_code, JSON_PRETTY_PRINT);
