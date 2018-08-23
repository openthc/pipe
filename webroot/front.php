<?php
/**
	Front Controller - via Slim
*/

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Want to use these?
// header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
// header('Access-Control-Allow-Credentials: true');

// Slim Configuration
$app = new \OpenTHC\App(array('debug' => true));

//// Tell Container to use a Magic Response object
////$container['response'] = function($c0) {
////
////};


// 404 Handler
$con = $app->getContainer();
$con['notFoundHandler'] = function($c) {
	return function ($REQ, $RES) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Not Found',
			'_url' => $REQ->getUri()->__toString(),
		), 404);
	};
};


/**
	Authentication
*/
$app->group('/auth', function() {

	$this->get('', 'App\Controller\Auth\Status');

	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open');

	//$this->get('', 'App\Controller\Ping');
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
	$data['rbe_auth'] = $_SESSION['rbe-auth'];
	$data['rbe_meta_license'] = $_SESSION['rbe-auth']['license'];

	return $this->view->render($RES, 'page/browse.html', $data);
})->add('App\Middleware\RCE')->add('App\Middleware\Session');


/**
	Config Stuff
*/
$app->group('/config', 'App\Module\Config')->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Plants
$app->group('/plant', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('plant/search.php', $RES, $ARG);
	});

	//$this->post('', function($REQ, $RES, $ARG) {
	//	die('Create Plants');
	//});

	$this->get('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/plant/single.php', $_SESSION['rbe-base']), $ARG);
	});

	$this->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/plant/update.php', $_SESSION['rbe-base']), $ARG);
	});

	//$this->post('/{guid:[0-9a-f]+}/collect', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/plants-get.php', APP_ROOT, $_SESSION['rbe-base']);
	//	require_once($f);
	//	return $RES;
	//});

})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Inventory Group
$app->group('/lot', 'App\Module\Lot')->add('App\Middleware\RCE')->add('App\Middleware\Session');


// QA Group
$app->group('/qa', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return _from_rce_file('qa/search.php', $RES, $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/qa/single.php', $_SESSION['rbe-base']), $ARG);
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

})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Transfer Group
$app->group('/transfer', function() {

	$this->get('/outgoing', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/outgoing/search.php', $RES, $ARG);
	});

	//$this->get('/outgoing/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/transfer-single.php', APP_ROOT, $_SESSION['rbe-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	//$this->post('/outgoing/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/transfer-accept.php', APP_ROOT, $_SESSION['rbe-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	/*
		Incoming Transfers
	*/
	$this->get('/incoming', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/incoming/search.php', $RES, $ARG);
	});


	/*
		Rejected Transfers
	*/
	$this->get('/rejected', function($REQ, $RES, $ARG) {
		return _from_rce_file('transfer/rejected/search.php', $RES, $ARG);
	});

})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Retail Sales
$app->group('/sale', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/retail/search.php', $_SESSION['rbe-base']), $ARG);
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
$app->group('/stem', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		return $this->view->render($RES, 'page/stem.html', array());
	});

	$this->post('/biotrack', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/biotrack.php');
	});

	$this->map([ 'GET', 'POST' ], '/leafdata/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/leafdata.php');
	});

	$this->map([ 'GET', 'POST' ], '/metrc/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/metrc.php');
	});

})
//->add('App\Middleware\Log\HTTP')
;


// Run the App
$app->run();
