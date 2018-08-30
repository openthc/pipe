<?php
/**
	Return a List of Outgoing Transfer Objects
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 304;

$obj_name = 'transfer';

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
SQL::init('sqlite:' . $sql_file);

$dt0 = $_SERVER['REQUEST_TIME'];
$sql = sprintf("SELECT val FROM _config WHERE key = 'sync-{$obj_name}-time'");
$dt1 = intval(SQL::fetch_one($sql));
$age = $dt0 - $dt1;

if ($age >= 240) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$rce = \RCE::factory($_SESSION['rbe']);

	$res_source = $rce->transfer()->all();
	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rce->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result']['data'];

} else {

	// From Cache
	$res_cached = array();
	$res_source = array();

	$sql = "SELECT hash, meta FROM {$obj_name}";
	$res = SQL::fetch_all($sql);

	foreach ($res as $rec) {

		$x = json_decode($rec['meta'], true);
		$x['hash'] = $rec['hash'];

		$res_cached[ $x['global_id'] ] = $x['hash'];
		$res_source[] = $x;
	}

}


foreach ($res_source as $src) {

	if (empty($src['hash'])) {
		$src['hash'] = _hash_obj($src);
	}

	$obj = array();
	$obj['guid'] = $src['global_id'];
	$obj['hash'] = $src['hash'];
	$obj['source_license_guid'] = $src['global_from_mme_id'];
	$obj['target_license_guid'] = $src['global_to_mme_id'];
	$obj['type'] = $src['manifest_type'];
	$obj['status'] = $src['status'];
	$obj['status_void'] = $src['void'];
	//$obj['carrier_license_guid'] = $src['global_transporting_mme_id'];

	if ($obj['hash'] != $res_cached[ $obj['guid'] ]) {

		$ret_code = 200;

		$obj['_source'] = $src;
		$obj['_updated'] = 1;

		unset($src['hash']);

		RCE_Sync::save($obj_name, $guid, $hash, $src);

	}

	$obj_output[] = $obj;
}

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $obj_output,
), $ret_code, JSON_PRETTY_PRINT);
