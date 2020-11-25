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

namespace Test\Core;

class A_CRE_Ping_Test extends \Test\OpenTHC_Test_Case_Base
{
	public function test_load_cre()
	{
		$cre_list = \App\CRE::getEngineList();
		$this->assertCount(21, $cre_list);

		foreach ($cre_list as $cre_conf) {
			// print_r($cre_conf);
			$cre_conf['service-key'] = 'TEST_SERVICE_KEY';
			$cre_conf['license-key'] = 'TEST_LICENSE_KEY';
			$cre = \App\CRE::factory($cre_conf);
			$this->assertNotEmpty($cre);
			$this->assertTrue($cre instanceof \OpenTHC\CRE\Base);
		}
	}

	public function test_ping_cre()
	{
		$cre_list = \App\CRE::getEngineList();
		foreach ($cre_list as $cre_conf) {
			$cre_conf['service-key'] = 'TEST_SERVICE_KEY';
			$cre_conf['license-key'] = 'TEST_LICENSE_KEY';
			$cre = \App\CRE::factory($cre_conf);
			$res = $cre->ping();
			$this->assertIsArray($res);
			$this->assertCount(3, $res);
			$this->assertArrayHasKey('code', $res);
			$this->assertArrayHasKey('data', $res);
			$this->assertArrayHasKey('meta', $res);
		}
	}

}
