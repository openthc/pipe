<?php
/**
	Return a Single License
	We have to filter ourselves because the API does not offer this option
*/

$rce = \RCE::factory($_SESSION['rce']);

$res = $rce->license()->all();

$ret = null;

foreach ($res['result'] as $x) {

	if ($ARG['guid'] == $x['global_id']) {
		// OK
	} elseif ($ARG['guid'] == $x['code']) {
		// OK
	} elseif ($ARG['guid'] == substr($x['code'], 1)) {
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
		'_source' => $x,
	);

	break; // Stop for

}


// Nothing?
if (empty($ret)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Not Found',
	), 404);
}


// Something?
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
