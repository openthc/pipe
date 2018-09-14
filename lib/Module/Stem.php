<?php
/**
	Slim Module for the Stem interface
*/

class Stem extends \OpenTHC\Module\Base
{

function __invoke($a)
{
	$a->get('', function($REQ, $RES, $ARG) {
		return $this->view->render($RES, 'page/stem.html', array());
	});

	$a->post('/biotrack', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/biotrack.php');
	});

	$a->map([ 'GET', 'POST' ], '/leafdata/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/leafdata.php');
	});

	$a->map([ 'GET', 'POST' ], '/metrc/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/metrc.php');
	});

	//->add('App\Middleware\Log\HTTP');
}

}
