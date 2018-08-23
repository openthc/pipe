<?php
/**
	Return a List of Inventory Lot Records
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'lot';

$ret_code = 304;

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
SQL::init('sqlite:' . $sql_file);

$dt0 = $_SERVER['REQUEST_TIME'];
$dt1 = intval(SQL::fetch_one("SELECT val FROM _config WHERE key = 'sync-{$obj_name}-time'"));
$age = $dt0 - $dt1;

if ($age >= 240) {

	$res_cached = SQL::fetch_mix("SELECT guid, hash FROM {$obj_name}");

	$rbe = \RCE::factory($_SESSION['rbe']);

	$res_source = $rbe->inventory()->all();
	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rbe->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result']['data'];

} else {

	$res_cached = array();
	$res_source = array();

	$res = SQL::fetch_all("SELECT hash, meta FROM {$obj_name}");

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
		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		//'name' => trim($src['name']),
		//'code' => trim($src['external_id']),
		'guid' => $src['global_id'],
		'hash' => $src['hash'],
		//'_source' => $src,
		// '_update' => 0,
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		unset($src['hash']);

		$ret_code = 200;

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

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
