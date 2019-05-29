<?php
/**
 * Return a List of Outgoing Transfer Objects
 */

use Edoceo\Radix\DB\SQL;

$obj_name = 'transfer_outgoing';

$age = CRE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= CRE_Sync::MAX_AGE) {

	$cre = \CRE::factory($_SESSION['cre']);

	$res_source = new CRE_Iterator_LeafData($cre->transfer());

	foreach ($res_source as $src) {

		$guid = $src['global_id'];

		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $guid ]) {

			$idx_update++;

			// Fully Inflate Transfer Object
			// $src = $cre->transfer()->one($guid);// inventory_transfer_items

			CRE_Sync::save($obj_name, $guid, $hash, $src);

		}
	}

	CRE_Sync::age($obj_name, time());

}


// Now Fetch all from DB and Send Back
$res_output = array();
$sql = "SELECT guid, hash, meta FROM {$obj_name} ORDER BY guid DESC";
$res_source = SQL::fetch_all($sql);

foreach ($res_source as $src) {

	$out = array(
		'guid' => $src['guid'],
		'hash' => $src['hash'],
		//'status' => VOID|LIVE/$src['status'],
		//	$obj['status'] = $src['status'];
		//	$obj['status_void'] = $src['void'];
		'source_license_guid' => $src['global_from_mme_id'],
		'target_license_guid' => $src['global_to_mme_id'],
		//'carrier_license_guid' => $src['global_transporting_mme_id'];
	);

	if ($out['hash'] != $res_cached[ $out['guid'] ]) {
		$out['_updated'] = 1;
		$out['_source'] = json_decode($src['meta'], true);
	} elseif ('true' == $_GET['source']) {
		$out['_source'] = json_decode($src['meta'], true);
	}

	$res_output[] = $out;

}


$ret_code = ($idx_update ? 200 : 203);

$RES = $RES->withHeader('x-openthc-age', $age);
$RES = $RES->withHeader('x-openthc-update', $idx_update);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res_output,
), $ret_code);
