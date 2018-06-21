<?php
/**
	Ping the Leaf Data Connection
*/

// curl -X GET https://watest.leafdatazone.com/api/v1/strains

// curl -X GET https://watest.leafdatazone.com/api/v1/strains -H  "x-mjf-key: "FDSFSD" -H  "x-mjf-mme-code: FDSFDSFDS" -H "Content-Type: application/json" -d ''

switch ($_SESSION['rbe']) {
case 'nv':
case 'nv/leafdata':
case 'wa':
case 'wa/leafdata':
	// OK
	break;
default:
	$RES = $RES->withJson(array(
		'status' => 'failure',
		'detail' => 'CLP#020: Invalid RBE',
	), 400);

}

$rbe = \RCE::factory($_SESSION['rbe']);

$good = 0;
$want = 0;

$want++;
$res = $rbe->call('GET', '/inventory_types');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rbe->call('GET', '/areas');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rbe->call('GET', '/mmes');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rbe->call('GET', '/users');
if (empty($res['error'])) {
	$good++;
}

$want++;
$res = $rbe->call('GET', '/strains');
if (empty($res['error'])) {
	$good++;
}

$RES = new Response_JSON();
return $RES->withJson(array(
	'status' => 'success',
	'result' => intval($good / $want * 100),
));
