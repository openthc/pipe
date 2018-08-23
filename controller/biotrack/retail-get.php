<?php
/**
	List of Retail Sales
*/


$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_sale(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['sale'],
));
