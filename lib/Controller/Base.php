<?php
/**
 *
 */

namespace App\Controller;

class Base extends \OpenTHC\Controller\Base
{
	protected $system;

	protected $cre_base;
	protected $cre_host;

	protected $src_path;

	protected $req_path;
	protected $req_head;
	protected $req_ulid;

	function __invoke($REQ, $RES, $ARG)
	{
		$this->req_ulid = _ulid();
		$this->src_path = explode('/', $ARG['path']);
	}

}
