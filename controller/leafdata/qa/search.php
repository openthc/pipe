<?php
/**
	Return List of QA Result Data
*/


$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->qa()->all();

$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['result']['data'],
));
