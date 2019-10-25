<?php
/**
	List of Retail Sales
*/


$cre = RCE::factory($_SESSION['cre']);

$res = $cre->sync_sale(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['sale'],
));
