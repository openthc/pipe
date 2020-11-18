<?php
/**
 * Stem pass-thru handler for METRC systems
 */

use Edoceo\Radix\DB\SQL;

$cre_base = null;
$cre_host = null;

$src_path = explode('/', $ARG['path']);

$system = array_shift($src_path);

// Requested System
switch ($system) {
case 'api-ak.metrc.com':
case 'api-ca.metrc.com':
case 'api-co.metrc.com':
case 'api-la.metrc.com':
case 'api-ma.metrc.com':
case 'api-md.metrc.com':
case 'api-mi.metrc.com':
case 'api-mo.metrc.com':
case 'api-mt.metrc.com':
case 'api-nv.metrc.com':
case 'api-oh.metrc.com':
case 'api-or.metrc.com':
	$cre_base = sprintf('https://%s', $system);
	break;
case 'sandbox-api-or.metrc.com':
case 'sandbox-api-co.metrc.com':
case 'sandbox-api-md.metrc.com':
case 'sandbox-api-me.metrc.com':
	// SubSwitch
	// so external users can use a canonical name and we'll adjust back here
	// based on the switching the METRC might do with their sandox endpoints
	switch ($system) {
		case 'sandbox-api-me.metrc.com':
			// re-maps to Maryland
			$system = 'sandbox-api-md.metrc.com';
		break;
	}
	$cre_base = sprintf('https://%s', $system);
	break;
default:
	return $RES->withJSON([
		'data' => null,
		'meta' => [ 'detail' => 'CRE Not Found' ],
	], 404);
}

// From URL if not already set
if (empty($cre_host)) {
	$cre_host = parse_url($cre_base, PHP_URL_HOST);
}

// Resolve Path
$src_path = implode('/', $src_path);
$req_path = $cre_base . '/' . $src_path . '?' . $_SERVER['QUERY_STRING'];
$req_path = trim($req_path, '?');


// A cheap-ass, incomplete filter
switch ($src_path) {
case 'facilities/v1':
case 'harvests/v1/waste/types':
case 'items/v1/categories':
case 'labtests/v1/states':
case 'labtests/v1/types':
case 'packages/v1/types':
case 'plantbatches/v1/types':
case 'plants/v1/additives/types':
case 'plants/v1/waste/methods':
case 'sales/v1/customertypes':
case 'unitsofmeasure/v1/active':
	// These don't require a licenseNumber
	break;
default:
	// Everything else does
	if (empty($_GET['licenseNumber'])) {
		// Fatal?
		return $RES->withJSON(array(
			'status' => 'failure',
			'origin' => 'openthc',
			'detail' => 'The License Number parameter must be supplied',
		), 400, JSON_PRETTY_PRINT);
	}

	break;
}

// Database
$sql_hash = crc32($_SERVER['HTTP_AUTHORIZATION']);
$sql_file = _database_create_open('metrc', $sql_hash);


$cre_http = new CRE_HTTP(array(
	'base_uri' => $cre_base
));


// Forward
switch ($_SERVER['REQUEST_METHOD']) {
case 'DELETE':
case 'GET':

	$req = new GuzzleHttp\Psr7\Request($_SERVER['REQUEST_METHOD'], $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('host', $cre_host);

	$res = $cre_http->send($req);

	break;

case 'POST':
case 'PUT':

	$src_json = file_get_contents('php://input');

	$req = new GuzzleHttp\Psr7\Request($_SERVER['REQUEST_METHOD'], $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('content-type', 'application/json');
	$req = $req->withHeader('host', $cre_host);

	$res = $cre_http->send($req, array('body' => $src_json));

	break;

}

// var_dump($res);
$code = ($res ? $res->getStatusCode() : 500);
$body = ($res ? $res->getBody() : null);

$RES = $RES->withStatus($code);
$RES = $RES->withHeader('content-type', $res->getHeader('content-type'));
$RES = $RES->write($body);

return $RES;
