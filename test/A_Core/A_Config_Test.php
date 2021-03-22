<?php
/**
 */

namespace Test\A_Core;

class A_Config_Test extends \Test\Base_Case
{
	function test_env()
	{
		$x = getenv('OPENTHC_TEST_HOST');
		$this->assertNotEmpty($x);
	}

	/**
	 */
	function test_psk()
	{
		$x = \OpenTHC\Config::get('psk');
		$this->assertNotEmpty($x);
	}

	/**
	 */
	function test_tz()
	{
		$x = \OpenTHC\Config::get('tz');
		$this->assertNotEmpty($x);
	}

}
