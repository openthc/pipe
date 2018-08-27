<?php
/**
	Stem pass-thru handler for LeafData systems
	Accept the Request, Sanatize It, Process Response and Sanatize Objects

	To use the Passthru Configure this as the URL Base

	https://pipe.openthc.com/stem/leafdata

	Forward to:
		https://traceability.lcb.wa.gov/api/v1

	Return Sanatized Response
*/

use Edoceo\Radix\DB\SQL;

$rce_base = 'https://traceability.lcb.wa.gov/api/v1';
//$rce_base = 'https://watest.leafdatazone.com/api/v1';
//$rce_base = 'http://localhost:8080/api/v1';
//if (!empty($_SERVER['HTTP_OPENTHC_RCE_BASE'])) {
//	$rce_base = $_SERVER['HTTP_OPENTHC_RCE_BASE'];
//}
$rce_host = parse_url($rce_base, PHP_URL_HOST);


// Auth
$RES = _do_auth($RES);
if (200 != $RES->getStatusCode()) {
	return $RES;
}


// Database
$sql_hash = crc32($_SERVER['HTTP_X_MJF_MME_CODE'] . $_SERVER['HTTP_X_MJF_KEY']);
$sql_file = sprintf('%s/var/stem-leafdata-%08x.sqlite', APP_ROOT, $sql_hash);
$sql_good = is_file($sql_file);

SQL::init('sqlite:' . $sql_file);
if (!$sql_good) {
	SQL::query("CREATE TABLE log_audit (cts not null default (strftime('%s','now')), code, path, req, res, err)");
}


// Resolve Path
$src_path = $_SERVER['REQUEST_URI']; // Contains Query String
$src_path = str_replace('/stem/leafdata/', null, $src_path);

$src_json = file_get_contents('php://input');

$req_path = $rce_base . '/' . $src_path;

$rce_http = new RCE_HTTP();


// Forward
switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':

	$req = new GuzzleHttp\Psr7\Request('GET', $req_path);
	$req = $req->withHeader('host', $rce_host);
	$req = $req->withHeader('x-mjf-mme-code', $_SERVER['HTTP_X_MJF_MME_CODE']);
	$req = $req->withHeader('x-mjf-key', $_SERVER['HTTP_X_MJF_KEY']);

	$res = $rce_http->send($req);

	break;

case 'POST':

	$req = new GuzzleHttp\Psr7\Request('POST', $req_path);
	$req = $req->withHeader('host', $rce_host);
	$req = $req->withHeader('x-mjf-mme-code', $_SERVER['HTTP_X_MJF_MME_CODE']);
	$req = $req->withHeader('x-mjf-key', $_SERVER['HTTP_X_MJF_KEY']);

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
	$_SERVER['HTTP_X_MJF_MME_CODE'] = trim($_SERVER['HTTP_X_MJF_MME_CODE']);
	$_SERVER['HTTP_X_MJF_KEY'] = trim($_SERVER['HTTP_X_MJF_KEY']);

	$lic = $_SERVER['HTTP_X_MJF_MME_CODE'];
	$key = $_SERVER['HTTP_X_MJF_KEY'];

	if (empty($lic) || empty($key)) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'License or Key not missing [CSL#025]',
		), 400);
	}

	return $RES;
}