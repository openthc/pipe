<?php
/**
 * BioTrackTHC License Types
 */

$lic_type_list = array();
foreach (RBE_BioTrack::$loc_type as $k => $v) {
	$lic_type_list[] = array(
		'code' => $k,
		'name' => $v,
	);
}


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $lic_type_list,
), 200, JSON_PRETTY_PRINT);
