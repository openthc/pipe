<?php
/**
 * OpenTHC PIPE Front Controller
 *
 * SPDX-License-Identifier: MIT
 */

 // Early Error Handler
set_error_handler(function($en, $em, $ef=null, $el=null, $ec=null) {

	while (ob_get_level() > 0) { ob_end_clean(); }

	header('HTTP/1.1 500 Internal Error', true, 500);
	header('content-type: text/plain');

	$msg = [];
	$msg[] = 'Internal Error [CWF-015]';
	$msg[] = sprintf('Message: %s [%d]', $em, $en);
	if (!empty($ef)) {
		$ef = substr($ef, strlen($ef) / 2); // don't show full path
		$msg[] = sprintf('File: ...%s:%d', $ef, $el);
	}

	error_log(implode('; ', $msg));

	echo implode("\n", $msg);

	exit(1);

}, (E_ALL & ~ E_NOTICE));

// Early Exception Handler
set_exception_handler(function($ex) {

	while (ob_get_level() > 0) { ob_end_clean(); }

	header('HTTP/1.1 500 Internal Error', true, 500);
	header('content-type: text/plain');

	$msg = [];
	$msg[] = 'Internal Error [CWF-039]';
	$msg[] = $ex->__toString();

	error_log(implode('; ', $msg));

	echo implode("\n", $msg);

	exit(1);
});


// Bootstrap
require_once(dirname(dirname(__FILE__)) . '/boot.php');


// Slim Application
$cfg = [];
// $cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);

$con = $app->getContainer();
// Clears Slim Handlers
unset($con['errorHandler']);
unset($con['phpErrorHandler']);

// Authentication
$app->group('/auth', 'OpenTHC\Pipe\Module\Auth')
	->add('OpenTHC\Middleware\Session');


// Engine Specific Controllers
// $app->get('/biotrack');
$app->get('/biotrack/{host}/ping', 'OpenTHC\Pipe\Controller\BioTrack:ping')
	->add('OpenTHC\Middleware\Session');

$app->map([ 'GET', 'POST' ], '/biotrack/{host}[/{path:.*}]', 'OpenTHC\Pipe\Controller\BioTrack')
	->add('OpenTHC\Middleware\Session');


// $app->get('/metrc');
$app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\Metrc');

// $app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/openthc/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\OpenTHC');

// $app->get('/qbench');
// $app->get('/qbench/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\QBench');


// Log Access
$app->map([ 'GET', 'POST' ], '/log', 'OpenTHC\Pipe\Controller\Log')
	->add('OpenTHC\Pipe\Middleware\Auth')
	->add('OpenTHC\Middleware\Session');

// Snapshot
$app->post('/log/snap', 'OpenTHC\Pipe\Controller\Log:snap')
	->add('OpenTHC\Pipe\Middleware\Auth')
	->add('OpenTHC\Middleware\Session');

// Engine Details
$app->get('/service/list', function() {
	$out_text = [];
	$cre_list = \OpenTHC\Pipe\CRE::getEngineList();
	// ksort($cre_list);
	foreach ($cre_list as $cre) {

		unset($cre['class']);
		unset($cre['epoch']);

		$cre['hostname'] = parse_url($cre['server'], PHP_URL_HOST);
		$cre_path = [];
		$cre_path[] = $cre['engine'];
		$cre_path[] = $cre['hostname'];
		$cre['path'] = implode('/', $cre_path);

		$out_text[] = sprintf('%- 10s  %s', $cre['id'], $cre['name']);
		$out_text[] = '            ' . sprintf('e:%s s:%s', $cre['engine'], $cre['server']);
		$out_text[] = '            ' . sprintf('p:%s', $cre['path']);
		$out_text[] = '            ' . json_encode($cre, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$out_text[] = '';
	}
	$out_text = implode("\n", $out_text);
	_exit_text($out_text);
});


// Run the App
$app->run();
