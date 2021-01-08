<?php
/**
 * View the Logs from the STEM service
 */

namespace App\Controller;

use Edoceo\Radix\DB\SQL;

class Log extends \OpenTHC\Controller\Base
{
	private $sql_debug;

	function __invoke($REQ, $RES, $ARG)
	{
		$res = [];

		if (0 == count($_GET)) {
			// No Query
		} else {
			$res = $this->_sql_query($sql, $arg);
		}

		require_once(APP_ROOT . '/view/log.php');

	}

	function _sql_query()
	{
		$dbc = _dbc();

		$arg = [];
		$sql = 'SELECT * FROM log_audit {WHERE} ';
		$sql_filter = [];

		// License Filter
		if (!empty($_GET['l'])) {
			$sql_filter[] = 'lic_hash = :l0';
			$arg[':l0'] = $_GET['l'];
		}

		if (!empty($_GET['q'])) {
			$sql_filter[] = '(req_head LIKE :q0 OR req_body LIKE :q0 OR res_head LIKE :q1 OR res_body LIKE :q1)';
			$arg[':q0'] = sprintf('%%%s%%', $_GET['q']);
			$arg[':q1'] = sprintf('%%%s%%', $_GET['q']);
		}
		if (!empty($_GET['dt0'])) {

			$dt = new \DateTime($_GET['dt0']);

			$ms = (intval($dt->format('U')) * 1000) + intval($dt->format('v'));
			$u0 = \Edoceo\Radix\ULID::create( $ms );
			$u1 = substr($u0, 0, 10) . str_repeat('0', 16);

			$sql_filter[] = 'id >= :pk0';
			$arg[':pk0'] = $u1;

		}
		if (!empty($_GET['dt1'])) {

			$dt = new \DateTime($_GET['dt1']);

			$ms = (intval($dt->format('U')) * 1000) + intval($dt->format('v'));
			$u0 = \Edoceo\Radix\ULID::create( $ms );
			$u1 = substr($u0, 0, 10) . str_repeat('Z', 16);

			$sql_filter[] = 'id <= :pk1';
			$arg[':pk1'] = $u1;

		}

		$sql_filter[] = "res_head NOT LIKE '%HTTP/1.1 504 Gateway Time-out%'";

		if (count($sql_filter)) {
			$sql_filter = implode(' AND ' , $sql_filter);
			$sql = str_replace('{WHERE}', sprintf('WHERE %s', $sql_filter), $sql);
		} else {
			$sql = str_replace('{WHERE}', '', $sql);
		}

		$sql.= ' ORDER BY req_time DESC';
		$sql.= ' LIMIT 100';
		$sql.= sprintf(' OFFSET %d', $_GET['o']);

		$this->_sql_debug = $dbc->_sql_debug($sql, $arg);

		$res = $dbc->fetchAll($sql, $arg);

		return $res;

	}

}
