<?php
/**
	Slim Module for the Stem interface
*/

namespace App\Module;

class Stem extends \OpenTHC\Module\Base
{

function __invoke($a)
{
	$a->get('', function($REQ, $RES, $ARG) {
		return $this->view->render($RES, 'page/stem.html', array());
	});

	$a->any('/biotrack/{system}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/biotrack.php');
	})->add(array($this, '_load_rce'))->add('App\Middleware\Session');

	$a->map([ 'GET', 'POST' ], '/leafdata/{system}/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/leafdata.php');
	});

	$a->map([ 'GET', 'POST' ], '/metrc/{system}/{path:.*}', function($REQ, $RES, $ARG) {
		return require_once(APP_ROOT . '/controller/stem/metrc.php');
	});

}

function _load_rce($REQ, $RES, $NMW)
{
	if (empty($_SESSION['rbe-auth'])) {
		die("NO");
	}
	
	var_dump($_SESSION);
	exit;

	return $NMW($REQ, $RES);

}


}
