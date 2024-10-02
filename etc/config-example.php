<?php
/**
 * OpenTHC PIPE Configuration Example
 */

// Init
$cfg = [];

// Base
$cfg = [
	'tz' => 'America/Los_Angeles',
];

// Database
$cfg['database'] = [
	'hostname' => '127.0.0.1',
	'username' => 'openthc_pipe',
	'password' => 'openthc_pipe',
	'database' => 'openthc_pipe',
];

$cfg['openthc'] = [
	'app' => [],
	'pipe' => [
		'id' => '/* PIPE SERVICE ULID */',
		'origin' => 'https://pipe.openthc.example.com',
		'public' => '/* PIPE SERVICE PUBLIC KEY */',
		'secret' => '/* PIPE SERVICE SECRET KEY */',
	],
	'sso' => [
		'id' => '',
		'origin' => 'https://sso.openthc.example.com',
		'public' => '/* SSO SERVICE PUBLIC KEY */',
		'context' => '',
		'client-id' => '010PENTHCX0000SVC00000P1PE',
		'client-pk' => '/* PIPE SERVICE PUBLIC KEY */',
		'client-sk' => '/* PIPE SERVICE SECRET KEY */',
	]
];

return $cfg;
