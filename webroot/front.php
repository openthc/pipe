<?php
/**
	Front Controller - via Slim
*/

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Want to use these?
// header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
// header('Access-Control-Allow-Credentials: true');


// Create App Container
$con = new \Slim\Container(array(
	'debug' => true,
	'settings' => array(
		'addContentLengthHeader' => false,
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
	),
));

$con['view'] = function($c0) {

	$path = APP_ROOT . '/twig';
	$args = array(
		//'cache' => '/tmp',
		'debug' => true,
	);

	$view = new Slim\Views\Twig($path, $args);
	$view->addExtension(new Twig_Extension_Debug());

	$view->getEnvironment()->addFilter(new Twig_SimpleFilter('markdown', function($x) {
		return _markdown($x);
	}));

	return $view;

};

// Tell Container to use a Magic Response object
//$container['response'] = function($c0) {
//
//};

// 404 Handler
//$con['notFoundHandler'] = function($c) {
//	return function ($REQ, $RES) {
//		return $RES->withJSON(array(
//			'status' => 'failure',
//			'detail' => 'Not Found',
//			'_url' => $REQ->getUri()->__toString(),
//		), 404);
//	};
//};
//
//$con['errorHandler'] = function($c) {
//	return function ($REQ, $RES) {
//		die("\nerrorHandler\n");
//		//return $RES->withJSON(array(
//		//	'status' => 'failure',
//		//	'detail' => 'Not Found',
//		//), 404);
//	};
//};
//
//$con['phpErrorHandler'] = function($c) {
//	return function ($REQ, $RES) {
//		die("\nphpErrorHandler\n");
//		//return $RES->withJSON(array(
//		//	'status' => 'failure',
//		//	'detail' => 'Not Found',
//		//), 404);
//	};
//};

//unset($con['errorHandler']);
//unset($con['phpErrorHandler']);

$app = new \Slim\App($con);

// Authentication
$app->group('/auth', function() {

	$this->get('', 'Pipe\\Controller\\Auth\\Status');

	$this->get('/open', 'Pipe\\Controller\\Auth\\Open');
	$this->post('/open', 'Pipe\\Controller\\Auth\\Open');

	//$this->get('', 'Pipe\\Controller\\Ping');
	$this->get('/ping', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/ping.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	})->add('Middleware_RCE');

	$this->any('/shut', 'Pipe\\Controller\\Auth\\Shut');

})->add('Middleware_Session');

//// Home Request
//$app->get('/test', function($REQ, $RES, $ARG) {

//});
//
//$app->post('/test', function($REQ, $RES, $ARG) {
//	require_once(APP_ROOT . '/view/test-eval.php');
//});

$app->get('/browse', function($REQ, $RES, $ARG) {
	return $this->view->render($RES, 'page/browse.html', array());
})->add('Middleware_RCE')->add('Middleware_Session');

/**
	Config Stuff
*/
$app->group('/config', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$this->view->render($RES, 'page/config.html');
	});

	$this->group('/license', function() {

		$this->get('', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/config/license/search.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		//$this->get('/company', function() {
		//	die('List Licenses by Company?');
		//});

	});


	// Search
	$this->get('/product', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-search.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	})
		//->add('Pipe\\Middleware\\Output\CSV')
		//->add('Pipe\\Middleware\\Output\CSV')
		;

	// Single
	$this->get('/product/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-single.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	// Create
	$this->post('/product', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-create.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	// Modify
	$this->post('/product/{ulid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-update.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	// Delete
	$this->delete('/product/type/{ulid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/config-product-delete.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	// For Areas/Rooms/Zones
	$this->get('/zone', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone-select.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});
	$this->get('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone-select.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->post('/zone', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone-insert.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->post('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone-update.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});

	$this->delete('/zone/{guid}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/zone-delete.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});


})->add('Middleware_RCE')->add('Middleware_Session');

// Company
$app->group('/config/company', function() {

	// GET default
	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/company-get.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	})->add(function($req, $RES, $ncb) {
		//$app->
		$RES = $ncb($req, $RES);
		return $RES;
	});

})->add('Middleware_RCE')->add('Middleware_Session');


//
// Contact / Users / Employees
$app->group('/config/contacts', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/contact-get.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

//	$this->post('/{ulid:[0-9a-f]+}/', function($REQ, $RES, $ARG) {
//		require_once(APP_ROOT . '/v2016/contacts/update.php');
//		return $RES;
//	});

})->add('Middleware_RCE')->add('Middleware_Session');

// Plants
$app->group('/plant', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/plant-select.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	//$this->post('', function($REQ, $RES, $ARG) {
	//	die('Create Plants');
	//});

	$this->get('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/plant-single.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	$this->post('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/plant-update.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	//$this->post('/{ulid:[0-9a-f]+}/collect', function($REQ, $RES, $ARG) {
	//	$f = sprintf('%s/controller/%s/plants-get.php', APP_ROOT, $_SESSION['rbe-base']);
	//	require_once($f);
	//	return $RES;
	//});

})->add('Middleware_RCE')->add('Middleware_Session');


// Inventory Group
$app->group('/lot', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/lot/search.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
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
		$f = sprintf('%s/controller/%s/lot/single.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	});
//
//	// Update Item
//	$this->post('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
//		die('Update Inventory Item');
//	});
//
//	// Delete Item
//	$this->delete('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
//		// First send a 202, Pending
//		// Second send a 204, Deleted/No Content
//		die('Update Inventory Item');
//	});
//
})->add('Middleware_RCE')->add('Middleware_Session');


// QA Group
$app->group('/qa', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/qa-get.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	$this->get('/{ulid}/result', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/qa-get-one.php', APP_ROOT, $_SESSION['rbe-base']);
		$RES = require_once($f);
		return $RES;
	})->add(function($REQ, $RES, $ncb) {

		// ulid
		$ri = $REQ->getAttribute('routeInfo');
		$_GET['code'] = $ri[2]['ulid'];

		$_GET['code'] = trim($_GET['code']);

		if (empty($_GET['code'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'QCR#283: Invalid Inventory Code',
				'_arg' => $_GET,
			), 400);
		}

		$RES = $ncb($REQ, $RES);

		return $RES;
	});

})->add('Middleware_RCE')->add('Middleware_Session');


// Transport Group
$app->group('/transfer', function() {

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

})->add('Middleware_RCE')->add('Middleware_Session');

// Retail Sales
$app->group('/sale', function() {

	$this->get('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/retail-get.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	$this->post('', function($REQ, $RES, $ARG) {
		$f = sprintf('%s/controller/%s/retail-create.php', APP_ROOT, $_SESSION['rbe-base']);
		require_once($f);
		return $RES;
	});

	$this->get('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		print_r($_POST);
		die('Select Retail Sale Item');
	});

	$this->post('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		print_r($_POST);
		die('Update Retail Sale Item');
	});

	$this->delete('/{ulid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		die('Delete Retail Sale');
	});

})->add('Middleware_RCE')->add('Middleware_Session');

/**
	Allow an X-RBE header to over-ride
*/

// Run the App
$app->run();
