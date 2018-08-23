<?php
/**
	List of Transfers
*/


$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_manifest(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

// $rbe->inventory_transfer(0);
// $rbe->inventory_transfer_inbound(0);

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['manifest'],
));
