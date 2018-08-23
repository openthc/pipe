<?php
/**
	Handles a Proxy to /packages/v1/{id}
*/



$rbe = \RCE::factory($_SESSION['rbe']);

// /items/v1/categories
$res = $rbe->itemList('active');
//$res = $rbe->packageList('active');


foreach ($res['result'] as $x) {

	$rec = array(
		'name' => trim($x['Name']),
		'base' => trim($x['ProductCategoryType']),
		'mode' => trim($x['QuantityType']),
		'_source' => $x,
	);

	$ret[] = $rec;

}


$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
