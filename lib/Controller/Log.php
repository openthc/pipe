<?php
/**
 * View the Logs from the STEM service
 */

namespace App\Controller;

class Log extends \OpenTHC\Controller\Base
{
	private $sql_debug;

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$res = [];

		if (0 == count($_GET)) {
			// No Query
		} else {
			$res = $this->_sql_query();
		}

		ob_start();
		require_once(APP_ROOT . '/view/log.php');
		$output_html = ob_get_clean();

		if ('snap' == $_GET['a']) {
			$output_snap = _ulid();
			$output_file = sprintf('%s/webroot/snap/%s.html', APP_ROOT, $output_snap);
			$output_link = sprintf('/snap/%s.html', $output_snap);
			$output_html = preg_replace('/<form.+<\/form>/', '', $output_html);
			$output_html = preg_replace('/<div class="sql-debug">.+?<\/div>/', '', $output_html);
			file_put_contents($output_file, $output_html);
			return $RES->withRedirect($output_link);
		}

		_exit_html($output_html);

	}

	/**
	 * Run the Actual Query
	 */
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

		// Date Lo
		if (!empty($_GET['dt0'])) {

			$dt = new \DateTime($_GET['dt0']);

			$ms = (intval($dt->format('U')) * 1000) + intval($dt->format('v'));
			$u0 = \Edoceo\Radix\ULID::create( $ms );
			$u1 = substr($u0, 0, 10) . str_repeat('0', 16);

			$sql_filter[] = 'id >= :pk0';
			$arg[':pk0'] = $u1;

		}

		// Date Hi
		if (!empty($_GET['dt1'])) {

			$dt = new \DateTime($_GET['dt1']);

			$ms = (intval($dt->format('U')) * 1000) + intval($dt->format('v'));
			$u0 = \Edoceo\Radix\ULID::create( $ms );
			$u1 = substr($u0, 0, 10) . str_repeat('Z', 16);

			$sql_filter[] = 'id <= :pk1';
			$arg[':pk1'] = $u1;

		}

		// Search
		if (!empty($_GET['q'])) {
			$sql_filter[] = '(req_head LIKE :q0 OR req_body LIKE :q0 OR res_head LIKE :q1 OR res_body LIKE :q1)';
			$arg[':q0'] = sprintf('%%%s%%', $_GET['q']);
			$arg[':q1'] = sprintf('%%%s%%', $_GET['q']);
		}

		// Build Filter
		if (count($sql_filter)) {
			$sql_filter = implode(' AND ' , $sql_filter);
			$sql = str_replace('{WHERE}', sprintf('WHERE %s', $sql_filter), $sql);
		} else {
			$sql = str_replace('{WHERE}', '', $sql);
		}

		$sql.= ' ORDER BY req_time DESC';
		$sql.= ' LIMIT 100';
		$sql.= sprintf(' OFFSET %d', $_GET['o']);

		$this->sql_debug = $dbc->_sql_debug($sql, $arg);

		$res = $dbc->fetchAll($sql, $arg);

		return $res;

	}

}
