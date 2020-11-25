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
	// exit;
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
