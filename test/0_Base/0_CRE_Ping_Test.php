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

namespace Test\Base;

class CRE_Ping extends \Test\OpenTHC_Test_Case_Base
{
	public function test_ping_cre()
	{
		$cre_list = \CRE::getEngineList();
		$this->assertCount(28, $cre_list);

		foreach ($cre_list as $cre_conf) {

			print_r($cre_conf);

			$cre = \CRE::factory($cre_conf);

			echo get_class($cre) . "\n";

			$res = $cre->ping();

		}
	}

}
