<?php
/**
 * Stem pass-thru handler for METRC systems
 */

use Edoceo\Radix\DB\SQL;

$cre_base = null;
$cre_host = null;

// Requested System
switch ($ARG['system']) {
case 'api-ak.metrc.com':
case 'api-ca.metrc.com':
case 'api-co.metrc.com':
case 'api-mt.metrc.com':
case 'api-nv.metrc.com':
case 'api-or.metrc.com':
case 'sandbox-api-or.metrc.com':
case 'sandbox-api-co.metrc.com':
	$cre_base = sprintf('https://%s', $ARG['system']);
	break;
default:
	_exit_json(array(
		'status' => 'failure',
		'detail' => 'CRE Not Found',
	), 404);
}

// From URL if not already set
if (empty($cre_host)) {
	$cre_host = parse_url($cre_base, PHP_URL_HOST);
}


// Database
$sql_hash = crc32($_SERVER['HTTP_AUTHORIZATION']);
$sql_file = sprintf('%s/var/stem%s/leafdata-%08x.sqlite', APP_ROOT, date('Ymd'), $sql_hash);
$sql_path = dirname($sql_file);
if (!is_dir($sql_path)) {
	mkdir($sql_path, 0755, true);
}
$sql_good = is_file($sql_file);


SQL::init('sqlite:' . $sql_file);
if (!$sql_good) {
	SQL::query("CREATE TABLE log_audit (cts not null default (strftime('%s','now')), code, path, req, res, err)");
}


// Resolve Path
$src_trim = sprintf('/stem/metrc/%s', $ARG['system']); // Stuff to remove

$src_path = $_SERVER['REQUEST_URI']; // Contains Query String
$src_path = str_replace($src_trim, null, $src_path);
$src_path = ltrim($src_path, '/');

$req_path = $cre_base . '/' . $src_path;

// A cheap-ass, incomplete filter
switch ($req_path) {
case '/facilities/v1':
case '/harvests/v1/waste/types':
case '/items/v1/categories':
case '/labtests/v1/states':
case '/labtests/v1/types':
case '/packages/v1/types':
case '/plantbatches/v1/types':
case '/plants/v1/additives/types':
case '/plants/v1/waste/methods':
case '/sales/v1/customertypes':
case '/unitsofmeasure/v1/active':
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

$cre_http = new CRE_HTTP(array(
	'base_uri' => $cre_base
));


// Forward
switch ($_SERVER['REQUEST_METHOD']) {
case 'DELETE':
	break;
case 'GET':

	$req = new GuzzleHttp\Psr7\Request('GET', $req_path);
	$req = $req->withHeader('host', $cre_host);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);

	$res = $cre_http->send($req);

	break;

case 'POST':

	$req = new GuzzleHttp\Psr7\Request('POST', $req_path);
	$req = $req->withHeader('host', $cre_host);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);

	$src_json = file_get_contents('php://input');

	$res = $cre_http->send($req, array('json' => $src_json));

	break;

case 'PUT':

	$req = new GuzzleHttp\Psr7\Request('PUT', $req_path);
	$req = $req->withHeader('host', $cre_host);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);

	$src_json = file_get_contents('php://input');

	$res = $cre_http->send($req, array('json' => $src_json));

	break;

}

// var_dump($res);
$code = ($res ? $res->getStatusCode() : 500);
$body = ($res ? $res->getBody() : null);

$RES = $RES->withStatus($code);
$RES = $RES->withHeader('content-type', $res->getHeader('content-type'));
$RES = $RES->write($body);

return $RES;
