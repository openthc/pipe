<?php
/**
	Ping the Leaf Data Connection
*/

// curl -X GET https://watest.leafdatazone.com/api/v1/strains

// curl -X GET https://watest.leafdatazone.com/api/v1/strains -H  "x-mjf-key: "FDSFSD" -H  "x-mjf-mme-code: FDSFDSFDS" -H "Content-Type: application/json" -d ''

switch ($_SESSION['rce']) {
case 'nv':
case 'nv/leafdata':
case 'wa':
case 'wa/leafdata':
	// OK
	break;
default:
	$RES = $RES->withJson(array(
		'status' => 'failure',
		'detail' => 'CLP#020: Invalid RCE',
	), 400);

}

$rce = \RCE::factory($_SESSION['rce']);

$good = 0;
$want = 0;

$want++;
$res = $rce->call('GET', '/inventory_types');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rce->call('GET', '/areas');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rce->call('GET', '/mmes');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rce->call('GET', '/users');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rce->call('GET', '/strains');
if (empty($res['error'])) {
	$good++;
}


return $RES->withJson(array(
	'status' => 'success',
	'result' => intval($good / $want * 100),
));
