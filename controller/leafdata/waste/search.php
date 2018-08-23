<?php
/**
	Return a List of Waste objects
	LeafData calls this a Disposal
*/


$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->disposal()->all();

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['result']['data'],
));
