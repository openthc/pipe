<?php
/**
	Stem pass-thru handler for METRC systems
	Accept the Request, Sanatize It, Process Response and Sanatize Objects

	To use the Passthru Configure this as the URL Base

	https://pipe.openthc.com/stem/metrc

	Forward to:
		https://sandbox-api-ca.metrc.com/api/v1

	Return Sanatized Response
*/

use Edoceo\Radix\DB\SQL;

$cre_base = 'https://sandbox-api-ca.metrc.com';
//$cre_base = 'http://localhost:8080';
//if (!empty($_SERVER['HTTP_OPENTHC_CRE_BASE'])) {
//	$cre_base = $_SERVER['HTTP_OPENTHC_CRE_BASE'];
//}
$cre_host = null;

// Requested System
switch ($ARG['system']) {
case 'api-ca.metrc.com':
//case 'sandbox-api-ca.metrc.com':
case 'api-co.metrc.com':
case 'sandbox-api-co.metrc.com':
case 'api-nv.metrc.com':
//case 'sandbox-api-nv.metrc.com':
case 'api-or.metrc.com':
case 'sandbox-api-or.metrc.com':
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

// Auth
// $RES = _req_auth($RES);
// if (200 != $RES->getStatusCode()) {
// 	return $RES;
// }


// Database
$sql_hash = crc32($_SERVER['HTTP_AUTHORIZATION']);
$sql_file = sprintf('%s/var/stem-metric-%08x.sqlite', APP_ROOT, $sql_hash);
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
case '/harvests/v1/active':
case '/harvests/v1/onhold':
case '/harvests/v1/inactive':
case '/packages/v1/active':
case '/patients/v1/active':
case '/plantbatches/v1/active':
case '/plants/v1/vegetative':
case '/rooms/v1/active':
case '/sales/v1/receipts':

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
}

// var_dump($res);
$code = ($res ? $res->getStatusCode() : 500);
$body = ($res ? $res->getBody() : null);

$RES = $RES->withStatus($code);
$RES = $RES->withHeader('content-type', $res->getHeader('content-type'));
$RES = $RES->write($body);

return $RES;


/**
	Authentication Validator
*/
function _req_auth($RES)
{
	$auth = $_SERVER['HTTP_AUTHORIZATION'];

	if (empty($auth)) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Invalid Auth [CSM#030]',
		), 400);
	}

	if (!preg_match('/^Basic\s+(.+)$/', $auth, $m)) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Invalid Auth [CSM#037]',
		), 400);
	}

	return $RES;
}
