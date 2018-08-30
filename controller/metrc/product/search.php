<?php
/**
	Handles a Proxy to /packages/v1/{id}
*/



$rce = \RCE::factory($_SESSION['rbe']);

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


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
