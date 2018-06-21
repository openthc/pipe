<?php
/**
	List of Transfers
*/

$RES = new Response_JSON();

$rbe = RCE::factory($_SESSION['rbe']);

$res = $rbe->sync_manifest(array(
	'min' => intval($_GET['min']),
	'max' => intval($_GET['max']),
));

// $rbe->inventory_transfer(0);
// $rbe->inventory_transfer_inbound(0);

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['manifest'],
));
