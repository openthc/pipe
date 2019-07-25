<?php
/**
 * Return a List of Licenses
 * @todo Make Licenses a Special / Global Data-Store
 */

use Edoceo\Radix\DB\SQL;

$ret_code = 304;

$obj_name = 'zone';

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
SQL::init('sqlite:' . $sql_file);

$dt0 = $_SERVER['REQUEST_TIME'];
$sql = sprintf("SELECT val FROM _config WHERE key = 'sync-{$obj_name}-time'");
$dt1 = intval(SQL::fetch_one($sql));
$age = $dt0 - $dt1;

if ($age >= CRE_Sync::MAX_AGE) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$cre = \CRE::factory($_SESSION['cre']);

	try {
		$res_source = $cre->zone()->search();
	} catch (Exception $e) {
		if (401 == $e->getCode()) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => $cre->formatError($res_source),
			), 401);
		}
	}

	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $cre->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result'];

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

$ret = array();

foreach ($res_source as $src) {

	if (empty($src['hash'])) {

		$key_list = array_keys($src);
		foreach ($key_list as $key) {
			$src[$key] = trim($src[$key]);
		}

		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		'name' => $src['name'],
		'code' => $src['code'],
		'guid' => $src['global_id'],
		'hash' => $src['hash'],
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		$ret_code = 200;

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

		unset($src['hash']);

		$sql = "INSERT OR REPLACE INTO {$obj_name} (guid, hash, meta) VALUES (:guid, :hash, :meta)";
		$arg = array(
			':guid' => $src['global_id'],
			':hash' => $rec['hash'],
			':meta' => json_encode($src),
		);

		SQL::query($sql, $arg);

	}

	$ret[] = $rec;

}

$arg = array("sync-{$obj_name}-time", time());
SQL::query("INSERT OR REPLACE INTO _config (key, val) VALUES (?, ?)", $arg);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);
