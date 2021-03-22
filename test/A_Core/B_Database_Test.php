<?php
/**
 */

namespace Test\A_Core;

class B_Database_Test extends \Test\Base_Case
{
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
