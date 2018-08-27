<?php
/**
	Return a List of All Product Types
*/

//$f = sprintf('%s/etc/product-type.ini', APP_ROOT);
$f = sprintf('%s/etc/product-type.ini', '/opt/api.openthc.org');

$source_product_type_list = parse_ini_file($f, true);
//print_r($source_product_type_list);

$output_product_type_list = array();
foreach ($source_product_type_list as $pt_name => $pt_data) {
	if ($pt_data['leafdata']) {

		$output_product_type_list[] = array(
			'name' => $pt_name,
			'mode' => $pt_data['mode'],
			'sort' => $pt_data['sort'],
			'code' => $pt_data['leafdata_code'],
		);

	}
}

return $RES->withJSON(array(
	'status' => 'success',
	'result' => $output_product_type_list,
), 200, JSON_PRETTY_PRINT);
