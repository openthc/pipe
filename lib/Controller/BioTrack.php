<?php
/**
 * BioTrack PIPE
 *
 * SPDX-License-Identifier: MIT
 *
 * Accept the Request, Sanatize It, Process Response and Sanatize Objects
 * To use the Passthru Configure this as the URL Base
 * $BASE/biotrack/<SYSTEM>
 * Forward to:
 * https://wa.biotrack.com/serverjson.asp
 * Return Sanatized Response
 */

namespace OpenTHC\Pipe\Controller;

class BioTrack extends \OpenTHC\Pipe\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		$dts = new \DateTime();
		$req_path = sprintf('/%s', implode('/', $this->req_path));

		// Capture Request Headers

		$dbc = _dbc();
		$dbc->insert('log_audit', [
			'id' => $this->req_ulid,
			'lic_hash' => sprintf('%s/%s', $this->company_id, $this->license_id),
			'req_time' => $dts->format(\DateTime::RFC3339_EXTENDED),
			'req_head' => sprintf('%s %s HTTP/1.1', $_SERVER['REQUEST_METHOD'], $req_path),
		]);

		// Validate the CRE
		$RES = $this->_check_cre($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Our special end-point
		if ('ping' == $this->req_path[0]) {
			return $RES->withJSON([
				'data' => 'PONG',
				'meta' => [
					'detail' => 'Responding to a Test Ping',
					'source' => 'openthc',
					'cre' => $ARG['path'],
				]
			]);
		}

		// Deny
		if (count($_GET) != 0) {
			return $RES->withJSON(array(
				'success' => 0,
				'meta' => [ 'note' => 'No Query String parameters are accepted [CSB-040]' ],
			), 400);
		}

		if ('POST' != $_SERVER['REQUEST_METHOD']) {
			return $RES->withJSON(array(
				'success' => 0,
				'meta' => [ 'note' => 'Only a POST is allowed here [CSB-045]' ],
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
					'note' => 'Error Decoding Input [CSB-034]',
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
				'meta' => [ 'note' => 'The "action" parameter must be provided [CSB-106]' ],
				//'_request' => $json,
			), 400);
		}
		if (!preg_match('/^\w+$/', $src_json['action'])) {
			return $RES->withJSON(array(
				'success' => 0,
				'meta' => [ 'note' => 'Invalid "action" parameter [CSB-113]' ],
			), 400);
		}


		// API Session Check
		if (empty($src_json['sessionid'])) {
			if ('login' != $src_json['action']) {
				return $RES->withJSON(array(
					'success' => 0,
					'meta' => [ 'note' => 'A "sessionid" must be provided [CSB-123]' ],
				), 400);
			}
		}

		$url = $this->cre_base;
		$req_head = [
			'accept: application/json',
			'content-type: text/JSON',
		];
		$req = $this->curl_init($url, $req_head);

		$req_body = json_encode($src_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$dbc->update('log_audit', [ 'req_body' => $req_body ], [ 'id' => $this->req_ulid ]);

		curl_setopt($req, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
		curl_setopt($req, CURLOPT_POSTFIELDS, $req_body);
		curl_setopt($req, CURLOPT_HTTPHEADER, $req_head);

		$this->curl_exec($req);

		// Update Response
		$res_time = new \DateTime();
		$dbc->update('log_audit', [
			'req_head' => $this->req_head,
			'res_time' => $res_time->format(\DateTime::RFC3339_EXTENDED),
			'res_info' => json_encode($this->res_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'res_head' => $this->res_head,
			'res_body' => $this->res_body,
		], [ 'id' => $this->req_ulid ]);

		// this is a workaround for a biotrack bug where headers leak into the response body /djb 20170723
		//$_raw = str_replace('Content-Type: text/plain', null, $_raw);
		//$_raw = trim($_raw);

		$RES = $RES->withStatus($this->res_info['http_code']);
		$RES = $RES->withHeader('content-type', 'application/json');
		$RES = $RES->write($this->res_body);

		return $RES;

	}

	/**
	 * Parse the System from the Path, Mutates $this
	 */
	function _check_cre($RES)
	{
		switch ($this->req_host) {
			case 'nm':
			case 'mcp-tracking.nmhealth.org':
				$this->cre_base = 'https://mcp-tracking.nmhealth.org/serverjson.asp';
				break;
			default:
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'detail' => 'CRE Not Found [LCM-055]' ],
				], 404);
		}

		return $RES;
	}

}
