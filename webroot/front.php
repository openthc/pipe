<?php
/**
 * OpenTHC PIPE Front Controller
 */

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Slim Application
$cfg = [];
// $cfg['debug'] = true;
$app = new \OpenTHC\App($cfg);

$con = $app->getContainer();
if (!empty($cfg['debug'])) {
	unset($con['errorHandler']);
	unset($con['phpErrorHandler']);
}

$app->get('/log', function($REQ, $RES, $ARG) {
	return require_once(APP_ROOT . '/controller/log.php');
});

$app->map([ 'GET', 'POST' ], '/biotrack/{system}', function($REQ, $RES, $ARG) {
	return require_once(APP_ROOT . '/controller/biotrack.php');
})->add('OpenTHC\Middleware\Session');

$app->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{path:.*}', function($REQ, $RES, $ARG) {
	return require_once(APP_ROOT . '/controller/leafdata.php');
});
$app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{system}/{path:.*}', function($REQ, $RES, $ARG) {
	return require_once(APP_ROOT . '/controller/metrc.php');
});


/**
 * @deprecated
 * Stem Handlers simply log all requests/responses
 */
$app->group('/stem', 'App\Module\Stem'); // @deprecated


// Custom Middleware?
$f = sprintf('%s/Custom/boot.php', APP_ROOT);
if (is_file($f)) {
	require_once($f);
}


// Run the App
$app->run();
