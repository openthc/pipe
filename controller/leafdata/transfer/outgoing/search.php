<?php
/**
	Return a List of Outgoing Transfer Objects
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'transfer';

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rce']);

	$res_source = new RCE_Iterator_LeafData($rce->transfer());

	foreach ($res_source as $src) {

		$guid = $src['global_id'];

		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $guid ]) {

			$idx_update++;

			// Fully Inflate Transfer Object
			$src = $rce->transfer()->one($guid);// inventory_transfer_items

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
	} elseif ('true' == $_GET['source']) {
		$out['_source'] = json_decode($src['meta'], true);
	}

	$res_output[] = $out;

//	$obj = array();
//	$obj['guid'] = $src['global_id'];
//	$obj['hash'] = $src['hash'];
//	$obj['source_license_guid'] = $src['global_from_mme_id'];
//	$obj['target_license_guid'] = $src['global_to_mme_id'];
//	$obj['type'] = $src['manifest_type'];
//	$obj['status'] = $src['status'];
//	$obj['status_void'] = $src['void'];
	//$obj['carrier_license_guid'] = $src['global_transporting_mme_id'];

}


$ret_code = ($idx_update ? 200 : 203);

$RES = $RES->withHeader('x-openthc-age', $age);
$RES = $RES->withHeader('x-openthc-update', $idx_update);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res_output,
), $ret_code, JSON_PRETTY_PRINT);
