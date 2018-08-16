<?php
/**
	List of QA Samples
*/

$RES = new Response_JSON();

$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_inventory_qa_sample(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

// Unify Ouput According to OpenTHC Specification

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['inventory_qa_sample'],
));
