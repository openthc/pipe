<?php
/**
 * Return all Products - Special Case in BioTrack
 * Scan Inventory for Unique Product Names
 */

use Edoceo\Radix\DB\SQL;

$ret_code = 203;

$obj_name = 'product';

$out_detail = array();
$out_result = array();

$age = CRE_Sync::age($obj_name);

$sql = 'SELECT meta FROM lot';
$res = SQL::fetch($sql);
foreach ($res as $rec) {
	$m = json_decode($rec['meta'], true);
	print_r($m);
	exit;
	$p = trim($rec['product_name']);
	$out_result[$p] = $p;
}

ksort($out_result);

$ret_code = ($idx_update ? 200 : 203);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $out_result,
), $ret_code, JSON_PRETTY_PRINT);
