<?php
/**
	Return a List of Licenses
	In METRC this is only YOUR licenses
*/

use Edoceo\Radix\DB\SQL;


$obj_name = 'strain';

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
SQL::init('sqlite:' . $sql_file);

$dt0 = $_SERVER['REQUEST_TIME'];
$sql = sprintf("SELECT val FROM _config WHERE key = 'sync-{$obj_name}-time'");
$dt1 = intval(SQL::fetch_one($sql));
$age = $dt0 - $dt1;

if ($age >= RCE_Sync::MAX_AGE) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$rce = \RCE::factory($_SESSION['rbe']);

	$res_source = $rce->strainList();

	if (200 != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rce->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result'];

} else {

	// From Cache
	$res_cached = array();
	$res_source = array();

	$sql = "SELECT guid, hash, meta FROM {$obj_name}";
	$res = SQL::fetch_all($sql);

	foreach ($res as $rec) {

		$res_cached[ $rec['guid'] ] = $rec['hash'];

		$x = json_decode($rec['meta'], true);
		$x['hash'] = $rec['hash'];

		$res_source[] = $x;
	}

}

$ret = array();

foreach ($res_source as $src) {

	if (empty($src['hash'])) {
		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		'guid' => $src['Id'],
		'name' => trim($src['Name']),
		//'base' => trim($src['ProductCategoryType']),
		//'mode' => trim($src['QuantityType']),
		'hash' => $src['hash'],
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		$ret_code = 200;

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

		unset($src['hash']);

		RCE_Sync::save($obj_name, $guid, $hash, $src);

	}

	$ret[] = $rec;

}

RCE_Sync::age($obj_name, time());


$ret_code = ($idx_update ? 200 : 203);


$RES = $RES->withHeader('openthc-age', $age);
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);
