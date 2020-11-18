<?php
/**
 * OpenTHC Pipe Application Bootstrap
 */

use Edoceo\Radix\DB\SQL;

define('APP_ROOT', __DIR__);

error_reporting(E_ALL & ~ E_NOTICE);

openlog('openthc-pipe', LOG_ODELAY|LOG_PID, LOG_LOCAL0);

require_once(APP_ROOT . '/vendor/autoload.php');
require_once(APP_ROOT . '/lib/CRE.php');
require_once(APP_ROOT . '/lib/CRE_HTTP.php');
require_once(APP_ROOT . '/lib/CRE_Sync.php');
require_once(APP_ROOT . '/lib/CRE_Iterator.php');
require_once(APP_ROOT . '/lib/CRE_Iterator_LeafData.php');


/**
 * Create and Open the SQLite Database File
 * Global Static Connection!
 */
function _database_create_open($cre, $key)
{
	$ymd = date('Ymd');
	$sql_file = sprintf('%s/var/%s/%s/%s.sqlite', APP_ROOT, $cre, $ymd, $key);
	$sql_path = dirname($sql_file);
	if (!is_dir($sql_path)) {
		mkdir($sql_path, 0755, true);
	}
	$sql_good = is_file($sql_file);

	SQL::init('sqlite:' . $sql_file);
	if (!$sql_good) {
		$sql = <<<SQL
CREATE TABLE log_audit (
	cts not null default (strftime('%s','now')),
	code,
	path,
	req,
	res,
	err
)
SQL;
		SQL::query($sql);
	}

	return $sql_file;

}
