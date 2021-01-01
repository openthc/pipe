<?php
/**
 * Stem pass-thru handler for METRC systems
 */

use Edoceo\Radix\DB\SQL;

namespace App\Controller;

class METRC extends \App\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		parent::__invoke($REQ, $RES, $ARG);

		$RES = $this->_check_system($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// Resolve Path
		$src_path = implode('/', $src_path);
		$req_path = sprintf('/%s?%s', $src_path, $_SERVER['QUERY_STRING']);
		$req_path = trim($req_path, '?');


		// A cheap-ass, incomplete filter
		switch ($src_path) {
		case 'facilities/v1':
		case 'harvests/v1/waste/types':
		case 'items/v1/categories':
		case 'labtests/v1/states':
		case 'labtests/v1/types':
		case 'packages/v1/types':
		case 'plantbatches/v1/types':
		case 'plants/v1/additives/types':
		case 'plants/v1/waste/methods':
		case 'sales/v1/customertypes':
		case 'unitsofmeasure/v1/active':
			// These don't require a licenseNumber
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
		$url = $this->cre_base . $req_path;
		$req_head = [
			'accept: application/json',
			sprintf('authorization: %s', $_SERVER['HTTP_AUTHORIZATION']),
		];

		$cre = new \App\CRE();
		$req = $cre->curl_init($cre_base . $req_path);

		// Forward
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

		$res_body = $cre->curl_exec($req);
		$dbc->query('UPDATE log_audit SET res_time = now() WHERE id = :l', [ ':l' => $this->req_ulid ]);

		$res_info = $cre->getResponseInfo();

		// Update Response
		$dbc->update('log_audit', [
			'req_head' => $cre->getRequestHead(),
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
	 * Parse the System from the Path, Mutates $this
	 */
	function _check_system($RES)
	{
		$this->system = array_shift($this->src_path);

		// Requested System
		switch ($this->system) {
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
		case 'api-or.metrc.com':
			$this->cre_base = sprintf('https://%s', $this->system);
			break;
		case 'sandbox-api-or.metrc.com':
		case 'sandbox-api-co.metrc.com':
		case 'sandbox-api-md.metrc.com':
		case 'sandbox-api-me.metrc.com':
			// SubSwitch
			// so external users can use a canonical name and we'll adjust back here
			// based on the switching the METRC might do with their sandox endpoints
			switch ($this->system) {
				case 'sandbox-api-me.metrc.com':
					$this->system = 'sandbox-api-md.metrc.com'; // re-maps to Maryland
				break;
			}
			$this->cre_base = sprintf('https://%s', $system);
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
