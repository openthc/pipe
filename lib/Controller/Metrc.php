<?php
/**
 * Metrc Passthru
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller;

class Metrc extends \OpenTHC\Pipe\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		// Our special end-point
		$chk = basename($ARG['path']);
		if ('ping' == $chk) {

			$this->_check_cre($RES);

			return $RES->withJSON([
				'data' => 'PONG',
				'meta' => [
					'detail' => 'Responding to a Test Ping',
					'source' => 'openthc',
					'cre' => $this->cre,
					'cre_base' => $this->cre_base,
				]
			]);
		}

		$RES = $this->_check_cre($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Resolve Path
		$req_path = sprintf('/%s', implode('/', $this->req_path));

		// A cheap-ass, incomplete filter
		switch ($req_path) {
		case '/facilities/v1':
		case '/harvests/v1/waste/types':
		case '/items/v1/categories':
		case '/labtests/v1/states':
		case '/labtests/v1/types':
		case '/locations/v1/types':
		case '/packages/v1/types':
		case '/plantbatches/v1/types':
		case '/plants/v1/additives/types':
		case '/plants/v1/waste/methods':
		case '/sales/v1/customertypes':
		case '/unitsofmeasure/v1/active':
			// These don't require a licenseNumber
			// nor do they require date attributes
			unset($_GET['lastModifiedStart']);
			unset($_GET['lastModifiedEnd']);
			break;
		default:
			// Everything else does
			if (empty($_GET['licenseNumber'])) {
				// Fatal?
				return $RES->withJSON([
					'data' => null,
					'meta' => [
						'origin' => 'openthc',
						'detail' => 'The License Number parameter must be supplied [LCM-093]'
					]
				], 400);
			}

			break;
		}

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

	/**
	 * Parse the System from the Path, Mutates $this
	 */
	function _check_cre($RES)
	{
		// Requested System
		switch ($this->req_host) {
		case 'ak':
		case 'ca':
		case 'co':
		case 'la':
		case 'ma':
		case 'md':
		case 'me':
		case 'mi':
		case 'mo':
		case 'mt':
		case 'nv':
		case 'oh':
		case 'or':
			$this->cre_base = sprintf('https://api-%s.metrc.com', $this->cre);
			break;
		case 'api-ak.metrc.com':
		case 'api-ca.metrc.com':
		case 'api-co.metrc.com':
		case 'api-la.metrc.com':
		case 'api-ma.metrc.com':
		case 'api-md.metrc.com':
		case 'api-mi.metrc.com':
		case 'api-mo.metrc.com':
		case 'api-mt.metrc.com':
		case 'api-nv.metrc.com':
		case 'api-oh.metrc.com':
		case 'api-ok.metrc.com':
		case 'api-or.metrc.com':
			$this->cre_base = sprintf('https://%s', $this->cre);
			break;
		case 'sandbox-api-co.metrc.com':
		case 'sandbox-api-md.metrc.com':
		case 'sandbox-api-ok.metrc.com':
		case 'sandbox-api-or.metrc.com':
			$this->cre_base = sprintf('https://%s', $this->cre);
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
