<?php
/**
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
			return require_once(APP_ROOT . '/controller/stem/biotrack.php');
		})
			//->add(array($this, '_load_cre'))
			->add('App\Middleware\Session');

		$a->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{system}/{path:.*}', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/stem/leafdata.php');
		});

		$a->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{system}/{path:.*}', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/stem/metrc.php');
		});

		$a->get('/log', function($REQ, $RES, $ARG) {
			return require_once(APP_ROOT . '/controller/stem/log.php');
		});

	}

	function _load_cre($REQ, $RES, $NMW)
	{
		if (empty($_SESSION['cre-auth'])) {
			return $RES->withStatus(500);
		}

		return $NMW($REQ, $RES);

	}

	/**
		@todo Attach this middleware somehow
	*/
	function _find_cre_from_header($REQ, $RES, $NMW)
	{
		// From Headers?
		//if (!empty($_SERVER['HTTP_OPENTHC_RCE_BASE'])) {
		//	$cre_base = $_SERVER['HTTP_OPENTHC_RCE_BASE'];
		//}
		//if (!empty($_SERVER['HTTP_OPENTHC_RCE_HOST'])) {
		//	$cre_host = $_SERVER['HTTP_OPENTHC_RCE_HOST'];
		//}
	}

}
