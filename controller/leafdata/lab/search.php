<?php
/**
 * Return a List of QA Results
 */

use Edoceo\Radix\DB\SQL;

session_write_close();

$obj_name = 'lab_result';

$age = CRE_Sync::age($obj_name);
$age = CRE_Sync::MAX_AGE + 1;

// Load Cache Data
$sql = "SELECT guid, hash FROM {$obj_name}";
$res_cached = SQL::fetch_mix($sql);

// Load Fresh Data
if ($age >= CRE_Sync::MAX_AGE) {

	$cre = \CRE::factory($_SESSION['cre']);

	$res_source = $cre->qa()->all();
	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $cre->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result']['data'];

	foreach ($res_source as $src) {

		// Filter: Some objects from LeafData are missing the Global ID!! Skip Them
		if (empty($src['global_id'])) {
			continue;
		}

		$guid = $src['global_id'];
		$hash = _hash_obj($src);

		if ($hash != $res_cached[ $guid ]) {

			$idx_update++;

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
), $ret_code, JSON_PRETTY_PRINT);
