<?php
/**
 * Fetch a Single QA Result
 */

session_write_close();

$cre = \CRE::factory($_SESSION['cre']);

$obj = $cre->qa()->one($ARG['guid']);
if (empty($obj)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'QA Result not found',
	), 404);
}

$obj = RBE_LeafData::de_fuck($obj);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $obj,
));
