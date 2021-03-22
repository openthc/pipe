<?php
/**
 * LeafData PIPE Fire Test
 */

namespace Test\B_Base;

class B_BioTrack_Test extends \Test\Base_Case
{
	function test_shit()
	{
		return(null);
	}

	function assertValidResponse($res)
	{
		$this->assertNotEmpty($res);
		$res = json_decode($res, true);
		$this->assertNotEmpty($res);
		$this->assertCount(9, $res);

		$this->assertArrayHasKey('current_page', $res);
		$this->assertArrayHasKey('data', $res);
		$this->assertArrayHasKey('from', $res);
		$this->assertArrayHasKey('last_page', $res);
		$this->assertArrayHasKey('next_page_url', $res);
		$this->assertArrayHasKey('per_page', $res);
		$this->assertArrayHasKey('prev_page_url', $res);
		$this->assertArrayHasKey('to', $res);
		$this->assertArrayHasKey('total', $res);

		$ret = $res['data'];
		$this->assertIsArray($ret);

		return $ret;
	}

	/**
	 *
	 */
	function _curl_init($path)
	{
		$path = ltrim($path, '/');

		$url = sprintf('https://%s/biotrack/wa/test/%s', getenv('OPENTHC_TEST_HOST'), $path);
		$req = _curl_init($url);

		$head = [
			'content-type: application/json',
		];

		curl_setopt($req, CURLOPT_HTTPHEADER, $head);

		return $req;

	}

}
