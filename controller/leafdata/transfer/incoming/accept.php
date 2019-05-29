<?php
/**
	Accept the Incoming Transfer
*/

//var_dump($ARGS);

$src_json = file_get_contents('php://input');
$req_data = json_decode($src_json, true);


// Arguments?
//$arg = array(
//	'global_id' => $this->Manifest['guid'],
//	'inventory_transfer_items' => array(),
//);


$cre = \CRE::factory($_SESSION['cre']);
$res = $cre->transfer()->receive($req_data);


// Call it good
return $RES->withJSON($res);
