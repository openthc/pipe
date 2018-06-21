<?php
/**
	List of Retail Sales
*/

$RES = new Response_JSON();

$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_sale(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['sale'],
));
