<?php
/**

*/


$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->inventory()->all();

$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['result']['data'],
));
