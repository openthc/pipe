<?php
/**
 * Product Types for BioTrack
 */

$res = RBE_BioTrack::kindList();

$product_type_list = array();
foreach ($res as $k => $v) {
	$product_type_list[] = array(
		'code' => $k,
		'name' => $v,
	);
}

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $product_type_list,
), 200, JSON_PRETTY_PRINT);
