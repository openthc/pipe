<?php
/**
 *
 * SPDX-License-Identifier: MIT
 *
 * Notes about the Auth module
 * The "program-key" cooresponds to a code that is a company object identifier
 * The "license-key" cooresponds to a code that is a license object identifier
 *
 * Licenses can belong to a company in a 1:M way
 * Companies can have different permissions to act on a license's object
 *
 */

namespace OpenTHC\Pipe\Test\CRE;

class Ping_Test extends \OpenTHC\Pipe\Test\Base
{
	/**
	 *
	 */
	// public function test_ping_engine()
	// {
	// 	$engine_list = [
	// 		'biotrack',
	// 		'leafdata',
	// 		'metrc',
	// 	];

	// 	foreach ($engine_list as $engine) {

	// 		$url = sprintf('%s/%s/ping', OPENTHC_TEST_ORIGIN, $engine);
	// 		$req = _curl_init($url);
	// 		$res = curl_exec($req);
	// 		$inf = curl_getinfo($req);
	// 		curl_close($req);

	// 		$this->assertEquals(200, $inf['http_code']);
	// 		$this->assertNotEmpty($res);

	// 	}

	// 	// foreach ($cre_list as $cre_conf) {
	// 	// 	// print_r($cre_conf);
	// 	// 	// $cre_conf['service-key'] = 'TEST_SERVICE_KEY';
	// 	// 	// $cre_conf['license-key'] = 'TEST_LICENSE_KEY';
	// 	// 	// $cre = \OpenTHC\Pipe\CRE::factory($cre_conf);
	// 	// 	// $this->assertNotEmpty($cre);
	// 	// 	// $this->assertTrue($cre instanceof \OpenTHC\CRE\Base);
	// 	// }
	// }

	/**
	 *
	 */
	public function test_ping_cre()
	{
		$cre_list = \OpenTHC\CRE::getEngineList();
		$this->assertCount(99, $cre_list);

		foreach ($cre_list as $cre) {

			$cre_pipe = trim(OPENTHC_TEST_ORIGIN, '/');
			$cre_path = parse_url($cre['server'], PHP_URL_HOST);

			$url = sprintf('%s/%s/%s/ping', $cre_pipe, $cre['engine'], $cre_path);

			$req = _curl_init($url);
			$res = curl_exec($req);

			$inf = curl_getinfo($req);
			curl_close($req);

			$this->assertEquals(200, $inf['http_code'], sprintf('Failed Fetching %s', $url));
			$this->assertNotEmpty($res);
			$res = json_decode($res, true);
			$this->assertIsArray($res);
			$this->assertCount(2, $res);
			$this->assertIsArray($res['data']);
			// $this->assertNotEmpty($res['data']['cre']);
			// $this->assertNotEmpty($res['data']['cre_base']);
			// $this->assertNotEmpty($res['meta']['detail']);
			// $this->assertNotEmpty($res['meta']['source']);

		}
	}
}
