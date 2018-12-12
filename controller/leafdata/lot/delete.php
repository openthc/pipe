<?php
/**
	Delete a Lot
*/

// First send a 202, Pending
// Second send a 204, Deleted/No Content

$rce = \RCE::factory($_SESSION['rce']);

$res = $rce->inventory()->delete($ARG['guid']);
//if (empty($res)) {
//	return $RES->withJSON(array(
//		'status' => 'failure',
//		'detail' => 'Lot not found',
//	), 404);
//}
//
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res,
));
