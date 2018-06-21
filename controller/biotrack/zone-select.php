<?php
/**
	List of All Rooms
*/

$RES = new Response_JSON();

$rbe = RCE::factory($_SESSION['rbe']);

$ret = array();

$res = $rbe->sync_inventory_room(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
if (!empty($res['inventory_room'])) {
	foreach ($res['inventory_room'] as $r) {
		$r['_kind'] = 'Inventory';
		$ret[] = $r;
	}
}

$res = $rbe->sync_plant_room(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
if (!empty($res['plant_room'])) {
	foreach ($res['plant_room'] as $r) {
		$r['_kind'] = 'Plant';
		$ret[] = $r;
	}
}

// Unify Ouput According to OpenTHC Specification

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));
