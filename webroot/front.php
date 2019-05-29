<?php
/**
 * OpenTHC Pipe Front Controller
 */

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Slim Application
//$cfg = array();
$cfg = array('debug' => true);
$app = new \OpenTHC\App($cfg);


// 404 Handler
$con = $app->getContainer();
$con['notFoundHandler'] = function($c) {
	return function ($REQ, $RES) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Not Found',
		), 404);
	};
};


// Authentication
$app->group('/auth', function() {

	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open');

	$this->get('/connect', 'OpenTHC\Controller\Auth\Connect');

	$this->get('/back', function($REQ, $RES, $ARG) {

		$_POST['a'] = 'auth-web';
		$_POST['cre'] = $_SESSION['cre']['engine'];
		$_POST['license'] = $_SESSION['cre-auth']['license'];
		$_POST['client-key'] = $_SESSION['cre-auth']['client-key'];

		$C = new App\Controller\Auth\Open($this);
		$RES = $C->connect($REQ, $RES, $ARG);
		return $RES;

	});

	//$this->get('/ping', 'OpenTHC\Controller\Auth\Ping');
	$this->any('/ping', function($REQ, $RES, $ARG) {
		return _from_cre_file('ping.php', $RES, $ARG);
	});

	$this->get('/shut', 'OpenTHC\Controller\Auth\Shut');

})->add('App\Middleware\Session');


/**
 * A Very Simple Object Browser
 */
$app->get('/browse', function($REQ, $RES, $ARG) {

	session_write_close();

	$data['cre_auth'] = $_SESSION['cre-auth'];
	$data['cre_meta_license'] = $_SESSION['cre-auth']['license'];

	return $this->view->render($RES, 'page/browse.html', $data);
})
->add('App\Middleware\CRE')
->add('App\Middleware\Session');


// Config/Core Data Stuff
$app->group('/config', 'App\Module\Config')
	->add('App\Middleware\CRE')
	->add('App\Middleware\Database')
	->add('App\Middleware\Session');


// Batch
$app->group('/batch', 'App\Module\Batch')
	->add('App\Middleware\CRE')
	->add('App\Middleware\Database')
	->add('App\Middleware\Session');


// Plant
$app->group('/plant', 'App\Module\Plant')
	->add('App\Middleware\CRE')
	->add('App\Middleware\Database')
	->add('App\Middleware\Session');


// Inventory Lot
$app->group('/lot', 'App\Module\Lot')
	->add('App\Middleware\CRE')
	->add('App\Middleware\Database')
	->add('App\Middleware\Session');


// QA Group
$app->group('/qa', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_cre_file('qa/search.php', $RES, $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		return _from_cre_file('qa/single.php', $RES, $ARG);
	});
	//->add(function($REQ, $RES, $ncb) {
	//
	//	// guid
	//	$ri = $REQ->getAttribute('routeInfo');
	//	$_GET['code'] = $ri[2]['guid'];
	//	$_GET['code'] = trim($_GET['code']);
	//
	//	if (empty($_GET['code'])) {
	//		return $RES->withJSON(array(
	//			'status' => 'failure',
	//			'detail' => 'QCR#283: Invalid Inventory Code',
	//			'_arg' => $_GET,
	//		), 400);
	//	}
    //
	//	$RES = $ncb($REQ, $RES);
    //
	//	return $RES;
	//});

})
->add('App\Middleware\CRE')
->add('App\Middleware\Database')
->add('App\Middleware\Session');


// Transfer Group
$app->group('/transfer', function() {

	$this->get('/outgoing', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/outgoing/search.php', $RES, $ARG);
	});

	$this->get('/outgoing/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/outgoing/single.php', $RES, $ARG);
	});

	//$this->post('/outgoing/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/transfer-accept.php', APP_ROOT, $_SESSION['cre-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	/*
		Incoming Transfers
	*/
	$this->get('/incoming', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/incoming/search.php', $RES, $ARG);
	});

	$this->get('/incoming/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/outgoing/single.php', $RES, $ARG);
	});

	$this->post('/incoming/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/incoming/accept.php', $RES, $ARG);
	});

	/*
		Rejected Transfers
	*/
	$this->get('/rejected', function($REQ, $RES, $ARG) {
		return _from_cre_file('transfer/rejected/search.php', $RES, $ARG);
	});

})
->add('App\Middleware\CRE')
->add('App\Middleware\Database')
->add('App\Middleware\Session');


// Retail Sales
$app->group('/retail', 'App\Module\Retail')
	->add('App\Middleware\CRE')
	->add('App\Middleware\Database')
	->add('App\Middleware\Session');


// Waste Group
$app->group('/waste', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_cre_file('waste/search.php', $RES, $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		return _from_cre_file('waste/single.php', $RES, $ARG);
	});

})
->add('App\Middleware\CRE')
->add('App\Middleware\Database')
->add('App\Middleware\Session');


/**
 * Stem Handlers simply log all requests/responses
 */
$app->group('/stem', 'App\Module\Stem');


// Display System Info
$app->get('/system', function($REQ, $RES, $ARG) {

	// Return a list of supported CREs
	$cre_file = sprintf('%s/etc/cre.ini', APP_ROOT);
	$cre_data = parse_ini_file($cre_file, true, INI_SCANNER_RAW);

	$cfg = array(
		'headers' => array(
			'user-agent' => 'OpenTHC/420.18.230 (Pipe-Stem-Ping)',
		),
		'http_errors' => false
	);

	$c = new \GuzzleHttp\Client($cfg);

	$req_list = array();

	foreach ($cre_data as $cre_info) {
		//var_dump($cre_info);
		$url = $cre_info['server'];
		$req_list[$url] = $c->getAsync($url);

	}

	$res_list = \GuzzleHttp\Promise\settle($req_list)->wait();

	foreach ($res_list as $key => $res) {

		//var_dump($key);
		//var_dump($res);

		echo "Connect: $key<br>";

		switch ($res['state']) {
		case 'fulfilled':

			$res = $res['value'];
			$c = $res->getStatusCode();

			//echo $res->() . "\n";

			echo "$c<br>";
			echo '<pre>';
			// var_dump($res->getHeaders());
			echo h($res->getBody());
			echo '</pre>';
			break;

		case 'rejected':
			// Problem
			break;
		}

	}

});

$app->get('/system/cre', function($REQ, $RES, $ARG) {

	// Return a list of supported CREs
	$cre_file = sprintf('%s/etc/cre.ini', APP_ROOT);
	$cre_data = parse_ini_file($cre_file, true, INI_SCANNER_RAW);

	return $RES->withJSON(array(
		'status' => 'success',
		'result' => $cre_data,
	), 200, JSON_PRETTY_PRINT);

});


// Run the App
$app->run();
