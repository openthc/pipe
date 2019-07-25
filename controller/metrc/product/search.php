<?php
/**
 * Handles a Proxy to /packages/v1/{id}
 */

use Edoceo\Radix\DB\SQL;

$obj_name = 'product';

$age = CRE_Sync::age($obj_name);

if ($age >= CRE_Sync::MAX_AGE) {

	$rce = \CRE::factory($_SESSION['cre']);

	// /items/v1/categories
	$res = $rce->product()->search('active');
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
