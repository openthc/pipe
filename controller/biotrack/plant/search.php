<?php
/**
	List of All Plants
*/

$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_plant(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

// Unify Ouput According to OpenTHC Specification

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['plant'],
), 200, JSON_PRETTY_PRINT);
