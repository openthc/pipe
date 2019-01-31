<?php
/**
 * Return a Single Outgoing Transfer Object
*/

RCE_Sync::open();

$rce = \RCE::factory($_SESSION['rce']);
$obj = $rce->transfer()->one($ARG['guid']);
if (empty($obj)) {
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => 'Not Found',
		'result' => null,
	), 404);
}

$obj = RBE_LeafData::de_fuck($obj);

// Cleanup the Transfer Item Objects
$key_list = array_keys($obj['inventory_transfer_items']);
foreach ($key_list as $key) {

	$iti = $obj['inventory_transfer_items'][$key];
	$iti = RBE_LeafData::de_fuck($iti);

	if (!empty($iti['is_sample'])) {

		$stpst = sprintf('%s/%s', $iti['sample_type'], $iti['product_sample_type']);
		$stpst = trim($stpst, '/');

		switch ($stpst) {
		case 'lab_sample':
			$iti['_sample'] = 'QA/Sample';
			break;
		case 'non_mandatory_sample':
		case 'non_mandatory_sample':
		case 'non_mandatory_sample':
			$iti['_sample'] = 'QA/Optional';
			break;
		case 'product_sample':
			// @note sometimes the 'product_sample_type' part is missing from LeafData, so we map to vendor_sample
		case 'product_sample/vendor_sample':
			$iti['_sample'] = 'Client/Prospect Sample';
		 	break;
		case 'product_sample/budtender_sample':
			$iti['_sample'] = 'Client/Budtender Sample';
			break;
		case 'non_mandatory_sample/vendor_sample': // Migrate from BioTrack has this fucked up one
			$iti['_sample'] = 'Client/Prospect Sample';
			break;
		default:
			throw new Exception(sprintf('Unexpected Sample Type: "%s" [TOS#056]', $stpst));
		}

	}

	$obj['inventory_transfer_items'][$key] = $iti;
}


// Success
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $obj,
), 200);
