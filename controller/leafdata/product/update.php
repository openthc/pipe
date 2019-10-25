<?php
/**
 * Update a Single Product Object as JSON
 */

$cre = \CRE::factory($_SESSION['cre']);

// Read JSON

// Inflate

// Error Check

// Execute
$res = $cre->inventory_type()->update($mod);

var_dump($res);
$res = RBE_LeafData::de_fuck($res);

// Now Fetch Again
// $cre->inventory_type()->update($mod);

// return $RES->withJSON(array(
// 	'status' => 'success',
// 	'result' => $res,
// ));

return $RES->withJSON([
	'meta' => [],
	'data' => $obj,
]);
