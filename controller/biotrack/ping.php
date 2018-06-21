<?php
/**
	Test
*/

use Edoceo\Radix;

$rbe = \RCE::factory($_SESSION['rbe']);

$ret_ping = array();

$arg = array(
	'data' => array(),
);

$obj_list = $rbe->listSyncObjects();
$obj_list = array_keys($obj_list);
foreach ($obj_list as $obj) {
	$arg['data'][] = array(
		'table' => $obj,
		'transaction_start' => 0,
	);
}

$res = $rbe->sync_check($arg);
switch (intval($res['success'])) {
case 0:
	$RES = $RES->withJson(array(
		'status' => 'failure',
		'detail' => 'CPB#039: RBE Error',
		'result' => $res,
	), 400);
	return(0);
}
$ret_ping['sync_check'] = $res;

$have = $want = 0;

foreach ($obj_list as $obj) {

	$want++;

	$sfn = sprintf('sync_%s', $obj);
	$res = $rbe->$sfn(array(
		'min' => 999999999,
		'max' => 999999999 + 1,
	));

	$ret_ping[$sfn] = $res;
}

// Can See QA?
// $ret_ping['inventory_qa_check_all'] = $rbe->inventory_qa_check_all(9999999999999999);
// $ret_ping['inventory_qa_check'] = $rbe->inventory_qa_check(9999999999999999);

// And the Other Four Magic Things
// Need to Known Location First!
// $ret_ping['inventory_manifest_lookup'] = $rbe->inventory_manifest_lookup('123456');
// $ret_ping['inventory_transfer_outbound_return_lookup'] = $rbe->inventory_transfer_outbound_return_lookup('123456');

return $RES->withJson(array(
	'status' => 'success',
	'result' => $ret_ping,
), 200, JSON_PRETTY_PRINT);

//case 'wa/test':
//
//	if (!preg_match('/^\d{9}$/', $ext)) {
//		$res = $res->withJson(array(
//			'status' => 'failure',
//			'detail' => 'OCA#053: Provide UBI in the rbe-data field',
//		), 400);
//		return(0);
//	}
//
//	require_once(APP_ROOT . '/lib/RBE/BioTrack.php');
//	require_once(APP_ROOT . '/lib/RBE/BioTrack/WA.php');
//
//	$rbe = new RBE_Biotrack_WA();
//	$rbe->setTraining(true);
//	$chk = $rbe->login($ext, $uid, $pwd);
//
//	switch (intval($chk['success'])) {
//	case 0:
//
//		break;
//	case 1:
//		break;
//	}
//
//}
//