<?php
/**
 * @deprecated
 * Slim Module for the Stem interface
 */

namespace App\Module;

class Stem extends \OpenTHC\Module\Base
{
	function __invoke($a)
	{
		$a->get('', function($REQ, $RES, $ARG) {
			return $this->view->render($RES, 'page/stem.html', array());
		});

		$a->map([ 'GET', 'POST' ], '/biotrack/{system}', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/biotrack.php');
		})->add('App\Middleware\Session');

		$a->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{path:.*}', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/leafdata.php');
		});

		$a->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{system}/{path:.*}', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/metrc.php');
		});

		$a->get('/log', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/log.php');
		});

	}
}
