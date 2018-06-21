<?php
/**
	List of All Inventory
*/

$rbe = RCE::factory($_SESSION['rbe']);

// Inventory
$res = $rbe->sync_inventory(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

$res['success'] = 0;
switch ($res['success']) {
case 0:
	break;
case 1:
	if (!empty($res['inventory'])) {
		foreach ($res['inventory'] as $rec) {
			$ret[] = array(
				'guid' => $rec['id'],
				'strain' => array('name' => $rec['strain']),
				'product' => array(
					'name' => $rec['productname'],
					'type' => $rec['inventorytype'],
					'unit' => array(
						'type' => 'bulk',
						'count' => $rec['remaining_quantity'],
						'weight' => $rec['usable_weight'],
					)
				),
				'_source' => $rec,
			);
		}
	}
	break;
}

// Inventory Samples
$res = $rbe->sync_inventory_sample(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));
switch ($res['success']) {
case 0:
	break;
case 1:
	if (!empty($res['inventory_sample'])) {
		foreach ($res['inventory_sample'] as $rec) {
			$ret[] = $rec;
		}
	}
	break;
}

//$res = $rbe->sync_inventory_adjust(0);
//switch ($res['success']) {
//case 0:
//	break;
//case 1:
//	if (!empty($res['inventory_adjust'])) {
//		foreach ($res['inventory_adjust'] as $rec) {
//			// $ret[] = $rec;
//		}
//	}
//	break;
//}

// @todo Unify Ouput According to OpenTHC Specification

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), 200, JSON_PRETTY_PRINT);
