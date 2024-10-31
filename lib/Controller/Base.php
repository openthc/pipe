<?php
/**
 * PIPE Base Controller
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller;

class Base extends \OpenTHC\Controller\Base
{
	protected $cre_base;

	// Request Stuff
	protected $req_host;
	protected $req_path;
	protected $req_head;
	protected $req_ulid;

	protected $res_info;
	protected $res_body;

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		// Contact
		if ( ! empty($_SERVER['HTTP_OPENTHC_CONTACT_ID'])) {
			$this->contact_id = $_SERVER['HTTP_OPENTHC_CONTACT_ID'];
		} elseif ( ! empty($_SERVER['HTTP_OPENTHC_CONTACT'])) {
			$this->contact_id = $_SERVER['HTTP_OPENTHC_CONTACT'];
		}
		// if (empty($this->contact_id)) {
		// 	throw new \Exception('Invalid Request [PCB-035]', 403);
		// }

		// Company
		if ( ! empty($_SERVER['HTTP_OPENTHC_COMPANY_ID'])) {
			$this->company_id = $_SERVER['HTTP_OPENTHC_COMPANY_ID'];
		} elseif ( ! empty($_SERVER['HTTP_OPENTHC_COMPANY'])) {
			$this->company_id = $_SERVER['HTTP_OPENTHC_COMPANY'];
		}
		if (empty($this->company_id)) {
			throw new \Exception('Invalid Request [PCB-045]', 403);
		}

		// License
		if ( ! empty($_SERVER['HTTP_OPENTHC_LICENSE_ID'])) {
			$this->license_id = $_SERVER['HTTP_OPENTHC_LICENSE_ID'];
		} elseif ( ! empty($_SERVER['HTTP_OPENTHC_LICENSE'])) {
			$this->license_id = $_SERVER['HTTP_OPENTHC_LICENSE'];
		}
		// if (empty($this->license_id)) {
		// 	throw new \Exception('Invalid Request [PCB-055]', 403);
		// }

		$this->req_ulid = _ulid();
		$this->req_host = $ARG['host'];
		$this->req_path = explode('/', $ARG['path']);
	}

	/**
	 *
	 */
	function sendPong($RES)
	{
		return $RES->withJSON([
			'data' => [
				'cre_base' => $this->cre_base,
				'req_host' => $this->req_host,
				'req_path' => $req_path,
			],
			'meta' => [
				'note' => 'PONG',
			]
		]);
	}

	/**
	 *
	 */
	function curl_init($url, $head=[])
	{
		$req = _curl_init($url);

		curl_setopt($req, CURLOPT_HTTPHEADER, $head);
		curl_setopt($req, CURLINFO_HEADER_OUT, true);

		$this->tmp_file_head = tmpfile();
		curl_setopt($req, CURLOPT_WRITEHEADER, $this->tmp_file_head);

		return $req;

	}

	/**
	 *
	 */
	function curl_exec($req)
	{
		$this->res_body = curl_exec($req);
		$this->res_info = curl_getinfo($req);

		$this->req_head = trim($this->res_info['request_header']);
		unset($this->res_info['request_header']);

		rewind($this->tmp_file_head);
		$this->res_head = stream_get_contents($this->tmp_file_head);

		return $this->res_body;

	}

}
