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

// Engine Specific Controllers
// $app->get('/biotrack');
$app->map([ 'GET', 'POST' ], '/biotrack/{host}[/{path:.*}]', 'OpenTHC\Pipe\Controller\BioTrack')
	->add('OpenTHC\Middleware\Session');

// $app->get('/leafdata');
$app->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\LeafData');

// $app->get('/metrc');
$app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\Metrc');

// $app->get('/qbench');
// $app->get('/qbench/{host}/{path:.*}', 'OpenTHC\Pipe\Controller\QBench');


// Log Access
$app->map([ 'GET', 'POST' ], '/log', 'OpenTHC\Pipe\Controller\Log')
	->add('OpenTHC\Middleware\Session');

// Engine Details
$app->get('/service/list', function() {
	$out_text = [];
	$cre_list = \OpenTHC\Pipe\CRE::getEngineList();
	// ksort($cre_list);
	foreach ($cre_list as $cre) {
		$cre['hostname'] = parse_url($cre['server'], PHP_URL_HOST);
		$out_text[] = sprintf('% 20s    /%s/%s', $cre['code'], $cre['engine'], $cre['hostname']);
		$out_text[] = '                        ' . $cre['server'];
		$out_text[] = '';
	}
	$out_text = implode("\n", $out_text);
	_exit_text($out_text);
});


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Run the App
$app->run();
