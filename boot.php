<?php
/**
 * OpenTHC Pipe Application Bootstrap
 */

define('APP_NAME', 'OpenTHC');
define('APP_SITE', 'https://pipe.openthc.org');
define('APP_ROOT', __DIR__);
define('APP_SALT', sha1(APP_NAME . APP_SITE . APP_ROOT));
define('APP_BUILD', '420.18.238');

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/CRE.php');
require_once(APP_ROOT . '/lib/CRE_HTTP.php');
require_once(APP_ROOT . '/lib/CRE_Sync.php');
require_once(APP_ROOT . '/lib/CRE_Iterator.php');
require_once(APP_ROOT . '/lib/CRE_Iterator_LeafData.php');

// My (crappy) AutoLoader
spl_autoload_register(function($c) {

	$c = str_replace('_', '/', $c);
	$f = sprintf('%s/lib/%s.php', APP_ROOT, $c);

	if (is_file($f)) {
		require_once($f);
	}

}, true, false);


/*
*/
function _from_cre_file($f, $RES, $ARG)
{
	$f = trim($f, '/');
	$f = sprintf('%s/controller/%s/%s', APP_ROOT, $_SESSION['cre-base'], $f);
	if (!is_file($f)) {

		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => 'Interface not implemented [APP#046]',
		), 501, JSON_PRETTY_PRINT);

		// return $RES->withJSON(array(
		// 	'status' => 'failure',
		// 	'detail' => 'Not Found',
		// 	'_f' => $f,
		// 	'_s' => $_SESSION,
		// 	'_R' => $_SERVER,
		// ), 404);
	}

	$r = require_once($f);

	return $r;

};


function _hash_obj($o)
{
	_ksort_r($o);
	$hash = sha1(json_encode($o));
	return $hash;
}


class App
{
	static function log() { }

	static function metric()
	{
		return new App_Metric();
	}

}

class App_Metric
{
	function counter() { }
	function timing() { }
}

/**
	A Faker cause some of the CRE tools depend on this
*/
class License extends \OpenTHC\License
{
//	static function findByCode($x)
//	{
//		//var_dump($x);
//		//exit;
//		return array(
//			'code' => $x,
//			'guid' => $x,
//		);
//	}
//
//	static function findByGUID($x)
//	{
//		//var_dump($x);
//		//exit;
//		return array(
//			'code' => $x,
//			'guid' => $x,
//		);
//	}
}
