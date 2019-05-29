<?php
/**
	Fetch a Single Batch
*/


$cre = \CRE::factory($_SESSION['cre']);

// This wraps the fetch for both open/closed status
$res = $cre->batch()->one($ARG['guid']);
if (empty($res)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Batch not found',
	), 404);
}

$res = RBE_LeafData::de_fuck($res);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res,
));
