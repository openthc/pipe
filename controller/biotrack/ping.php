<?php
/**
	Test
*/

use Edoceo\Radix;

$rce = \RCE::factory($_SESSION['rce']);

$ret_ping = array();

$arg = array(
	'data' => array(),
);

$obj_list = $rce->listSyncObjects();
$obj_list = array_keys($obj_list);
foreach ($obj_list as $obj) {
	$arg['data'][] = array(
		'table' => $obj,
		'transaction_start' => 0,
	);
}

$res = $rce->sync_check($arg);
switch (intval($res['success'])) {
case 0:
	$RES = $RES->withJson(array(
		'status' => 'failure',
		'detail' => 'CPB#039: RCE Error',
		'result' => $res,
	), 400);
	return(0);
}
$ret_ping['sync_check'] = $res;

$have = $want = 0;

foreach ($obj_list as $obj) {

	$want++;

	$sfn = sprintf('sync_%s', $obj);
	$res = $rce->$sfn(array(
		'min' => 999999999,
		'max' => 999999999 + 1,
	));

	$ret_ping[$sfn] = $res;
}

// Can See QA?
// $ret_ping['inventory_qa_check_all'] = $rce->inventory_qa_check_all(9999999999999999);
// $ret_ping['inventory_qa_check'] = $rce->inventory_qa_check(9999999999999999);

// And the Other Four Magic Things
// Need to Known Location First!
// $ret_ping['inventory_manifest_lookup'] = $rce->inventory_manifest_lookup('123456');
// $ret_ping['inventory_transfer_outbound_return_lookup'] = $rce->inventory_transfer_outbound_return_lookup('123456');

return $RES->withJson(array(
	'status' => 'success',
	'result' => array(
		'host' => 'pipe.openthc.com',
		'ipv4' => '',
		'ipv6' => '',
		'ssl-cert' => '',
		'_session_id' => session_id(),
		'_session' => $_SESSION,
		'_source' => $ret_ping,
	),
), 200, JSON_PRETTY_PRINT);
