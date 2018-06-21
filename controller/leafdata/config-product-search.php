<?php
/**
	List all the Product
	In LeafData it's called an Inventory Type
*/

$ret = array();

$rbe = \RCE::factory($_SESSION['rbe']);

//$rls = new RBE_LeafData_Sync($rbe);
//$rlsp = new RBE_LeafData_Sync_Product($rls, $rbe);
//$res = $rlsp->all();

$page_cur = 1;
$page_max = 1;

while ($page_cur <= $page_max) {

	$res = $rbe->inventory_type()->all(array('page' => $page_cur));

	// Triple Retry?
	if ('success' != $res['status']) {
		syslog(LOG_ERR, $this->_rbe->formatError($res));
	}

	foreach ($res['result']['data'] as $x) {

		$x = RBE_LeafData::de_fuck($x);

		$rec = array();
		$rec['guid'] = $x['global_id'];
		$rec['name'] = $x['name'];
		$rec['type'] = sprintf('%s/%s', $x['type'], $x['intermediate_type']);
		$rec['strain'] = array('guid' => $x['global_strain_id']);
		$rec['package'] = array(
			'size' => floatval($x['net_weight']),
			'unit' => $x['uom'],
		);
		$rec['created_at'] = $x['created_at'];
		//$rec['_src'] = $x;
		$rec = _product_type_verify($rec);
		$ret[] = $rec;
	}

	$page_cur++;
	$page_max = $res['result']['last_page'];
}

//$REQ = $REQ->withAttribute('status', 'success');
//$REQ = $REQ->withAttribute('result', $ret);

$RES = new Response_JSON();
return $RES->withJSON(array(
	'status' => 'success',
	'result' => $ret,
));

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
