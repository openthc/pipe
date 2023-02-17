<?php
/**
 * Metrc PIPE Fire Test
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\B_Base;

class D_Metrc_Test extends \OpenTHC\Pipe\Test\Base_Case
{

	/**
	 *
	 */
	protected function setUp() : void
	{
		$this->_cre = \OpenTHC\CRE::getEngine('openthc/metrc/bunk');
	}

	/**
	 *
	 */
	function test_ping()
	{
		$req = $this->_curl_init('/uom');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);
		$res = $this->assertValidResponse($res);
		var_dump($res);
	}

	/**
	 *
	 */
	function assertValidResponse($res, $code=200, $dump=null) : array
	{
		$this->assertNotEmpty($res);
		// $res = json_decode($res, true);
		// $this->assertNotEmpty($res);
		// $this->assertCount(9, $res);

		// $this->assertArrayHasKey('current_page', $res);
		// $this->assertArrayHasKey('data', $res);
		// $this->assertArrayHasKey('from', $res);
		// $this->assertArrayHasKey('last_page', $res);
		// $this->assertArrayHasKey('next_page_url', $res);
		// $this->assertArrayHasKey('per_page', $res);
		// $this->assertArrayHasKey('prev_page_url', $res);
		// $this->assertArrayHasKey('to', $res);
		// $this->assertArrayHasKey('total', $res);

		// $ret = $res['data'];
		// $this->assertIsArray($ret);

		// return $ret;
	}

	/**
	 *
	 */
	function _curl_init($path)
	{
		$base = rtrim($this->_cre['server'], '/');
		$path = ltrim($path, '/');

		$url = sprintf('%s/%s', $base, $path);
		$req = _curl_init($url);

		$head = [
			'content-type: application/json',
		];

		curl_setopt($req, CURLOPT_HTTPHEADER, $head);

		return $req;

	}

}
