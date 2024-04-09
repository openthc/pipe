<?php
/**
 * BioTrack PIPE Fire Test
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\B_Base;

class B_BioTrack_Test extends \OpenTHC\Pipe\Test\Base_Case
{

	/**
	 *
	 */
	protected function setUp() : void
	{
		$this->_cre = \OpenTHC\CRE::getEngine('openthc/biotrack/bunk');
		var_dump($this->_cre);
	}

	/**
	 *
	 */
	function test_ping()
	{

		$this->assertNotEmpty($this->_cre);
		$this->assertNotEmpty($this->_cre['host']);

		$url = sprintf('/biotrack/%s/serverjson.asp', $this->_cre['host']);
		$res = $this->httpClient->get($url);
		$this->assertNotEmpty($res);
	}

	/**
	 *
	 */
	function assertValidResponse($res, $code_expect=200, $type_expect='application/json', $dump=null) : array
	{
		$ret = parent::assertValidResponse($res, $code_expect, $type_expect, $dump);

		switch ($type_expect) {
		case 'application/json':
			$this->assertIsArray($ret);
			$this->assertArrayHasKey('data', $ret);
			$this->assertArrayHasKey('meta', $ret);
			break;
		}

		return $ret;
	}

	/**
	 *
	 */
	function _curl_init($path)
	{
		$base = rtrim($this->_cre['server'], '/');
		$path = ltrim($path, '/');

		$url = sprintf('%s/%s', $base , $path);
		$req = _curl_init($url);

		$head = [
			'content-type: application/json',
		];

		curl_setopt($req, CURLOPT_HTTPHEADER, $head);

		return $req;

	}

}
