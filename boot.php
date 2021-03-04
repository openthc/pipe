<?php
/**
 * OpenTHC Pipe Application Bootstrap
 */

use Edoceo\Radix\DB\SQL;

define('APP_ROOT', __DIR__);

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');

\OpenTHC\Config::init(APP_ROOT);

/**
 * Database Connection
 */
function _dbc()
{
	static $ret;

	if (empty($ret)) {

		$cfg = \OpenTHC\Config::get('database');
		$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		$ret = new SQL($dsn, $cfg['username'], $cfg['password']);

	}

	return $ret;
}
