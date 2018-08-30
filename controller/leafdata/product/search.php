<?php
/**
	Return a List of Products
	In LeafData it's called an Inventory Type
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 203;

$obj_name = 'product';

$age = RCE_Sync::age($obj_name);

if ($age >= RCE_Sync::MAX_AGE) {

	$sql = "SELECT guid, hash FROM {$obj_name}";
	$res_cached = SQL::fetch_mix($sql);

	$rce = \RCE::factory($_SESSION['rbe']);

	$res_source = $rce->inventory_type()->all();
	if ('success' != $res_source['status']) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => $rce->formatError($res_source),
		), 500);
	}

	$res_source = $res_source['result']['data'];

} else {

	// From Cache
	$res_cached = array();
	$res_source = array();

	$sql = "SELECT hash, meta FROM {$obj_name}";
	$res = SQL::fetch_all($sql);

	foreach ($res as $rec) {

		$x = json_decode($rec['meta'], true);
		$x['hash'] = $rec['hash'];

		$res_cached[ $x['global_id'] ] = $x['hash'];
		$res_source[] = $x;
	}

}

$ret = array();

foreach ($res_source as $src) {

	if (empty($src['hash'])) {
		$src['hash'] = _hash_obj($src);
	}

	$rec = array(
		'name' => trim($src['name']),
		'guid' => $src['global_id'],
		'hash' => $src['hash'],
		'type' => sprintf('%s/%s', $src['type'], $src['intermediate_type']),
		//'code' => trim($src['external_id']),
	);

	if ($rec['hash'] != $res_cached[ $rec['guid'] ]) {

		$rec['_source'] = $src;
		$rec['_updated'] = 1;

		unset($src['hash']);

		RCE_Sync::save($obj_name, $guid, $hash, $src);

	}

	$ret[] = $rec;

}

RCE_Sync::age($obj_name, time());


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
), $ret_code, JSON_PRETTY_PRINT);


/**
	Determine if the value-pair is correct from a hard-coded map
	@return modified input data-array, with _warn attribute (maybe)
*/
function _product_type_verify($rec)
{
	//if (empty($rec['type'])) {
	//	$rec['type'] = 'end_product';
	//}
	//
	//if (empty($rec['intermediate_type'])) {
	//	$rec['intermediate_type'] = 'usable_marijuana';
	//}
	//
	//// Fixup Bad Data from Leaf
	//switch ($rec['type']) {
	//case 'immature_plant':
	//	if (empty($rec['intermediate_type'])) {
	//		$rec['intermediate_type'] = 'clones';
	//	}
	//	break;
	//case 'waste':
	//	if (empty($rec['intermediate_type'])) {
	//		$rec['intermediate_type'] = 'waste';
	//	}
	//	break;
	//}

	//$PT = SQL::fetch_row('SELECT * FROM product_type WHERE stub = ?', array($stub));

	$product_type_list = array(
		'immature_plant/seeds',
		'immature_plant/clones',
		'immature_plant/plant_tissue',
		'mature_plant/mature_plant',
		'mature_plant/non_mandatory_plant_sample',
		'harvest_materials/flower',
		'harvest_materials/flower_lots',
		'harvest_materials/other_material',
		'harvest_materials/other_material_lots',
		'intermediate_product/marijuana_mix',
		'intermediate_product/non-solvent_based_concentrate',
		'intermediate_product/hydrocarbon_concentrate',
		'intermediate_product/co2_concentrate',
		'intermediate_product/ethanol_concentrate',
		'intermediate_product/food_grade_solvent_concentrate',
		'intermediate_product/infused_cooking_medium',
		'end_product/liquid_edible',
		'end_product/solid_edible',
		'end_product/concentrate_for_inhalation',
		'end_product/topical',
		'end_product/infused_mix',
		'end_product/packaged_marijuana_mix',
		'end_product/sample_jar',
		'end_product/usable_marijuana',
		'end_product/capsules',
		'end_product/tinctures',
		'end_product/transdermal_patches',
		'end_product/suppository',
		'waste/waste',
	);

	if (!in_array($rec['type'], $product_type_list)) {
		$rec['_warn'][] = sprintf('Invalid Type: "%s"', $rec['type']);
	}

	return $rec;

}
