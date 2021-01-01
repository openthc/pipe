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

namespace App\Controller;

class LeafData extends \App\Controller\Base
{
	// Default
	protected $cre_base = 'https://bunk.openthc.dev/leafdata/v2017';

	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		// Auth Headers
		$RES = $this->_check_auth($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// System
		$RES = $this->_check_system($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Resolve Path
		// Clean these if the Client Added Them
		if ('api' == $this->src_path[0]) {
			array_shift($this->src_path); // drop it
		}
		if ('v1' == $this->src_path[0]) {
			array_shift($this->src_path); // drop it
		}
		$req_path = implode('/', $this->src_path);
		$req_path = sprintf('/%s?%s', $req_path, $_SERVER['QUERY_STRING']);
		$req_path = trim($req_path, '?');

		// Database
		$dbc = _dbc();
		$dbc->insert('log_audit', [
			'id' => $this->req_ulid,
			'lic_hash' => md5($_SERVER['HTTP_X_MJF_MME_CODE']),
			'req_head' => sprintf('%s %s HTTP/1.1', $_SERVER['REQUEST_METHOD'], $req_path),
		]);

		// Forward
		$url = $this->cre_base . $req_path;
		$req_head = [
			'accept: application/json',
			// 'content-type: application/json',
			sprintf('x-mjf-mme-code: %s', $_SERVER['HTTP_X_MJF_MME_CODE']),
			sprintf('x-mjf-key: %s', $_SERVER['HTTP_X_MJF_KEY']),
		];

		$cre = new \App\CRE();
		$req = $cre->curl_init($url, $req_head);

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

			curl_setopt($req, CURLOPT_POST, true);
			curl_setopt($req, CURLOPT_POSTFIELDS, $req_body);
			$req_head[] = 'content-type: application/json';
			curl_setopt($req, CURLOPT_HTTPHEADER, $req_head);

			break;

		}

		$res_body = $cre->curl_exec($req);
		$dbc->query('UPDATE log_audit SET res_time = now() WHERE id = :l', [ ':l' => $this->req_ulid ]);

		$res_info = $cre->getResponseInfo();

		// Update Response
		$dbc->update('log_audit', [
			'req_head' => $cre->getRequestHead(),
			// 'res_time' => 'now()',
			'res_info' => json_encode($res_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'res_head' => $cre->getResponseHead(),
			'res_body' => $res_body,
		], [ 'id' => $this->req_ulid ]);

		// Try to be Smart with Response Code?
		$RES = $RES->withStatus($res_info['http_code'] ?: 500);
		$RES = $RES->withHeader('content-type', 'application/json; charset=utf-8');
		$RES = $RES->write($res_body);

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
				'meta' => [ 'detail' => 'License or Key missing [CSL-025]' ]
			], 400);
		}

		return $RES;
	}

	/**
	 * Parse the System from the Path, Mutates $this
	 */
	function _check_system($RES)
	{
		$system = [];
		$system[] = array_shift($this->src_path);
		if ('test' == $this->src_path[0]) {
			$system[] = array_shift($this->src_path);
		}

		$this->system = implode('/', $system);

		// Requested System
		switch ($this->system) {
			case 'pa':
			case 'pa/test':
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'detail' => 'Not Implemented [ACL-161]' ],
				], 501);
			case 'ut':
			case 'ut/test':
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'detail' => 'Not Implemented [ACL-167]' ],
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
					'meta' => [
						'source' => 'openthc',
						'detail' => 'Invalid System [CSL-033]',
					]
				], 400);
		}

		return $RES;
	}

}
