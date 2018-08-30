<?php
/**
	Return a Single License
*/

$rce = \RCE::factory($_SESSION['rbe']);

$res = $rce->license()->all();

$ret = array();

foreach ($res['result'] as $x) {

	if ($ARG['guid'] == $x['global_id']) {
		// OK
	} elseif ($ARG['guid'] == $x['code']) {
		// OK
	} else {
		// Skip
		continue;
	}

	$key_list = array_keys($x);
	foreach ($key_list as $k) {
		$x[$k] = trim($x[$k]);
	}

	// Clean Junk Fields
	$key_list = array(
		'phone',
		'address1',
		'address2',
		'city',
	);
	foreach ($key_list as $k) {
		if ('1' == $x[$k]) {
			$x[$k] = null;
		}
	}

	$ret = array(
		'guid' => $x['global_id'],
		'code' => $x['code'],
		'name' => $x['name'],
		'phone' => $x['phone'],
		'address' => array(
			'line1' => $x['address1'],
			'line2' => $x['address2'],
			'city' => $x['city'],
		),
	);

	break; // Stop for

}


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
