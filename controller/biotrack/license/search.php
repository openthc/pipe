<?php
/**
	Return All Licenses (vendor, qa_lab, third_party_transporter)
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 203;

$obj_name = 'license';

$out_detail = array();
$out_result = array();

$age = RCE_Sync::age($obj_name);


// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);


// Load Fresh Data?
if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rbe']);

	// Load Primary Licenses
	$out_detail[] = 'Loading Licenses';
	$res_source = $rce->sync_vendor(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		foreach ($res_source['vendor'] as $src) {

			//$src['_kind'] = RBE_Biotrack::$loc_type[$x['locationtype']]; // RBE_Biotrack'company';

			$guid = sprintf('%s-%s', $src['ubi'], $src['location']);
			$hash = _hash_obj($src);

			if ($hash != $res_cached[ $guid ]) {

				$idx_update++;

				RCE_Sync::save($obj_name, $guid, $hash, $src);

			}
		}
	} else {
		$out_detail[] = $res_source['error'];
	}


	// Load Labs
	$out_detail[] = 'Loading Labs';
	$res_source = $rce->sync_qa_lab(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));
	//_exit_json($res_source);

	if (1 == $res_source['success']) {
		foreach ($res_source['qa_lab'] as $src) {

			$src['_kind'] = 'QA';

			$guid = trim($src['location']);
			$hash = _hash_obj($src);

			if ($hash != $res_cached[ $guid ]) {

				$idx_update++;

				RCE_Sync::save($obj_name, $guid, $hash, $src);

			}
		}
	} else {
		$out_detail[] = $res_source['error'];
	}


	// Load Transporters
	$out_detail[] = 'Loading Transporters';
	$res_source = $rce->sync_third_party_transporter(array(
		'min' => intval($_GET['min']),
		'max' => intval($_GET['max']),
	));

	if (1 == $res_source['success']) {
		foreach ($res_source['third_party_transporter'] as $src) {

			$src['_kind'] = 'Carrier';

			$guid = sprintf('%s-%s', $src['ubi'], $src['license_number']);
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
