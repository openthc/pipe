#!/usr/bin/php
<?php
/**
 * OpenTHC PIPE Test Runner
 */

namespace OpenTHC\Test;

require_once(__DIR__ . '/boot.php');

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
	echo "RUn PHP Lnk\n";
	// phplint_exec($arg);
}


// PHPStan
if ($arg['phpstan']) {
	echo "RUn PHPStan\n";
	// phpstan_exec($arg);
}

// PHPUnit
if ($arg['phpunit']) {
	phpunit_exec($arg);
}
