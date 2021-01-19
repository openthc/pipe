<?php
/**
 */

class A_Config_Test extends
{
	/**
	 */
	function test_psk()
	{
		$f = sprintf('%s/etc/psk', APP_ROOT);
		$this->assertTrue(is_file($f));
	}

	/**
	 */
	function test_tz()
	{
		$f = sprintf('%s/etc/tz', APP_ROOT);
		$this->assertTrue(is_file($f));
	}

}
