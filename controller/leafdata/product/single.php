<?php
/**
	Return a Single Object as JSON
*/

$rce = \RCE::factory($_SESSION['rce']);

$obj = $rce->inventory_type()->one($ARG['guid']);


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $obj,
));
