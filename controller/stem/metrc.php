<?php
/**
	Stem pass-thru handler for METRC systems

	To use the Passthru Configure this as the URL Base

	https://pipe.openthc.com/stem/metrc

	Forward to:
	protected $_rbe_base = 'https://sandbox-api-ca.metrc.com/api/v1';
	protected $_rbe_host = 'traceability.lcb.wa.gov';

	Return Sanatized Response
*/

use Edoceo\Radix\DB\SQL;

$rce_host = 'https://sandbox-api-ca.metrc.com';
//$rce_host = 'http://localhost:8080';
//if (!empty($_SERVER['HTTP_OPENTHC_RCE'])) {
//}


// Auth
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

//$auth = base64_decode($auth);


// Database for Logging
$sql_file = sprintf('%s/var/stem-metric-%08x.sqlite', APP_ROOT, crc32($auth));
$sql_good = is_file($sql_file);

SQL::init('sqlite:' . $sql_file);
if (!$sql_good) {
	SQL::query("CREATE TABLE log_audit (cts not null default (strftime('%s','now')), code, path, req, res, err)");
}


// Resolve Path
//$src_path = basename($_SERVER['SCRIPT_URL']); // OR REQUEST_URI ??
$src_path = $_SERVER['REQUEST_URI']; // Contains Query String
$src_path = str_replace('/stem/metrc/', null, $src_path);

$src_json = file_get_contents('php://input');

$req_path = $rce_host . '/' . $src_path;

//if (!empty($_SERVER['QUERY_STRING'])) {
//	$req_path.= '?' . $_SERVER['QUERY_STRING'];
//}

$rce = new RCE_HTTP();


// echo $req_path;
switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':

	$req = new GuzzleHttp\Psr7\Request('GET', $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('host', 'sandbox-api-ca.metrc.com');

	$res = $rce->send($req);

	break;

case 'POST':

	$req = new GuzzleHttp\Psr7\Request('POST', $req_path);
	$req = $req->withHeader('authorization', $_SERVER['HTTP_AUTHORIZATION']);
	$req = $req->withHeader('host', 'sandbox-api-ca.metrc.com');

	$res = $rce->send($req, array('json' => $src_json));

	break;
}

// var_dump($res);
$code = ($res ? $res->getStatusCode() : 0);
$body = ($res ? $res->getBody()->__toString() : null);

_exit_json($body, $code);

exit(0);
