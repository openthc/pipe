<?php
/**
	OpenTHC Pipe Application Bootstrap
*/

define('APP_NAME', 'OpenTHC');
define('APP_SITE', 'https://pipe.openthc.org');
define('APP_ROOT', dirname(__FILE__));
define('APP_SALT', md5(APP_NAME . APP_SITE));

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

error_reporting((E_ALL|E_STRICT) ^ E_NOTICE);

// My (crappy) AutoLoader
spl_autoload_register(function($c) {

	$c = str_replace('_', '/', $c);
	$f = sprintf('%s/lib/%s.php', APP_ROOT, $c);

	if (is_file($f)) {
		require_once($f);
	}

}, true, false);

require_once('/opt/com.openthc.com/lib/php.php');
require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/RCE.php');

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

class License
{
	static function findByCode($x)
	{
		//var_dump($x);
		//exit;
		return array(
			'code' => $x,
			'guid' => $x,
		);
	}
}

class Response_From_File extends Slim\Http\Response
{
	function execute($f, $ARG=null)
	{
		$f = trim($f, '/');
		$f = sprintf('%s/controller/%s', APP_ROOT, $f);
		if (!is_file($f)) {
			return $this->withJSON(array(
				'status' => 'failure',
				'detail' => 'Not Found',
				'_f' => $f,
			), 404);
		}
		$r = require_once($f);
		return $r;
	}
}


class Response_JSON extends Slim\Http\Response
{
	function withJSON($data, $code=200, $opts=null)
	{
		if (empty($opts)) {
			$opts = JSON_PRETTY_PRINT;
		}

		return parent::withJSON($data, $code, $opts);

	}
}
