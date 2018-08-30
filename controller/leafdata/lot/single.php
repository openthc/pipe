<?php
/**

*/


$rce = \RCE::factory($_SESSION['rbe']);

$res = $rce->inventory()->one($ARG['guid']);
if (empty($res)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Lot not found',
	), 404);
}

$res = RBE_LeafData::de_fuck($res);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res,
));
