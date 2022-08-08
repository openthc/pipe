<?php
/**
 * OpenTHC Passthru
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller;

class OpenTHC extends \OpenTHC\Pipe\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		// Our special end-point
		switch ($ARG['path']) {
			case 'ping':
				return $RES->withJSON([
					'data' => 'PONG',
					'meta' => [
						'detail' => 'Responding to a Test Ping',
						'source' => 'openthc',
						'cre' => 'openthc',
						'cre_base' => $ARG['host'],
					]
				]);
		}

		// Resolve Path
		$req_path = sprintf('/%s', implode('/', $this->src_path));

		// Database
		$dbc = _dbc();
		$dbc->insert('log_audit', [
			'id' => $this->req_ulid,
			'lic_hash' => md5($_SERVER['HTTP_AUTHORIZATION']),
			'req_time' => date_format(new \DateTime(), \DateTime::RFC3339_EXTENDED),
			'req_head' => sprintf('%s %s HTTP/1.1', $_SERVER['REQUEST_METHOD'], $req_path),
		]);

		// Forward
		$url = $this->cre_base . $req_path . '?' . http_build_query($_GET);
		$req_head = [
			'accept: application/json',
			sprintf('authorization: %s', $_SERVER['HTTP_AUTHORIZATION']),
		];

		$req = $this->curl_init($url, $req_head);

		switch ($_SERVER['REQUEST_METHOD']) {
		case 'DELETE':
			curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		case 'GET':
			// Nothing
			break;
		case 'POST':
		case 'PUT':

			$src_json = file_get_contents('php://input');
			$src_json = json_decode($src_json, true);

			$req_body = json_encode($src_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$dbc->update('log_audit', [ 'req_body' => $req_body ], [ 'id' => $this->req_ulid ]);

			curl_setopt($req, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
			curl_setopt($req, CURLOPT_POSTFIELDS, $req_body);

			$req_head[] = 'content-type: application/json';
			curl_setopt($req, CURLOPT_HTTPHEADER, $req_head);

			break;

		}

		$this->curl_exec($req);

		// Update Response
		$dbc->update('log_audit', [
			'req_head' => $this->req_head,
			'res_time' => date_format(new \DateTime(), \DateTime::RFC3339_EXTENDED),
			'res_info' => json_encode($this->res_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'res_head' => $this->res_head,
			'res_body' => $this->res_body,
		], [ 'id' => $this->req_ulid ]);

		// Try to be Smart with Response Code?
		switch ($this->res_info['http_code']) {
			case 0: // curl error
				$RES = $RES->withStatus(500, 'curl error [LCM-131]');
				break;
			case 520: // special from CloudFlare
			case 524: // special from CloudFlare
				$RES = $RES->withStatus($this->res_info['http_code'], 'CloudFlare Error [LCM-134]');
				break;
			default:
				$RES = $RES->withStatus($this->res_info['http_code']);
		}

		$RES = $RES->withHeader('content-type', 'application/json');
		$RES = $RES->write($this->res_body);

		return $RES;
	}

}
