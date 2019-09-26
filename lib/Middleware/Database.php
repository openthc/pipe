<?php
/**
 * Makes sure the Caching Database is present
 */

namespace App\Middleware;

use Edoceo\Radix\DB\SQL;

class Database
{
	public function __invoke($REQ, $RES, $NMW)
	{
		if (empty($_SESSION['sql-hash'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication State [LMD#017]',
			), 403);
		}

		$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
		$sql_good = (is_file($sql_file) && (filesize($sql_file) > 512));

		SQL::init('sqlite:' . $sql_file);

		if (!$sql_good) {

			// Create Database
			SQL::query('CREATE TABLE _config (key TEXT PRIMARY KEY, val TEXT)');
			SQL::query("CREATE TABLE _log_alert (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE _log_audit (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE _log_delta (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query('CREATE TABLE contact (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE license (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE batch (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE lab_result (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE lot (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE lot_delta (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE plant (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE plant_collect (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE product (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE retail (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE strain (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE transfer_incoming (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE transfer_outgoing (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE waste (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE vehicle (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE zone (guid TEXT PRIMARY KEY, stat INT, hash TEXT, meta TEXT)');

			SQL::query('INSERT INTO _config VALUES (?, ?)', array('Created', date(\DateTime::RFC3339)));
			SQL::query('INSERT INTO _log_audit (code, meta) VALUES (?, ?)', array('App Created', date(\DateTime::RFC3339)));
			SQL::query('INSERT INTO _log_audit (code, meta) VALUES (?, ?)', array('App Session', json_encode($_SESSION)));

		}

		$RES = $NMW($REQ, $RES);

		return $RES;

	}
}
