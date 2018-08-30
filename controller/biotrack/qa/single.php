<?php
/**
	Gets a QA Result
*/

$ret = array(
	//'company' => 'Lab Company',
	//'contact' => 'Lab Contact',
	//'certificate' => '',
	//'image' => '',
	//'status' => '-unknown-',
	//'note' => array(), // Is Medical, For Usable
	'metric' => array(
		'general' => array(),
		'microbe' => array(),
		'potency' => array(),
		'solvent' => array(),
		'terpene' => array(),
	),
);

$rce = \RCE::factory($_SESSION['rbe']);

//
//$res = $rce->sync_qa_lab(0);
//switch ($res['success']) {
//case 0:
//	// Tag an Error
//	$ret['detail'][] = $res['error'];
//	break;
//case 1:
//	foreach ($res['qa_lab'] as $x) {
//		$x['_kind'] = 'QA';
//		$ret['result'][] = $x;
//	}
//}

// First Results
$res0 = $rce->inventory_qa_check($_GET['code']);
switch ($res0['success']) {
case 0:
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'QGO#019: Invalid Result from RBE',
		'result' => $res0,
	));
	break;
case 1:

	// OK
	switch ($res0['use']) {
	case 0:
		$ret['note'][] = 'Intended Use: Zero';
		break;
	case 1:
		$ret['note'][] = 'Intended Use: OK for Usable';
		break;
	}

	// Medical
	switch ($res0['is_medical']) {
	case 0:
		$ret['note'][] = 'NOT for Medical';
		break;
	case 1:
		$ret['note'][] = 'OK for Medical';
		break;
	}

	// Status
	switch ($res0['result']) {
	case 0:
		$ret['status'] = 'Pending';
		break;
	case 1:
		$ret['status'] = 'Passed';
		break;
	}

	$metric = _fold_qa_results($res0);

	if (!empty($metric[1])) {
		$ret['metric']['general']['moisture'] = floatval($metric[1]['moisture']);
	}
	if (!empty($metric[3])) {
		$ret['metric']['general']['visual-other'] = floatval($metric[3]['other']);
		$ret['metric']['general']['visual-stems'] = floatval($metric[3]['stems']);
	}

	if (!empty($metric[2])) {
		$ret['metric']['potency']['thc'] = floatval($metric[2]['thc']);
		$ret['metric']['potency']['thc-a'] = floatval($metric[2]['thca']);
		$ret['metric']['potency']['cbd'] = floatval($metric[2]['cbd']);
		$ret['metric']['potency']['cbd-a'] = floatval($metric[2]['cbda']);

		$ret['metric']['potency']['thc-total'] = $ret['metric']['potency']['thc'] + ($ret['metric']['potency']['thc-a'] * 0.877);
		$ret['metric']['potency']['cbd-total'] = $ret['metric']['potency']['cbd'] + ($ret['metric']['potency']['cbd-a'] * 0.877);
		// $ret['metric']['potency']['_raw'] = $metric[2];

	}

	if (!empty($metric[4])) {
		$ret['metric']['microbe']['bacteria'] = floatval($metric[4]['aerobic_bacteria']);
		// @todo bile_tolerant ?
		$ret['metric']['microbe']['coliforms'] = floatval($metric[4]['coliforms']);
		$ret['metric']['microbe']['e-coli'] = floatval($metric[4]['e_coli_and_salmonella']);
		$ret['metric']['microbe']['salmonella'] = floatval($metric[4]['e_coli_and_salmonella']);
		$ret['metric']['microbe']['mold']  = floatval($metric[4]['yeast_and_mold']);
		$ret['metric']['microbe']['yeast'] = floatval($metric[4]['yeast_and_mold']);
	}

	// Solvents
	if (!empty($metric[5])) {
		// residual_solvent
	}

	// Mycotoxin
	if (!empty($metric[6])) {
		// total_mycotoxins
	}

	// Pesticide
	if (!empty($metric[7])) {
		// pesticide_residue
	}

	// Heavy Metals
	if (!empty($metric[7])) {
		// heavy_metal
	}

	// $ret['metric']['_raw'] = $metric;

}

//$ret['useage0'] = $res0['use'];
//$ret['result0'] = $res0['result'];
//$ret['sessiontime0_iso'] = strftime('%Y-%m-%d %H:%M:%S', $res0['sessiontime']);

//if (!empty($res0['test'])) {
//	$ret['test0'] = _fold_qa_results($res0);
//}

// Now the Check_All
$res1 = $rce->inventory_qa_check_all($_GET['code']);
switch ($res0['success']) {
case 0:
	break;
case 1:
	// OK
	break;
}
if (empty($res1['data']) || !is_array($res1['data']) || (1 != count($res1['data']))) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'QGO#025: Invalid Result from RBE',
	));
}

$x = $res1['data'][0];

//$ret['sample_parent_id'] = $x['parent_id'];
//$ret['inventory_sample_id'] = $x['sample_id']; // Legacy
//$ret['sample_id'] = $x['sample_id']; // Canon

//if (empty($x['transactionid_original']) && empty($x['transactionid'])) {
//	  // Ignore, No Data
//	  // return null;
//} else {

//$ret['sessiontime1_iso'] = strftime('%Y-%m-%d %H:%M:%S', $res1['sessiontime']);

//if (!empty($x['lab_license'])) {
//	$ret['lab'] = $x['lab_license'];
//}

//$ret['result1'] = $x['result'];
//$ret['passed1'] = (1 == $x['result']);

//if (!empty($x['test'])) {
//	$ret['test1'] = _fold_qa_results($x);
//}

// Unify Ouput According to OpenTHC Specification

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
	//'_res0' => $res0,
	//'_res1' => $res1,
));

/**

*/
function _fold_qa_results($res)
{
	$r = array();

	foreach ($res['test'] as $i => $t) {

		$t = array_change_key_case($t);
		$i = $t['type'];

		// BioTrack Test Case #2 is Potency, Calc CBD and TCH Totals
		if (2 == $i) {

			if (empty($t['thc-total'])) {
				$t['thc-total'] = ($t['thca'] * 0.877) + $t['thc'];
			}

			if (empty($t['cbd-total'])) {
				$t['cbd-total'] = ($t['cbda'] * 0.877) + $t['cbd'];
			}
		}

		ksort($t);

		$r[ $i ] = $t;
	}

	return $r;

}
