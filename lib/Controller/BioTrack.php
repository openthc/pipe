<?php
/**
 * BioTrack pass-thru
 * Accept the Request, Sanatize It, Process Response and Sanatize Objects
 * To use the Passthru Configure this as the URL Base
 * $BASE/biotrack/<SYSTEM>
 * Forward to:
 * https://wa.biotrack.com/serverjson.asp
 * Return Sanatized Response
 */

namespace App\Controller;

class BioTrack extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		// Our special end-point
		$chk = basename($ARG['path']);
		if ('ping' == $chk) {
			return $RES->withJSON([
				'data' => 'PONG',
				'meta' => [
					'detail' => 'Responding to a Test Ping',
					'source' => 'openthc',
					'cre' => $ARG['path'],
					'cre_base' => 'biotrack....',
				]
			]);
		}

		$cre_base = 'https://wa.biotrackthc.net/serverjson.asp';
		$cre_host = parse_url($cre_base, PHP_URL_HOST);

		// Deny
		if (count($_GET) != 0) {
			return $RES->withJSON(array(
				'success' => 0,
				'_detail' => 'No Query String parameters are accepted [CSB-040]',
			), 400);
		}

		if ('POST' != $_SERVER['REQUEST_METHOD']) {
			return $RES->withJSON(array(
				'success' => 0,
				'_detail' => 'Only a POST is allowed here [CSB-045]',
			), 405);
		}


		// Detect Content Type
		$type = strtok($_SERVER['CONTENT_TYPE'], ';');
		switch ($type) {
		case 'text/JSON':
			// Accurate for BioTrack System
			break;
		case 'text/json': // OK?
			$ret['warn'][] = 'Set content-type to "text/JSON"';
			break;
		case 'application/json': // The RFC one
			$ret['warn'][] = 'Set content-type to "text/JSON", application/json is only for application that work properly';
			break;
		default:
			return $RES->withJSON(array(
				'data' => null,
				'meta' => [ 'detail' => 'Specify "content-type: text/JSON" [CSB-067]' ]
			), 400);
		}


		// Good JSON?
		$src_json = file_get_contents('php://input');
		$src_json = json_decode($src_json, true);
		if (empty($src_json)) {
			return $RES->withJSON(array(
				'data' => null,
				'meta' => [
					'detail' => 'Error Decoding Input [CSB-034]',
					'error' => json_last_error_msg(),
				]
			), 400);
		}


		// API Version Check
		$src_json['API'] = '4.0';


		// Assign NONCE
		if (empty($src_json['nonce'])) {
			$src_json['nonce'] = $_SERVER['UNIQUE_ID'];
		}


		// Action
		if (empty($src_json['action'])) {
			return $RES->withJSON(array(
				'success' => 0,
				'error' => 'Invalid Action',
				'errorcode' => 62,
				'_detail' => 'The "action" parameter must be provided',
				//'_request' => $json,
			), 400);
		}
		if (!preg_match('/^\w+$/', $src_json['action'])) {
			return $RES->withJSON(array(
				'success' => 0,
				'_detail' => 'Invalid "action" parameter [CSB-098]',
			), 400);
		}


		// API Session Check
		if (empty($src_json['sessionid'])) {
			if ('login' != $src_json['action']) {
				return $RES->withJSON(array(
					'success' => 0,
					'_detail' => 'A "sessionid" must be provided [CSB-109]',
				), 400);
			}
		}


		// Now Just Forward to BioTrack
		// $dbc->insert('log_audit', [
		// 	'id' => $this->req_ulid,
		// 	'req_head' => '',
		// 	'req_body' => '',
		// ]);

		// Resolve Path
		$cre_http = new \CRE_HTTP(array(
			'base_uri' => $cre_base
		));
		// $req = _curl_init($url);

		// Forward
		switch ($_SERVER['REQUEST_METHOD']) {
		case 'POST':

			$req = new \GuzzleHttp\Psr7\Request('POST', '');
			$req = $req->withHeader('host', $cre_host);
			$req = $req->withHeader('content-type', 'text/JSON');

			$res = $cre_http->send($req, array('json' => $src_json));

			break;
		}

		// this is a workaround for a biotrack bug where headers leak into the response body /djb 20170723
		//$_raw = str_replace('Content-Type: text/plain', null, $_raw);
		//$_raw = trim($_raw);

		$dbc->update('log_audit', [
			'req_head' => $cre->getRequestHead(),
			// 'res_time' => 'now()',
			'res_info' => json_encode($res_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'res_head' => $cre->getResponseHead(),
			'res_body' => $res_body,
		], [ 'id' => $this->req_ulid ]);

		$code = ($res ? $res->getStatusCode() : 500);
		// $code = curl_getinfo($req, )
		$body = ($res ? $res->getBody() : null);
		// $body = curl_exec($req);

		$RES = $RES->withStatus($code);
		$RES = $RES->withHeader('content-type', $res->getHeader('content-type'));
		$RES = $RES->write($body);

		return $RES;

	}

	// function _do_request()

}
