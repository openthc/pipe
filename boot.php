<?php
/**
 * OpenTHC Pipe Application Bootstrap
 *
 * SPDX-License-Identifier: MIT
 */

use Edoceo\Radix\DB\SQL;

define('APP_ROOT', __DIR__);
define('APP_VERSION', '420.24.276');

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	_exit_html_fail('<h1>Invalid Application Configuration [ALB-035]</h1>', 500);
}

define('OPENTHC_SERVICE_ID', \OpenTHC\Config::get('openthc/pipe/id'));
define('OPENTHC_SERVICE_ORIGIN', \OpenTHC\Config::get('openthc/pipe/origin'));


/**
 * Database Connection
 */
function _dbc() : \Edoceo\Radix\DB\SQL
{
	static $ret;

	if (empty($ret)) {

		$dsn = sprintf('sqlite://%s/var/database.sqlite', APP_ROOT);
		$ret = new SQL($dsn);
		/*
		$cfg = \OpenTHC\Config::get('database');
		$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
		$ret = new SQL($dsn, $cfg['username'], $cfg['password']);
		*/

	}

	return $ret;
}
