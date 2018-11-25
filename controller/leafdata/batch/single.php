<?php
/**
	Fetch a Single Batch
*/


$rce = \RCE::factory($_SESSION['rce']);

// This wraps the fetch for both open/closed status
$res = $rce->batch()->one($ARG['guid']);
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
