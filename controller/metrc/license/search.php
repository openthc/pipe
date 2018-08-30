<?php
/**
	Return a List of Licenses
	In METRC this is only YOUR licenses
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'license';

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rbe']);

	$res_source = $rce->facilitiesList();
	$res_source = $res_source['result'];

	foreach ($res_source as $src) {

		$guid = $src['License']['Number'];
		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $guid ]) {

			$idx_update++;

			RCE_Sync::save($obj_name, $guid, $hash, $src);
		}
	}

	RCE_Sync::age($obj_name, time());

	$RES = $RES->withHeader('x-openthc-update', $idx_update);

}


// Now Fetch all from DB and Send Back
$res_output = array();
$sql = "SELECT guid, hash, meta FROM {$obj_name} ORDER BY guid DESC";
$res_source = SQL::fetch_all($sql);

foreach ($res_source as $src) {

	$m = json_decode($src['meta'], true);

	$out = array(
		'guid' => $src['guid'],
		'hash' => $src['hash'],
		'name' => trim($m['Name']),
		'code' => $m['License']['Number'],
		'type' => trim($m['License']['LicenseType']),
	);

	if ($out['hash'] != $res_cached[ $out['guid'] ]) {
		$out['_updated'] = 1;
		$out['_source'] = $m;
	}

	$res_output[] = $out;

}


$ret_code = ($idx_update ? 200 : 203);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res_output,
), $ret_code, JSON_PRETTY_PRINT);
