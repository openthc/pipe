<?php
/**
*/

$RES = new Response_JSON();

$kind_ini = parse_ini_file('/opt/api.openthc.org/etc/kind.ini', true);
$kind_ret = array();

foreach ($kind_ini as $kind_name => $kind_data) {
	if ($kind_data['biotrack']) {
		$kind_ret[$kind_name] = $kind_data;
	}
}

$RES = $RES->withJSON(array(
	'status' => 'success',
	'result' => $kind_ret,
));
