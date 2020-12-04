<?php
/**
 * OpenTHC PIPE Front Controller
 */

 // Early Error Handler
set_error_handler(function($en, $em, $ef=null, $l=null, $ec=null) {

	while (ob_get_level() > 0) { ob_end_clean(); }

	$rl = error_reporting();
	$hf = sprintf('%08x', $er & $rl);

	header('HTTP/1.1 500 Internal Error', true, 500);
	header('content-type: text/plain');
	echo "Internal Error [CWF-009]:\nLevel: $en / $rl = $hf\nMessage: $em\n";

	exit(1);

}, (E_ALL & ~ E_NOTICE));

// Early Exception Handler
set_exception_handler(function($ex) {

	while (ob_get_level() > 0) { ob_end_clean(); }

	$ex = $ex->getMessage();

	header('HTTP/1.1 500 Internal Error', true, 500);
	header('content-type: text/plain');
	echo "Internal Error [CWF-016]:\nMessage: $ex\n";

	exit(1);
});


// Bootstrap
require_once(dirname(dirname(__FILE__)) . '/boot.php');


// Slim Application
$cfg = [];
// $cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);

$con = $app->getContainer();
if (!empty($cfg['debug'])) {
	// Clears Slim Handlers
	unset($con['errorHandler']);
	unset($con['phpErrorHandler']);
	// Remove the Early ones from the stack too?
	// restore_error_handler();
	// restore_exception_handler();
}

// Engine Specific Controllers
$app->map([ 'GET', 'POST' ], '/biotrack/{system}', 'App\Controller\BioTrack')->add('OpenTHC\Middleware\Session');

$app->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{path:.*}', 'App\Controller\LeafData');

$app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{path:.*}', 'App\Controller\METRC');

// Log Access
$app->get('/log', 'App\Controller\Log');

// Engine Details
$app->get('/engines', function() {
	$out_text = [];
	$cre_list = \App\CRE::getEngineList();
	ksort($cre_list);
	foreach ($cre_list as $cre) {
		$cre['hostname'] = parse_url($cre['server'], PHP_URL_HOST);
		$out_text[] = sprintf('% 20s    %s', $cre['code'], $cre['server']);
		$out_text[] = '                        /' . $cre['engine'] . '/' . $cre['hostname'];;
		$out_text[] = '';
	}
	$out_text = implode("\n", $out_text);
	_exit_text($out_text);
});


/**
 * @deprecated
 * Legacy Path for STEM
 */
$app->group('/stem', 'App\Module\Stem'); // @deprecated


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Run the App
$app->run();
