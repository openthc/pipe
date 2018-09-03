<?php
/**
	Return all Strain - Special Case in BioTrack

	Scan Plant and Inventory for Unique Strain
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'strain';

$res_output = array();

$sql = 'SELECT meta FROM plant';
$res = SQL::fetch($sql);
if ($res && ($res->rowCount() > 0)) {
	foreach ($res as $rec) {
		$m = json_decode($rec['meta'], true);
		$s = trim($m['strain']);
		$res_output[$s] = $s;
	}
}

$sql = 'SELECT meta FROM inventory';
$res = SQL::fetch($sql);
if ($res && ($res->rowCount() > 0)) {
	foreach ($res as $rec) {
		$m = json_decode($rec['meta'], true);
		$s = trim($m['strain']);
		$res_output[$s] = $s;
	}
}


ksort($res_output);

RCE_Sync::age($obj_name, time());

// $RES = $RES->withHeader('x-openthc-update', $idx_update);

$ret_code = ($idx_update ? 200 : 203);

return $RES->withJSON(array(
	'status' => 'success',
	'detail' => $out_detail,
	'result' => $out_result,
), $ret_code, JSON_PRETTY_PRINT);



