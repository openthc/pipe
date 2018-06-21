<?php
/**

*/


$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->inventory()->one($ARG['guid']);
if (empty($res)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Lot not found',
	), 404);
}

$res = RBE_LeafData::de_fuck($res);

$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res,
));
