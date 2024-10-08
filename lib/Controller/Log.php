<?php
/**
 * Log Viewer
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller;

class Log extends \OpenTHC\Controller\Base
{
	private $query_limit = 25;
	private $query_offset = 0;

	private $sql_debug;

	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		if (count($_GET)) {

			switch ($_GET['a']) {
			case 'x':
				// Clear Filters
				return $RES->withRedirect('/log');
			}

		}

		$this->query_offset = max(0, intval($_GET['o']));

		$data = [
			'Page' => [ 'title' => 'Log Search' ],
			'tz' => \OpenTHC\Config::get('tz'),
			'link_newer' => null,
			'link_older' => null,
			'log_audit' => [], //$res,
			'sql_debug' => null,
		];

		$data['log_audit'] = $this->_sql_query();
		$data['sql_debug'] = $this->sql_debug;

		$data['link_newer'] = http_build_query(array_merge($_GET, [ 'o' => max(0, $this->query_offset - $this->query_limit) ] ));
		$data['link_older'] = http_build_query(array_merge($_GET, [ 'o' => $this->query_offset + $this->query_limit ] ));

		$output_html = $this->render('log.php', $data);

		return $RES->write($output_html);

	}

	function snap($REQ, $RES, $ARG)
	{
		$output_snap = _ulid();
		$output_link = sprintf('/output/snap-%s.html', $output_snap);
		$output_file = sprintf('%s/webroot%s', APP_ROOT, $output_link);

		// $data['Page']['title'] = sprintf('OpenTHC Log Snapshot %s', $output_snap);
		// $data['snap'] = 'snap';
		$output_note = sprintf('<body><div class="container-fluid mt-3"><div class="alert alert-warning">View Snapshot %s</div></div>', $output_snap);

		$output_html = $_POST['source-html'];
		$output_html = preg_replace('/<title>.+?<\/title>/', sprintf('<title>PIPE || Snapshot %s</title>', $output_snap), $output_html);
		// $output_html = preg_replace('/<body>/', $output_note, $output_html);
		$output_html = preg_replace('/<h1>.+?<\/h1>/', sprintf('<h1>Snapshot %s', $output_snap), $output_html);
		$output_html = preg_replace('/<form.+<\/form>/ms', '', $output_html);
		$output_html = preg_replace('/<div class="sql-debug.+?<\/div>/ms', '', $output_html);
		// $output_html = preg_replace('/<script>.+?<\/script>/ms', '', $output_html);

		file_put_contents($output_file, $output_html);

		return $RES->withJSON([
			'data' => $output_link,
			'meta' => [ 'note' => 'Done' ]
		], 201);

		// return $RES->withRedirect($output_link);

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

		// Specific ID?
		if (!empty($_GET['id'])) {
			$sql = str_replace('{WHERE}', 'WHERE id = :pk', $sql);
			$arg = [ ':pk' => $_GET['id'] ];
			$this->sql_debug = $dbc->_sql_debug($sql, $arg);
			$res = $dbc->fetchAll($sql, $arg);
			return $res;
		}

		// License Filter
		if (!empty($_GET['l'])) {
			$sql_filter[] = 'lic_hash = :l0';
			$arg[':l0'] = $_GET['l'];
		}

		// Date Lo
		if (!empty($_GET['d0'])) {

			$dt = new \DateTime($_GET['d0'] . ' ' . $_GET['t0']);

			$ms = (intval($dt->format('U')) * 1000) + intval($dt->format('v'));
			$u0 = \Edoceo\Radix\ULID::create( $ms );
			$u1 = substr($u0, 0, 10) . str_repeat('0', 16);

			$sql_filter[] = 'id >= :pk0';
			$arg[':pk0'] = $u1;

		}

		// Date Hi
		if (!empty($_GET['d1'])) {

			$dt = new \DateTime($_GET['d1'] . ' ' . $_GET['t1']);

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
		$sql.= sprintf(' LIMIT %d', $this->query_limit);
		$sql.= sprintf(' OFFSET %d', $_GET['o']);

		$this->sql_debug = $dbc->_sql_debug($sql, $arg);

		$res = $dbc->fetchAll($sql, $arg);

		return $res;

	}

}
