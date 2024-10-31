<?php
/**
 * LeafData PIPE
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller;

class LeafData extends \OpenTHC\Pipe\Controller\Base
{
	// Default
	protected $cre_base = 'https://bunk.openthc.dev/leafdata/v2017';

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		// Our special end-point (before error-check of check_auth)
		$chk = basename($ARG['path']);
		if ('ping' == $chk) {
			$this->_check_auth($RES);
			return $this->sendPong($RES);
		}

		// Auth Headers
		$RES = $this->_check_auth($RES);

		if (200 != $RES->getStatusCode()) {
			return $RES;
		}


		// Resolve Path
		// Clean these if the Client Added Them
		if ('api' == $this->req_path[0]) {
			array_shift($this->req_path); // drop it
		}
		if ('v1' == $this->req_path[0]) {
			array_shift($this->req_path); // drop it
		}
		$req_path = implode('/', $this->req_path);
		$req_path = sprintf('/%s?%s', $req_path, $_SERVER['QUERY_STRING']);
		$req_path = trim($req_path, '?');

		// Database
		$dbc = _dbc();
		$dbc->insert('log_audit', [
			'id' => $this->req_ulid,
			'lic_hash' => md5($_SERVER['HTTP_X_MJF_MME_CODE']),
			'req_name' => sprintf('%s %s', $_SERVER['REQUEST_METHOD'], $req_path),
		]);

		// Forward
		$url = $this->cre_base . $req_path;
		$req_head = [
			'accept: application/json',
			// 'content-type: application/json',
			sprintf('x-mjf-mme-code: %s', $_SERVER['HTTP_X_MJF_MME_CODE']),
			sprintf('x-mjf-key: %s', $_SERVER['HTTP_X_MJF_KEY']),
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
			'res_meta' => json_encode($this->res_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'res_head' => $this->res_head,
			'res_body' => $this->res_body,
		], [ 'id' => $this->req_ulid ]);

		// Try to be Smart with Response Code?
		$RES = $RES->withStatus($this->res_info['http_code'] ?: 500);
		$RES = $RES->withHeader('content-type', 'application/json; charset=utf-8');
		$RES = $RES->write($this->res_body);

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
				'meta' => [ 'note' => 'License or Key missing [CSL-025]' ]
			], 400);
		}

		return $RES;
	}

	/**
	 * Parse the CRE from the Path, Mutates $this
	 */
	function _check_cre($RES)
	{
		$cre = [];
		$cre[] = array_shift($this->req_path);
		if ('test' == $this->req_path[0]) {
			$cre[] = array_shift($this->req_path);
		}

		$this->cre = implode('/', $cre);

		// Requested CRE
		switch ($this->cre) {
			case 'pa':
			case 'pa/test':
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'note' => 'Not Implemented [ACL-161]' ],
				], 501);
			case 'ut':
			case 'ut/test':
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'note' => 'Not Implemented [ACL-167]' ],
				], 501);
			case 'wa':
			case 'wa-live':
				$this->cre_base = 'https://traceability.lcb.wa.gov/api/v1';
				break;
			case 'wa/test':
			case 'test':
			case 'wa-test':
				$this->cre_base = 'https://watest.leafdatazone.com/api/v1';
				break;
			default:
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'note' => 'Invalid CRE [CSL-033]' ]
				], 400);
		}

		return $RES;
	}

}
