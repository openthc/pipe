<?php
/**
 * Notes about the Auth module
 * The "program-key" cooresponds to a code that is a company object identifier
 * The "license-key" cooresponds to a code that is a license object identifier
 *
 * Licenses can belong to a company in a 1:M way
 * Companies can have different permissions to act on a license's object
 *
 */

namespace Test\A_Core;

class A_CRE_Ping_Test extends \Test\Base_Case
{
	public function test_ping_engine()
	{
		$engine_list = [
			'biotrack',
			'leafdata',
			'metrc',
		];

		foreach ($engine_list as $engine) {

			$url = sprintf('https://%s/%s/ping', getenv('OPENTHC_TEST_HOST'), $engine);
			$req = _curl_init($url);
			$res = curl_exec($req);
			$inf = curl_getinfo($req);
			curl_close($req);

			$this->assertEquals(200, $inf['http_code']);
			$this->assertNotEmpty($res);

		}

		// $cre_list = \OpenTHC\CRE\Base::getEngineList();
		// $this->assertCount(20, $cre_list);

		// foreach ($cre_list as $cre_conf) {
		// 	// print_r($cre_conf);
		// 	// $cre_conf['service-key'] = 'TEST_SERVICE_KEY';
		// 	// $cre_conf['license-key'] = 'TEST_LICENSE_KEY';
		// 	// $cre = \App\CRE::factory($cre_conf);
		// 	// $this->assertNotEmpty($cre);
		// 	// $this->assertTrue($cre instanceof \OpenTHC\CRE\Base);
		// }
	}

	public function test_ping_cre()
	{
		$cre_list = [
			'biotrack/hi',
			'biotrack/il',
			'leafdata/wa',
			'leafdata/wa/test',
			'metrc/ak',
			'metrc/ca',
			'metrc/co',
			'metrc/la',
			'metrc/ma',
			'metrc/md',
			'metrc/me',
			'metrc/mi',
			'metrc/mo',
			'metrc/nv',
			'metrc/or',
		];

		foreach ($cre_list as $cre) {

			$url = sprintf('https://%s/%s/ping', getenv('OPENTHC_TEST_HOST'), $cre);
			$req = _curl_init($url);
			$res = curl_exec($req);
			// var_dump($res);

			$inf = curl_getinfo($req);
			curl_close($req);

			$this->assertEquals(200, $inf['http_code']);
			$this->assertNotEmpty($res);
			$res = json_decode($res, true);
			$this->assertIsArray($res);
			$this->assertCount(2, $res);
			$this->assertIsArray($res['meta']);
			$this->assertNotEmpty($res['meta']['detail']);
			$this->assertNotEmpty($res['meta']['source']);
			$this->assertEquals('openthc', $res['meta']['source']);
			$this->assertNotEmpty($res['meta']['cre']);
			$this->assertNotEmpty($res['meta']['cre_base']);

		}
	}
}
