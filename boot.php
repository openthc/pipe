<?php
/**
 * OpenTHC Pipe Application Bootstrap
 */

use Edoceo\Radix\DB\SQL;

define('APP_ROOT', __DIR__);

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');

/**
 * Database Connection
 */
function _dbc()
{
	static $ret;

	if (empty($ret)) {

		$url = getenv('POSTGRES_URL');
		$url = parse_url($url);
		$url['path'] = trim($url['path'], '/');

		$dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], $url['path']);
		$ret = new SQL($dsn, $url['user'], $url['pass']);

	}

	return $ret;
}
