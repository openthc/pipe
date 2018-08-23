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

$rce_base = 'https://sandbox-api-ca.metrc.com';
//$rce_base = 'http://localhost:8080';
//if (!empty($_SERVER['HTTP_OPENTHC_RCE_BASE'])) {
//	$rce_base = $_SERVER['HTTP_OPENTHC_RCE_BASE'];
//}
$rce_host = parse_url($rce_base, PHP_URL_HOST);


// Auth
$RES = _do_auth($RES);
if (200 != $RES->getStatus()) {
	return $RES;
}


// Database
$sql_hash = crc32($_SERVER['HTTP_AUTHORIZATION']);
$sql_file = sprintf('%s/var/stem-metric-%08x.sqlite', APP_ROOT, $sql_hash);
$sql_good = is_file($sql_file);

SQL::init('sqlite:' . $sql_file);
if (!$sql_good) {
	SQL::query("CREATE TABLE log_audit (cts not null default (strftime('%s','now')), code, path, req, res, err)");
}


// Resolve Path
$src_path = $_SERVER['REQUEST_URI']; // Contains Query String
$src_path = str_replace('/stem/metrc/', null, $src_path);

switch ($src_path) {
case '/harvests/v1/active':
case '/harvests/v1/onhold':
case '/harvests/v1/inactive':
case '/items/v1/categories':
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
			'detail' => 'The License Number parameter must be supplied',
		), 400, JSON_PRETTY_PRINT);
	}

	break;
}

$src_json = file_get_contents('php://input');

$req_path = $rce_base . '/' . $src_path;

$rce_http = new RCE_HTTP();


// Forward
switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':

	$req = new GuzzleHttp\Psr7\Request('GET', $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('host', $rce_host);

	$res = $rce_http->send($req);

	break;

case 'POST':

	$req = new GuzzleHttp\Psr7\Request('POST', $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('host', $rce_host);

	$res = $rce_http->send($req, array('json' => $src_json));

	break;
}

// var_dump($res);
$code = ($res ? $res->getStatusCode() : 500);
$body = ($res ? $res->getBody()->__toString() : null);

$RES = $RES->withStatus($code);
$RES = $RES->write($body);

return $RES;


/**
	Authentication Validator
*/
function _do_auth($RES)
{
	$auth = $_SERVER['HTTP_AUTHORIZATION'];

	if (empty($auth)) {
		_exit_json(array(
			'status' => 'failure',
			'detail' => 'Invalid Auth [CSM#030]',
		), 400);
	}

	if (!preg_match('/^Basic\s+(.+)$/', $auth, $m)) {
		_exit_json(array(
			'status' => 'failure',
			'detail' => 'Invalid Auth [CSM#037]',
		), 400);
	}

	return $RES;
}
