<?php
/**
 * BioTrack PIPE Fire Test
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\CRE;

class BioTrack_Test extends \OpenTHC\Pipe\Test\Base
{

	/**
	 *
	 */
	protected function setUp() : void
	{
		parent::setUp();
		$this->_cre = \OpenTHC\CRE::getConfig('usa/nm');
		// 'openthc/biotrack/bunk');
	}

	/**
	 *
	 */
	function test_ping()
	{
		$this->assertNotEmpty($this->_cre);
		$this->assertNotEmpty($this->_cre['server']);

		$cre_host = parse_url($this->_cre['server'], PHP_URL_HOST);

		$url = sprintf('/biotrack/%s/serverjson.asp', $cre_host);
		$res = $this->httpClient->get($url);
		$this->assertNotEmpty($res);
	}

}
