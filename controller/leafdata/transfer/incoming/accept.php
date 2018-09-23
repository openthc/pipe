<?php
/**

*/

//var_dump($ARG);
//var_dump($_POST);

$arg = $_POST;

$rce = \RCE::factory($_SESSION['rce']);

$res = $rce->transfer()->receive($arg);
if ('success' != $res['status']) {

	$ret = array(
		'status' => 'failure',
		'result' => $res['result'],
	);

	$err = $rce->formatError($res);
	$ret['detail'] = $err;

	// Add Hints Here
	if (preg_match('/transit but is open/', $err)) {
		$ret['_hint'] = 'Origin must Release this Transfer';
	} elseif (preg_match('/transit but is received/', $err)) {
		$ret['_hint'] = 'Transfer has already beeen Accepted and Received';
	}

	return $RES->withJSON($ret, 400);

}
//var_dump($res);
//$t = $rce->transfer()->one($ARG['guid']);
//var_dump($t);


return $RES->withJSON($res);
