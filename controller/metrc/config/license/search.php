<?php
/**
	Return a List of Licenses
	In METRC this is only YOUR licenses
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 200;

$obj_name = 'license';

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
SQL::init('sqlite:' . $sql_file);

$dt0 = $_SERVER['REQUEST_TIME'];
$sql = sprintf("SELECT val FROM _config WHERE key = 'sync-{$obj_name}-time'");
$dt1 = intval(SQL::fetch_one($sql));
$age = $dt0 - $dt1;

if ($age >= 240) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$rbe = \RCE::factory($_SESSION['rbe']);

	$res_source = $rbe->facilitiesList();

	if (200 != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rbe->formatError($res_source),
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

	_exit_json($_SESSION['sql-hash']);
}

$ret = array();

foreach ($res_source as $src) {

	if (empty($src['hash'])) {
		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		'name' => trim($src['Name']),
		'code' => $src['License']['Number'],
		'guid' => $src['License']['Number'],
		'hash' => $src['hash'],
		'type' => trim($src['License']['LicenseType']),
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		$ret_code = 200;

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

		unset($src['hash']);

		$sql = "INSERT OR REPLACE INTO {$obj_name} (guid, hash, meta) VALUES (:guid, :hash, :meta)";
		$arg = array(
			':guid' => $rec['guid'],
			':hash' => $rec['hash'],
			':meta' => json_encode($src),
		);

		SQL::query($sql, $arg);

	}

	$ret[] = $rec;

}

$arg = array("sync-{$obj_name}-time", time());
SQL::query("INSERT OR REPLACE INTO _config (key, val) VALUES (?, ?)", $arg);

$RES = $RES->withHeader('openthc-age', $age);
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);
