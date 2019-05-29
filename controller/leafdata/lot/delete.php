<?php
/**
	Delete a Lot
*/

// First send a 202, Pending
// Second send a 204, Deleted/No Content

$cre = \CRE::factory($_SESSION['cre']);

$res = $cre->inventory()->delete($ARG['guid']);
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
