<?php
/**
 *
 */

namespace App\Controller;

class Base extends \OpenTHC\Controller\Base
{
	protected $cre;
	protected $cre_base;
	protected $cre_host;

	protected $src_path;

	protected $req_path;
	protected $req_head;
	protected $req_ulid;

	protected $res_info;
	protected $res_body;

	function __invoke($REQ, $RES, $ARG)
	{
		$this->req_ulid = _ulid();
		$this->src_path = explode('/', $ARG['path']);
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
