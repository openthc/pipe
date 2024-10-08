<?php
/**
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\Core;

class Config_Test extends \OpenTHC\Pipe\Test\Base
{
	/**
	 *
	 */
	protected function setUp() : void
	{
		// Nothing
	}

	/**
	 *
	 */
	function test_env()
	{
		$x = defined('OPENTHC_TEST_ORIGIN');
		$this->assertNotEmpty($x);
	}

	/**
	 *
	 */
	function test_tz()
	{
		$x = \OpenTHC\Config::get('tz');
		$this->assertNotEmpty($x);
	}

}
