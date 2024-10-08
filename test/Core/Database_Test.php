<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test\Core;

class Database_Test extends \OpenTHC\Pipe\Test\Base
{
	/**
	 *
	 */
	function test_database()
	{
		$cfg = \OpenTHC\Config::get('database');
		$this->assertNotEmpty($cfg['hostname']);
		$this->assertNotEmpty($cfg['username']);
		$this->assertNotEmpty($cfg['password']);
		$this->assertNotEmpty($cfg['database']);
	}
}


// TEST <TABLE></TABLE>

// INSERT OK, UPDATE OK, SELECT OK -- DELETE NOT OK

// SU TO ROOT USER THEN CLEWANUP

// CHECK FOR BOTH ROOT and REGULAR USER
