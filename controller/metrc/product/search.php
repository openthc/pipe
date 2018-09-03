<?php
/**
	Handles a Proxy to /packages/v1/{id}
*/

use Edoceo\Radix\DB\SQL;

$obj_name = 'product';

$age = RCE_Sync::age($obj_name);

if ($age >= RCE_Sync::MAX_AGE) {

	$rce = \RCE::factory($_SESSION['rce']);

	// /items/v1/categories
	$res = $rce->itemList('active');
	//$res = $rce->packageList('active');

	foreach ($res['result'] as $x) {

		$rec = array(
			'name' => trim($x['Name']),
			'base' => trim($x['ProductCategoryType']),
			'mode' => trim($x['QuantityType']),
			'_source' => $x,
		);

		$ret[] = $rec;

	}

}



return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
