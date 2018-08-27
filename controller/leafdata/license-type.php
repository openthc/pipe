<?php
/**

*/

$ret = array(
	'status' => 'success',
	'result' => array(),
);

$ret['result'][] = array(
	'guid' => 'cultivator',
	'code' => 'G',
	'name' => 'Grower/Producer',
);

$ret['result'][] = array(
	'guid' => 'production',
	'code' => 'M',
	'name' => 'Manufacturer/Processor',
);

$ret['result'][] = array(
	'guid' => 'cultivator_production',
	'code' => 'J',
	'name' => 'Joint/Producer+Processor',
);

$ret['result'][] = array(
	'guid' => 'dispensary',
	'code' => 'R',
	'name' => 'Retailer/Dispensary',
);

return $RES->withJSON($ret);
