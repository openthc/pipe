#!/usr/bin/env php
<?php
/**
 * OpenTHC PIPE Test Runner
 *
 * SPDX-License-Identifier: MIT
 */

require_once(dirname(__DIR__) . '/boot.php');

// Default Option
if (empty($_SERVER['argv'][1])) {
	$_SERVER['argv'][1] = 'phpunit';
	$_SERVER['argc'] = count($_SERVER['argv']);
}


// Command Line
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
	'exit' => false,
	'optionsFirst' => true,
]);
$cli_args = $res->args;
if ('all' == $cli_args['<command>']) {
	$cli_args['phplint'] = true;
	$cli_args['phpstan'] = true;
	$cli_args['phpunit'] = true;
} else {
	$cmd = $cli_args['<command>'];
	$cli_args[$cmd] = true;
	unset($cli_args['<command>']);
}


// Test Config
$cfg = [];
$cfg['base'] = APP_ROOT;
$cfg['site'] = 'pipe';

$test_helper = new \OpenTHC\Test\Helper($cfg);
$cfg['output'] = $test_helper->output_path;


// PHPLint
if ($cli_args['phplint']) {
	$tc = new \OpenTHC\Test\Facade\PHPLint($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// PHPStan
if ($cli_args['phpstan']) {
	$tc = new \OpenTHC\Test\Facade\PHPStan($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// PHPUnit
if ($cli_args['phpunit']) {

	$cfg_file_list = [];
	$cfg_file_list[] = sprintf('%s/phpunit.xml', __DIR__);
	$cfg_file_list[] = sprintf('%s/phpunit.xml.dist', __DIR__);
	foreach ($cfg_file_list as $f) {
		if (is_file($f)) {
			$cfg['--configuration'] = $f;
			break;
		}
	}

	if ( ! empty($cli_args['--filter'])) {
		$cfg['--filter'] = $cli_args['--filter'];
	}

	$tc = new \OpenTHC\Test\Facade\PHPUnit($cfg);
	$res = $tc->execute();
	var_dump($res);
}


// Output
$res = $test_helper->index_create($res['data']);
echo "TEST COMPLETE\n  $res\n";
