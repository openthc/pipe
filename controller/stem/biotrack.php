<?php
/**
	Stem pass-thru handler for BioTrack systems
	Accept the Request, Sanatize It, Process Response and Sanatize Objects

	To use the Passthru Configure this as the URL Base

	https://pipe.openthc.com/stem/biotrack

	Forward to:
		https://wa.biotrack.com/serverjson.asp

	Return Sanatized Response
*/

use Edoceo\Radix\DB\SQL;

$sql_hash = $_SESSION['sql-hash'];
$sql_file = sprintf('%s/var/stem-biotrack-%08x.sqlite', APP_ROOT, $sql_hash);
$sql_good = is_file($sql_file);

SQL::init('sqlite:' . $sql_file);
if (!$sql_good) {
	SQL::query("CREATE TABLE log_audit (cts not null default (strftime('%s','now')), code, path, req, res, err)");
}


$rce_base = 'https://wa.biotrackthc.net/serverjson.asp';
//$rce_base = 'http://localhost:8080';
//if (!empty($_SERVER['HTTP_OPENTHC_RCE_BASE'])) {
//	$rce_base = $_SERVER['HTTP_OPENTHC_RCE_BASE'];
//}
$rce_host = parse_url($rce_base, PHP_URL_HOST);


// Deny
if (count($_GET) != 0) {
	header('HTTP/1.1 400 Bad Request', true, 400);
	die('No Query String Parameters are Accepted');
}


// Detect Content Type
$type = strtok($_SERVER['CONTENT_TYPE'], ';');
switch ($type) {
case 'text/JSON':
	// Accurate for BioTrack System
	break;
case 'text/json': // OK?
	$ret['warn'][] = 'Set Content-Type to "text/JSON"';
	break;
case 'application/json': // The RFC one
	$ret['warn'][] = 'Set Content-Type to "text/JSON", application/json is only for application that work properly';
	break;
default:
	header('HTTP/1.0 400 Bad Request', true, 400);
	die('Specify "Content-Type: text/JSON"');
}


$src_json = file_get_contents('php://input');


// Good JSON?
$src_json = json_decode($src_json, true);
if (empty($src_json)) {
	return $RES->withJSON(array(
		'success' => 0,
		'_detail' => 'MFB#034: Error Decoding Input',
		'_errors' => json_last_error_msg(),
	));
}


// Action
if (empty($src_json['action'])) {
	return $RES->withJSON(array(
		'_detail' => 'The "action" parameter must be provided',
		//'_request' => $json,
		'success' => 0,
		'error' => 'Invalid Action',
		'errorcode' => 62,
	), 400);
}
if (!preg_match('/^\w+$/', $src_json['action'])) {
	return $RES->withJSON(array(
		'_detail' => 'Invalid "action" parameter',
		//'_request' => $json,
		'success' => 0,
	), 400);
}

// Now Just Forward to BioTrack
$src_json['API'] = '4.0';
if (empty($src_json['sessionid'])) {
	if ('login' != $src_json['action']) {
		// Error
	}
}
//if (!empty($this->_sid)) {
//	$arg['sessionid'] = $this->_sid;
//}

//if (!empty($this->_training)) {
//	$arg['training'] = 1;
//}

if (empty($src_json['nonce'])) {
	$src_json['nonce'] = $_SERVER['UNIQUE_ID'];
}


// Resolve Path
$rce_http = new RCE_HTTP(array(
	'base_uri' => $rce_base
));


// Forward
switch ($_SERVER['REQUEST_METHOD']) {
case 'POST':

	$req = new GuzzleHttp\Psr7\Request('POST', '');
	$req = $req->withHeader('host', $rce_host);
	$req = $req->withHeader('content-type', 'text/JSON');

	$res = $rce_http->send($req, array('json' => $src_json));

	break;
}

// this is a workaround for a biotrack bug where headers leak into the response body /djb 20170723
//$_raw = str_replace('Content-Type: text/plain', null, $_raw);
//$_raw = trim($_raw);


// var_dump($res);
$code = ($res ? $res->getStatusCode() : 500);
$body = ($res ? $res->getBody() : null);

$RES = $RES->withStatus($code);
$RES = $RES->withHeader('content-type', $res->getHeader('content-type'));
$RES = $RES->write($body);

return $RES;
