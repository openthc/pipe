<?php
/**
	Return a List of Outgoing Transfer Objects
*/


$rbe = \RCE::factory($_SESSION['rbe']);

$t = $rbe->transfer();
//var_dump($t);

$out_list = array();
$res = $rbe->transfer()->all();
$src_list = $res['result']['data'];
//var_dump($src_list);
//exit(0);

foreach ($src_list as $src) {

	$out = array();
	$out['guid'] = $src['global_id'];
	$out['source_license_guid'] = $src['global_from_mme_id'];
	$out['target_license_guid'] = $src['global_to_mme_id'];
	$out['type'] = $src['manifest_type'];
	$out['status'] = $src['status'];
	$out['status_void'] = $src['void'];
	//$out['_meta'] = $src;
	//$out['carrier_license_guid'] = $src['global_transporting_mme_id'];

	$out_list[] = $out;
}


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $out_list,
));
