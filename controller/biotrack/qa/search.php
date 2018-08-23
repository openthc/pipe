<?php
/**
	List of QA Samples
*/


$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_inventory_qa_sample(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

// Unify Ouput According to OpenTHC Specification

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['inventory_qa_sample'],
));
