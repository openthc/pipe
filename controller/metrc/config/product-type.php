<?php
/**
	Return a List of Licenses
*/



$rbe = \RCE::factory($_SESSION['rbe']);

// /items/v1/categories
$res = $rbe->itemCategoryList();


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


$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
