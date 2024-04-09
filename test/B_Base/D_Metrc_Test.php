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

}
