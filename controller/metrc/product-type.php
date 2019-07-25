<?php
/**
 * Return a List of Product Types
 */

$rce = \CRE::factory($_SESSION['cre']);

// /items/v1/categories
try {
	$res = $rce->itemCategoryList();
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
// $res = $rce->packageTypeList();
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
