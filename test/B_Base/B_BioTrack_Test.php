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
		$this->_pipe_base = trim(getenv('OPENTHC_TEST_BASE'), '/');
	}

	/**
	 *
	 */
	function test_ping()
	{
		// Login and Keep Session
		// $req = $this->_curl_init($this->_pipe_base . '/biotrack/bunk/serverjson.asp');
		// $res = [];
		// $res['body'] = curl_exec($req);
		// $res['info'] = curl_getinfo($req);
		// $res = $this->assertValidResponse($res);
	}

	/**
	 *
	 */
	function assertValidResponse($res, $code=200, $dump=null) : array
	{
		$this->raw = $res['body'];

		$hrc = $res['info']['http_code'];

		if (empty($dump)) {
			if ($code != $hrc) {
				$dump = "HTTP $hrc != $code";
			}
		}

		if (!empty($dump)) {
			echo "\n<<< $dump <<< $hrc <<<\n{$this->raw}\n###\n";
		}

		$this->assertEquals($code, $hrc);
		$type = $res['info']['content_type'];
		$type = strtok($type, ';');
		$this->assertEquals('application/json', $type);

		$ret = \json_decode($this->raw, true);

		$this->assertIsArray($ret);
		$this->assertArrayHasKey('data', $ret);
		$this->assertArrayHasKey('meta', $ret);

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
