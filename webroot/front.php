<?php
/**
	Front Controller - via Slim
*/

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Want to use these?
// header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
// header('Access-Control-Allow-Credentials: true');

// Slim Configuration
$cfg = array(
	'debug' => true,
	'settings' => array(
		'addContentLengthHeader' => false,
		'displayErrorDetails' => true,
	),
);

// Create App Container
$con = new \Slim\Container($cfg);


// Load Slim View
$con['view'] = function($c0) {

	$path = APP_ROOT . '/twig';
	$args = array(
		//'cache' => '/tmp',
		'debug' => true,
	);

	$view = new Slim\Views\Twig($path, $args);
	$view->addExtension(new Twig_Extension_Debug());

	$tfm = new Twig_Filter('markdown', function($x) {
		return _markdown($x);
	}, array('is_safe' => array('html')));
	$view->getEnvironment()->addFilter($tfm);

	return $view;

};


// Tell Container to use a Magic Response object
//$container['response'] = function($c0) {
//
//};


// 404 Handler
$con['notFoundHandler'] = function($c) {
	return function ($REQ, $RES) {
		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Not Found',
			'_url' => $REQ->getUri()->__toString(),
		), 404);
	};
};


$app = new \Slim\App($con);


/**
	Authentication
*/
$app->group('/auth', function() {

	$this->get('', 'App\Controller\Auth\Status');

	$this->get('/open', 'App\Controller\Auth\Open');
	$this->post('/open', 'App\Controller\Auth\Open');

	//$this->get('', 'App\Controller\Ping');
	$this->get('/ping', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/ping.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	})->add('App\Middleware\RCE');

	$this->any('/shut', 'App\Controller\Auth\Shut');

})->add('App\Middleware\Session');


/**
	A Very Simple Object Browser
*/
$app->get('/browse', function($REQ, $RES, $ARG) {
	return $this->view->render($RES, 'page/browse.html', array());
})->add('App\Middleware\RCE')->add('App\Middleware\Session');


/**
	Config Stuff
*/
$app->group('/config', function() {

	// Company
	$this->group('/company', function() {

		// GET default
		$this->any('', function($REQ, $RES, $ARG) {
			$RES = new Response_From_File();
			return $RES->execute(sprintf('%s/config/company/search.php', $_SESSION['rbe-base']), $ARG);
		});

	});

	$this->group('/license', function() {

		$this->get('', function($REQ, $RES, $ARG) {
			$RES = new Response_From_File();
			return $RES->execute(sprintf('%s/config/license/search.php', $_SESSION['rbe-base']), $ARG);
		});

		$this->get('/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/config/license/single.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		//$this->get('/company', function() {
		//	die('List Licenses by Company?');
		//});

	});

	// Contact / Users / Drivers / Employees
	$this->get('/contact', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/config/contact/search.php', $_SESSION['rbe-base']), $ARG);
	});

	//	$this->post('/{guid:[0-9a-f]+}/', function($REQ, $RES, $ARG) {
	//		require_once(APP_ROOT . '/v2016/contacts/update.php');
	//		return $RES;
	//	});

	// Products

	// Search
	$this->get('/product', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/config/product/search.php', $_SESSION['rbe-base']), $ARG);
	})
		//->add('App\Middleware\Output\CSV')
		//->add('App\Middleware\Output\CSV')
		;

	// Product Type
	$this->get('/product-type', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/config/product-type.php', $_SESSION['rbe-base']), $ARG);
	});


	// Create
	//$this->post('/product', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/config-product-create.php', APP_ROOT, $_SESSION['rbe-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	// Single
	$this->get('/product/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-single.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	// Modify
	//$this->post('/product/{guid}', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/config-product-update.php', APP_ROOT, $_SESSION['rbe-base']);
	//	$RES = require_once($f);
	//	return $RES;
	//});

	// Delete
	$this->delete('/product/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-delete.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	/*
		Strain
	*/
	// Search
	$this->get('/strain', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/config/strain/search.php', $_SESSION['rbe-base']), $ARG);
	});

	/*
		Vehicle
	*/
	// Search
	$this->get('/vehicle', function($REQ, $RES, $ARG) {
		//$f = sprintf('%s/controller/%s/vehicle/search.php', APP_ROOT, $_SESSION['rbe-base']);
		//$RES = require_once($f);
		return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
	});

	// For Areas/Rooms/Zones
	$this->get('/zone', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone/search.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});
	$this->get('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone/select.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->post('/zone', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone/create.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->post('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone/update.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->delete('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone/delete.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});


})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// Plants
$app->group('/plant', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/plant/search.php', $_SESSION['rbe-base']), $ARG);
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
$app->group('/lot', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/lot/search.php', $_SESSION['rbe-base']), $ARG);
	});

//	$this->post('/', function($REQ, $RES, $ARG) {
//		die('Create Inventory');
//	});
//
//	// Combine Inventory to a new Type
//	$this->post('/combine', function($REQ, $RES, $ARG) {
//		return $RES->withJson(array(
//			'ulid' => ULID::generate(), // '1234567890123456',
//			'weight' => 123.45,
//			'weight_unit' => 'g',
//			'quantity' => 1,
//		));
//	});
//
//	// Convert Inventory to a new Type
//	$this->post('/convert', function($REQ, $RES, $ARG) {
//		return $RES->withJson(array(
//			'code' => '123456',
//			'weight' => '',
//			'weight_unit' => 123.45,
//			'quantity' => 1,
//		));
//	})->add(function($req, $RES) {
//		// Enfore Type => Type Rules
//		//die(print_r($_POST));
//	});
//
//	// View Item
	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/lot/single.php', $_SESSION['rbe-base']), $ARG);
	});
//
//	// Update Item
//	$this->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
//		die('Update Inventory Item');
//	});
//
//	// Delete Item
//	$this->delete('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
//		// First send a 202, Pending
//		// Second send a 204, Deleted/No Content
//		die('Update Inventory Item');
//	});
//
})->add('App\Middleware\RCE')->add('App\Middleware\Session');


// QA Group
$app->group('/qa', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/qa/search.php', $_SESSION['rbe-base']), $ARG);
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
$app->group('/transfer/outgoing', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/transfer-search.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->get('/{guid:[\w\.]+}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/transfer-single.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->post('/{guid:[\w\.]+}/accept', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/transfer-accept.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

//	$this->get('/export', function($REQ, $RES, $ARG) {
//		die('Search Transfers Inbound');
//	});
//
//	$this->get('/import', function($REQ, $RES, $ARG) {
//		die('Search Transfers Outbound');
//	});
//
//	$this->get('/reject', function($REQ, $RES, $ARG) {
//		die('Search Transfers Outbound');
//	});

})->add('App\Middleware\RCE')->add('App\Middleware\Session');

$app->group('/transfer/incoming', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/transfer/incoming/search.php', $_SESSION['rbe-base']), $ARG);
	});

});



// Retail Sales
$app->group('/sale', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/retail/search.php', $_SESSION['rbe-base']), $ARG);
	});

	$this->post('', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/retail/create.php', $_SESSION['rbe-base']), $ARG);
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
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/waste/search.php', $_SESSION['rbe-base']), $ARG);
	});

	$this->get('/{guid}', function($REQ, $RES, $ARG) {
		$RES = new Response_From_File();
		return $RES->execute(sprintf('%s/waste/single.php', $_SESSION['rbe-base']), $ARG);
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
