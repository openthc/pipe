<?php
/**
	Return a List of Licenses
*/

$rbe = \RCE::factory($_SESSION['rbe']);

// /items/v1/categories
try {
	$res = $rbe->itemCategoryList();
} catch (Exception $e) {
	if (401 == $e->getCode()) {
		//return $RES->withJSON(array(
		_exit_json(array(
			'status' => 'failure',
			'detail' => 'Not Allowed',
		), 401);
	}

	throw $e;
}


// /packages/v1/types
// $res = $rbe->packageTypeList();
// $ret = array();

foreach ($res['result'] as $x) {

	$rec = array(
		'name' => trim($x['Name']),
		'base' => trim($x['ProductCategoryType']),
		'mode' => trim($x['QuantityType']),
		'_source' => $x,
	);

	$ret[] = $rec;

}


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
