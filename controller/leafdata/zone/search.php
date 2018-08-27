<?php
/**
	Return a List of Zones
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 304;

$obj_name = 'zone';

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= 240) {

	$rce = \RCE::factory($_SESSION['rbe']);

	$res_source = new RCE_Iterator_LeafData($rce->area());

	foreach ($res_source as $src) {

		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $src['global_id'] ]) {

			$idx_update++;

			$sql = "INSERT OR REPLACE INTO {$obj_name} (guid, hash, meta) VALUES (:guid, :hash, :meta)";
			$arg = array(
				':guid' => $src['global_id'],
				':hash' => $hash,
				':meta' => json_encode($src),
			);

			SQL::query($sql, $arg);

		}
	}

	$RES = $RES->withHeader('x-openthc-update', $idx_update);

}


// Now Fetch all from DB and Send Back
$res_output = array();
$sql = "SELECT guid, hash, meta FROM {$obj_name} ORDER BY guid DESC";
$res_source = SQL::fetch_all($sql);

foreach ($res_source as $src) {

	$out = array(
		'guid' => $src['guid'],
		'hash' => $src['hash'],
		//'code' => trim($src['external_id']),
		//'name' => sprintf('%s in %s', trim($src['strain_name']), trim($src['area_name'])),
	);

	if ($out['hash'] != $res_cached[ $out['guid'] ]) {
		$out['_updated'] = 1;
		$out['_source'] = json_decode($src['meta'], true);
	}

	$res_output[] = $out;

}

RCE_Sync::age($obj_name, time());

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res_output,
), $ret_code, JSON_PRETTY_PRINT);
