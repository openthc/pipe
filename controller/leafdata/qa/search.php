<?php
/**
	Return a List of QA Result Data
*/

use Edoceo\Radix\DB\SQL;

$ret_code = 200;

$obj_name = 'qa';


$rbe = \RCE::factory($_SESSION['rbe']);

$res = $rbe->qa()->all();


return $RES->withJSON(array(
	'status' => 'success',
	'result' => $res['result']['data'],
));
