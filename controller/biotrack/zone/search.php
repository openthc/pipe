<?php
/**
	Return all Rooms - inventory_room and plant_room
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 203;

$obj_name = 'zone';

$out_detail = array();
$out_result = array();

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rbe']);

	// Load Inventory Rooms
	$out_detail[] = 'Loading Zone/Inventory';
	
	$rfn = function($src) {
		$src['_kind'] = 'Inventory';
		return $src;
	};
	$gfn = function($src) {
		return sprintf('inventory-%s', $src['roomid']);
	};
	
	$idx_update += RCE_Sync::biotrack_pull($rce, 'sync_inventory_room', $rfn, $gfn,
	
	$res_source = $rce->sync_inventory_room(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		foreach ($res_source['inventory_room'] as $src) {

			$src['_kind'] = 'Inventory';

			$guid = sprintf('inventory-%s', $src['roomid']);
			$hash = _hash_obj($src);

			if ($hash != $res_cached[ $guid ]) {

				$idx_update++;

				RCE_Sync::save($obj_name, $guid, $hash, $src);

			}
		}
	} else {
		$out_detail[] = $res_source['error'];
	}

	// Load Inventory Rooms
	$out_detail[] = 'Loading Zone/Plant';
	$res_source = $rce->sync_plant_room(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		foreach ($res_source['plant_room'] as $src) {

			$src['_kind'] = 'Plant';

			$guid = sprintf('plant-%s', $src['roomid']);
			$hash = _hash_obj($src);

			if ($hash != $res_cached[ $guid ]) {

				$idx_update++;

				RCE_Sync::save($obj_name, $guid, $hash, $src);

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

return $RES->withJSON(array(
	'status' => 'success',
	'detail' => $out_detail,
	'result' => $out_result,
), $ret_code, JSON_PRETTY_PRINT);
