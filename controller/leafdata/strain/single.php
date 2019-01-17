<?php
/**
	Fetch a Single Strain
*/


$rce = \RCE::factory($_SESSION['rce']);

$res = $rce->strain()->one($ARG['guid']);
if (empty($res)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Strain not found',
	), 404);
}

$res = RBE_LeafData::de_fuck($res);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res,
));
