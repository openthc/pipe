<?php
/**
	Return a Single Object as JSON
*/

$rbe = \RCE::factory($_SESSION['rbe']);

$obj = $rbe->inventory_type()->one($ARG['guid']);


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $obj,
));
