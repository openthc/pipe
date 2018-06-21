<?php
/**
	Return All Companies
*/

use Edoceo\Radix;

$RES = new Response_JSON();

$ret = array(
	'status' => 'failure',
	'detail' => array(),
	'result' => array(),
);

$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_vendor(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
switch ($res['success']) {
case 0:
	// Tag an Error
	$ret['detail'][] = $res['error'];
	break;
case 1:
	if (!empty($res['vendor'])) {
		foreach ($res['vendor'] as $x) {
			$x['_kind'] = RBE_Biotrack::$loc_type[$x['locationtype']]; // RBE_Biotrack'company';
			$ret['result'][] = $x;
		}
	}
}
//print_r($res);

$res = $rbe->sync_qa_lab(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
switch ($res['success']) {
case 0:
	// Tag an Error
	$ret['detail'][] = $res['error'];
	break;
case 1:
	if (!empty($res['qa_lab'])) {
		foreach ($res['qa_lab'] as $x) {
			$x['_kind'] = 'QA';
			$ret['result'][] = $x;
		}
	}
}
//print_r($res);


$res = $rbe->sync_third_party_transporter(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
switch ($res['success']) {
case 0:
	// Tag an Error
	$ret['detail'][] = $res['error'];
	break;
case 1:
	if (!empty($res['third_party_transporter'])) {
		foreach ($res['third_party_transporter'] as $x) {
			$x['_kind'] = 'Transporter';
			$ret['result'][] = $x;
		}
	}
}
//print_r($res);

// Unify Ouput According to OpenTHC Specification
if (!empty($_GET['type'])) {
	$ret['result'] = array_filter($ret['result'], function($v) {
		if ($v['_kind'] == $_GET['type']) {
			return true;
		}
		return false;
	});
}

$ret['status'] = 'success';

$RES = $RES->withJSON($ret);
