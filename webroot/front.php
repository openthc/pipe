<?php
/**
	Front Controller - via Slim
*/

require_once(dirname(dirname(__FILE__)) . '/boot.php');


// Slim Configuration
$cfg = array();
//$cfg = array('debug' => true);
$app = new \OpenTHC\App($cfg);

// Tell Container to use a Magic Response object
//$container['response'] = function($c0) {
//
//};


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


/**
	Authentication
*/
$app->group('/auth', function() {

	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open');

	$this->get('/ping', function($REQ, $RES, $ARG) {
		return _from_rce_file('ping.php', $RES, $ARG);
	})->add('App\Middleware\RCE');

	$this->any('/shut', 'App\Controller\Auth\Shut');

})->add('App\Middleware\Session');


/**
	A Very Simple Object Browser
*/
$app->get('/browse', function($REQ, $RES, $ARG) {

	$data = array();
	$data['rce_auth'] = $_SESSION['rce-auth'];
	$data['rce_meta_license'] = $_SESSION['rce-auth']['license'];

	return $this->view->render($RES, 'page/browse.html', $data);

})
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


/**
	Config Stuff
*/
$app->group('/config', 'App\Module\Config')
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


// Plants
$app->group('/plant', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('plant/search.php', $RES, $ARG);
	});

	//$this->post('', function($REQ, $RES, $ARG) {
	//	die('Create Plants');
	//});

	$this->get('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		return _from_rce_file('plant/single.php', $RES, $ARG);
	});

	$this->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		return _from_rce_file('plant/update.php', $RES, $ARG);
	});

	//$this->post('/{guid:[0-9a-f]+}/collect', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/plants-get.php', APP_ROOT, $_SESSION['rce-base']);
	//	require_once($f);
	//	return $RES;
	//});

})
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


// Inventory Group
$app->group('/lot', 'App\Module\Lot')
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


// QA Group
$app->group('/qa', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('qa/search.php', $RES, $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		return _from_rce_file('qa/single.php', $RES, $ARG);
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
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


// Transfer Group
$app->group('/transfer', function() {

	$this->get('/outgoing', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/outgoing/search.php', $RES, $ARG);
	});

	$this->get('/outgoing/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/outgoing/single.php', $RES, $ARG);
	});

	//$this->post('/outgoing/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/transfer-accept.php', APP_ROOT, $_SESSION['rce-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	/*
		Incoming Transfers
	*/
	$this->get('/incoming', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/incoming/search.php', $RES, $ARG);
	});

	$this->get('/incoming/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/outgoing/single.php', $RES, $ARG);
	});

	$this->post('/incoming/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/incoming/accept.php', $RES, $ARG);
	});

	/*
		Rejected Transfers
	*/
	$this->get('/rejected', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/rejected/search.php', $RES, $ARG);
	});

})
->add('App\Middleware\RCE')
->add('App\Middleware\Session');


// Retail Sales
$app->group('/sale', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('retail/search.php', $RES, $ARG);
	});

	$this->post('', function($REQ, $RES, $ARG) {
		return _from_rce_file('retail/create.php', $RES, $ARG);
	});

	$this->get('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		print_r($_POST);
		return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
	});

	$this->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		print_r($_POST);
		return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
	});

	$this->delete('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
	});

})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Waste Group
$app->group('/waste', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('waste/search.php', $RES, $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		return _from_rce_file('waste/single.php', $RES, $ARG);
	});

})->add('App\Middleware\RCE')->add('App\Middleware\Session');


/**
	Stem Handlers simply log all requests/responses
*/
$app->group('/stem', 'App\Module\Stem');


// Display System Info
$app->get('/system', function($REQ, $RES, $ARG) {

	// Return a list of supported RCEs
	$rce_file = sprintf('%s/etc/rce.ini', APP_ROOT);
	$rce_data = parse_ini_file($rce_file, true, INI_SCANNER_RAW);

	$cfg = array(
		'headers' => array(
			'user-agent' => 'OpenTHC/420.18.230 (Pipe-Stem-Ping)',
		),
		'http_errors' => false
	);

	$c = new \GuzzleHttp\Client($cfg);

	$req_list = array();

	foreach ($rce_data as $rce_info) {
		//var_dump($rce_info);
		$url = $rce_info['server'];
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

$app->get('/system/rce', function($REQ, $RES, $ARG) {

	// Return a list of supported RCEs
	$rce_file = sprintf('%s/etc/rce.ini', APP_ROOT);
	$rce_data = parse_ini_file($rce_file, true, INI_SCANNER_RAW);

	return $RES->withJSON(array(
		'status' => 'success',
		'result' => $rce_data,
	), 200, JSON_PRETTY_PRINT);

});


// Run the App
$app->run();
