<?php
/**
	List of All Inventory & Inventory_Sample Objects
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'lot';

$out_detail = array();
$out_result = array();

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rbe']);

	// Load Inventory
	$out_detail[] = 'Loading Inventory';
	$res_source = $rce->sync_inventory(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		foreach ($res_source['inventory'] as $src) {

			$guid = $src['id'];
			$hash = _hash_obj($src);

			if ($hash != $res_cached[ $guid ]) {

				$idx_update++;

				RCE_Sync::save($obj_name, $guid, $hash, $src);

			}
		}
	} else {
		$out_detail[] = $res_source['error'];
	}

	// Load Inventory
	$out_detail[] = 'Loading Inventory/Sample';
	$res_source = $rce->sync_inventory_sample(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		if (!empty($res_source['inventory_sample'])) {
			foreach ($res_source['inventory_sample'] as $src) {

				$guid = $src['id'];
				$hash = _hash_obj($src);

				if ($hash != $res_cached[ $guid ]) {

					$idx_update++;

					RCE_Sync::save($obj_name, $guid, $hash, $src);

				}
			}
		}
	} else {
		$out_detail[] = $res_source['error'];
	}

	RCE_Sync::age($obj_name, time());
}


// Now Fetch all from DB and Send Back
$sql = "SELECT guid, hash, meta FROM {$obj_name} ORDER BY guid DESC";
$res_source = SQL::fetch_all($sql);

foreach ($res_source as $src) {

	$add_source = false;

	$out = array(
		'guid' => $src['guid'],
		'hash' => $src['hash'],
	);

	if ($out['hash'] != $res_cached[ $out['guid'] ]) {

		$add_source = true;
		$out['_hash0'] = $res_cached[ $out['guid'] ];
		$out['_hash1'] = $out['hash'];
		$out['_updated'] = 1;

	}

	if (!empty($_GET['f-source'])) {
		$add_source = true;
	}

	if ($add_source) {
		$out['_source'] = json_decode($src['meta'], true);
	}

	$out_result[] = $out;

}

// $RES = $RES->withHeader('x-openthc-update', $idx_update);

$ret_code = ($idx_update ? 200 : 203);

return $RES->withJSON(array(
	'status' => 'success',
	'detail' => $out_detail,
	'result' => $out_result,
), $ret_code, JSON_PRETTY_PRINT);


///////////////////////////////////////////////////////////////////////
if (!empty($res['inventory'])) {
	foreach ($res['inventory'] as $rec) {
		$ret[] = array(
			'guid' => $rec['id'],
			'strain' => array('name' => $rec['strain']),
			'product' => array(
				'name' => $rec['productname'],
				'type' => $rec['inventorytype'],
				'unit' => array(
					'type' => 'bulk',
					'count' => $rec['remaining_quantity'],
					'weight' => $rec['usable_weight'],
				)
			),
			'_source' => $rec,
		);
	}
}

//$res = $rce->sync_inventory_adjust(0);
//switch ($res['success']) {
//case 0:
//	break;
//case 1:
//	if (!empty($res['inventory_adjust'])) {
//		foreach ($res['inventory_adjust'] as $rec) {
//			// $ret[] = $rec;
//		}
//	}
//	break;
//}

// @todo Unify Ouput According to OpenTHC Specification

$ret_code = ($idx_update ? 200 : 203);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);
