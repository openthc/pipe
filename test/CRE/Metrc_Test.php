<?php
/**
 * Metrc PIPE Fire Test
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\CRE;

class Metrc_Test extends \OpenTHC\Pipe\Test\Base
{

	/**
	 *
	 */
	protected function setUp() : void
	{
		parent::setup();
		$this->_cre = \OpenTHC\CRE::getEngine('usa/ma');
	}

	/**
	 *
	 */
	function test_ping()
	{
		// $cre_pipe = trim(OPENTHC_TEST_ORIGIN, '/');
		$cre_host = parse_url($this->_cre['server'], PHP_URL_HOST);

		$res = $this->httpClient->get(sprintf('/metrc/%s/ping', $cre_host));
		$res = $this->assertValidResponse($res);
		// var_dump($res);
	}

}
