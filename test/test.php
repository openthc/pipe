#!/usr/bin/php
<?php
/**
 * OpenTHC PIPE Test Runner
 *
 * SPDX-License-Identifier: MIT
 */

require_once(dirname(__DIR__) . '/boot.php');

$doc = <<<DOC
OpenTHC PIPE Test Runner

Usage:
	test <command> [<command-options>...]

Commands:
	all       run all tests
	phplint   run some tests
	phpunit
	phpstan

Options:
	--phpunit-config=FILE      File to use for PHPUnit XML Configuration
	--phpunit-filter=FILTER    Filter to pass to PHPUnit
DOC;

$res = \Docopt::handle($doc, [
	'help' => true,
	'optionsFirst' => true,
]);
$arg = $res->args;
var_dump($arg);
if ('all' == $arg['<command>']) {
	$arg['phplint'] = true;
	$arg['phpstan'] = true;
	$arg['phpunit'] = true;
} else {
	$cmd = $arg['<command>'];
	$arg[$cmd] = true;
	unset($arg['<command>']);
}
var_dump($arg);

$dt0 = new \DateTime();

define('OPENTHC_TEST_OUTPUT_BASE', \OpenTHC\Test\Helper::output_path_init());


// PHPLint
if ($arg['phplint']) {
	$tc = new \OpenTHC\Test\Facade\PHPLint([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	$res = $tc->execute();
	var_dump($res);
}


// PHPStan
if ($arg['phpstan']) {
	$tc = new OpenTHC\Test\Facade\PHPStan([
		'output' => OPENTHC_TEST_OUTPUT_BASE
	]);
	$res = $tc->execute();
	var_dump($res);
}


// PHPUnit
if ($arg['phpunit']) {

	$cfg = [
		'output' => OPENTHC_TEST_OUTPUT_BASE
	];

	if ( ! empty($cli_args['--filter'])) {
		$cfg['--filter'] = $cli_args['--filter'];
	}

	$tc = new OpenTHC\Test\Facade\PHPUnit($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// Done
\OpenTHC\Test\Helper::index_create('');


// Output Information
$origin = \OpenTHC\Config::get('openthc/pipe/origin');
$output = str_replace(sprintf('%s/webroot/', APP_ROOT), '', OPENTHC_TEST_OUTPUT_BASE);

echo "TEST COMPLETE\n  $origin/$output\n";
