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

namespace App\Controller;

class LeafData extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		// Default
		$cre_base = 'https://bunk.openthc.org/leafdata/v2017';
		$cre_host = null;

		$src_path = explode('/', $ARG['path']);

		// Calculate System
		$system = [];
		$system[] = array_shift($src_path);
		if ('test' == $src_path[0]) {
			$system[] = array_shift($src_path);
		}
		$system = implode('/', $system);

		// Requested System
		switch ($system) {
		case 'pa':
		case 'pa/test':
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Not Implemented' ],
			], 501);
		case 'ut':
		case 'ut/test':
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Not Implemented' ],
			], 501);
		case 'wa':
		case 'wa-live':
			$cre_base = 'https://traceability.lcb.wa.gov/api/v1';
			break;
		case 'wa/test':
		case 'test':
		case 'wa-test':
			$cre_base = 'https://watest.leafdatazone.com/api/v1';
			break;
		default:
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid System [CSL#033]'
			), 400);
		}
		// From URL if not already set
		if (empty($cre_host)) {
			$cre_host = parse_url($cre_base, PHP_URL_HOST);
		}

		// Resolve Path
		// Clean these if the Client Added Them
		if ('api' == $src_path[0]) {
			array_shift($src_path); // drop it
		}
		if ('v1' == $src_path[0]) {
			array_shift($src_path); // drop it
		}
		$src_path = implode('/', $src_path);
		$req_path = $cre_base . '/' . $src_path . '?' . $_SERVER['QUERY_STRING'];
		$req_path = trim($req_path, '?');

		// Auth
		$RES = $this->_check_auth($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Database
		$sql_hash = md5($_SERVER['HTTP_X_MJF_MME_CODE']);
		$sql_file = _database_create_open('leafdata', $sql_hash);

		// Forward
		$cre_http = new CRE_HTTP();

		switch ($_SERVER['REQUEST_METHOD']) {
		case 'DELETE':

			$req = new GuzzleHttp\Psr7\Request('DELETE', $req_path);
			$req = $req->withHeader('host', $cre_host);
			$req = $req->withHeader('x-mjf-mme-code', $_SERVER['HTTP_X_MJF_MME_CODE']);
			$req = $req->withHeader('x-mjf-key', $_SERVER['HTTP_X_MJF_KEY']);

			$res = $cre_http->send($req);

			break;

		case 'GET':

			$req = new GuzzleHttp\Psr7\Request('GET', $req_path);
			$req = $req->withHeader('host', $cre_host);
			$req = $req->withHeader('x-mjf-mme-code', $_SERVER['HTTP_X_MJF_MME_CODE']);
			$req = $req->withHeader('x-mjf-key', $_SERVER['HTTP_X_MJF_KEY']);

			$res = $cre_http->send($req);

			break;

		case 'POST':

			$src_json = file_get_contents('php://input');
			$src_json = json_decode($src_json, true);

			$req = new GuzzleHttp\Psr7\Request('POST', $req_path);
			$req = $req->withHeader('host', $cre_host);
			$req = $req->withHeader('x-mjf-mme-code', $_SERVER['HTTP_X_MJF_MME_CODE']);
			$req = $req->withHeader('x-mjf-key', $_SERVER['HTTP_X_MJF_KEY']);

			$res = $cre_http->send($req, array('json' => $src_json));

			break;

		}


		// Corecting the MIME Type
		$RES = $RES->withHeader('content-type', 'application/json; charset=utf-8');

		// Try to be Smart with Response Code?
		$code = ($res ? $res->getStatusCode() : 500);
		$body = ($res ? $res->getBody()->__toString() : null);

		$RES = $RES->withStatus($code);
		$RES = $RES->write($body);

		return $RES;
	}

	/**
		Authentication Validator
	*/
	function _check_auth($RES)
	{
		$_SERVER['HTTP_X_MJF_MME_CODE'] = trim($_SERVER['HTTP_X_MJF_MME_CODE']);
		$_SERVER['HTTP_X_MJF_MME_CODE'] = strtoupper($_SERVER['HTTP_X_MJF_MME_CODE']);

		$_SERVER['HTTP_X_MJF_KEY'] = trim($_SERVER['HTTP_X_MJF_KEY']);

		$lic = $_SERVER['HTTP_X_MJF_MME_CODE'];
		$key = $_SERVER['HTTP_X_MJF_KEY'];

		if (empty($lic) || empty($key)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'License or Key missing [CSL#025]' ]
			], 400);
		}

		return $RES;
	}

}
