<?php
/**
	Return a List of Licenses
*/

$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->license()->all();

$ret = array();

foreach ($res['result'] as $x) {

	$rec = array(
		'guid' => $x['global_id'],
		'code' => $x['code'],
		'name' => trim($x['name']),
		'phone' => trim($x['phone']),
		'address' => array(
			'line1' => trim($x['address1']),
			'line2' => trim($x['address2']),
			'city' => trim($x['city']),
		),
		// '_rbe_data' => $x,
	);

	$ret[] = $rec;

}

$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
