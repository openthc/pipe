<?php
/**
 * LeafData PIPE Fire Test
 */

namespace Test\B_Base;

class B_BioTrack_Test extends \Test\Base_Case
{
	function test_ping()
	{
		// Login and Keep Session

		$req = $this->_curl_init('/sync_status');
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');

		$req_body = json_encode([
			'action' => 'sync_status',
		]);

		curl_setopt($req, CURLOPT_POSTFIELDS, $req_body);

		$req_head[] = 'content-type: application/json';
		curl_setopt($req, CURLOPT_HTTPHEADER, $req_head);

		$res = curl_exec($req);
		var_dump($res);
		$res_info = curl_getinfo($req);
		$res = $this->assertValidResponse($res);

	}

	function assertValidResponse($res, $code=200, $dump=null)
	{
		$this->assertNotEmpty($res);

		$res = json_decode($res, true);
		$this->assertNotEmpty($res);
		$this->assertCount(2, $res);

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
