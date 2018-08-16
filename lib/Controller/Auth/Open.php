<?php
/**
	Connect and Authenticate to an RBE
*/

namespace App\Controller\Auth;

use Edoceo\Radix\DB\SQL;

class Open
{
	protected $_c;

	function __construct($c)
	{
		$this->_c = $c;
	}

	function __invoke($REQ, $RES, $ARG)
	{
		switch ($REQ->getMethod()) {
		case 'GET':
			return $this->renderForm($REQ, $RES, $ARG);
			break;
		case 'POST':
			return $this->connect($REQ, $RES, $ARG);
			break;
		}

	}

	/**
		Connect
	*/
	function connect($REQ, $RES, $ARG)
	{
		$rbe = $this->validateRBE();

		if (empty($rbe)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => sprintf('CAC#017: Invalid RBE: "%s"', strtolower(trim($_POST['rbe']))),
			), 400);
		}

		$_SESSION['rbe'] = $rbe;

		switch ($rbe['engine']) {
		case 'biotrack':
			$_SESSION['rbe-base'] = 'biotrack';
			$RES = $this->_biotrack($RES, $rbe);
			break;
		case 'leafdata':
			$_SESSION['rbe-base'] = 'leafdata';
			$RES = $this->_leafdata($RES, $rbe);
			break;
		case 'metrc':
			$_SESSION['rbe-base'] = 'metrc';
			$RES = $this->_metrc($RES, $rbe);
			break;
		}

		$this->_createDatabase();

		return $RES;
	}

	function renderForm($REQ, $RES, $ARG)
	{
		$rbe_file = sprintf('%s/etc/rce.ini', APP_ROOT);
		$rbe_data = parse_ini_file($rbe_file, true, INI_SCANNER_RAW);
		 //var_dump($rbe_data);
		 //exit;

		$data = array();
		$data['rbe_list'] = $rbe_data;

		return $this->_c->view->render($RES, 'page/auth-form.html', $data);

	}

	function _biotrack($RES, $rbe)
	{
		if (!empty($_POST['sid'])) {

			$_SESSION['rbe-auth'] = $_POST['sid'];

			$RES = $RES->withJson(array(
				'status' => 'success',
				'detail' => 'Session Continues',
				'result' => session_id(), // $chk,
			));

			return $RES;
		}

		$uid = strtolower(trim($_POST['rbe-meta-username']));

		if (!preg_match('/\w+@\w+/', $uid)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#023: Invalid Username',
				'_post' => $_POST,
			), 400);
		}

		// Password
		$pwd = trim($_POST['rbe-meta-password']);
		if (!preg_match('/^.{10}/', $pwd)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#031: Invalid Password',
			), 400);
		}

		$ext = preg_replace('/[^\d]+/', null, $_POST['rbe-meta-company']);
		$ext = substr($ext, 0, 9);

		if (!preg_match('/^\d{9}$/', $ext)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#060: Provide UBI in the rbe-meta-company field',
			), 400);
		}

		$api = \RCE::factory($rbe);
		$chk = $api->login($ext, $uid, $pwd);

		// @todo Detect a 500 Layer Response from BioTrack

		switch (intval($chk['success'])) {
		case 0:

			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#075: Invalid Username or Password',
				'result' => $chk,
			), 400);

			break;

		case 1:

			$_SESSION['rbe'] = $rbe;
			$_SESSION['rbe-auth'] = $chk['sessionid'];

			return $RES->withJson(array(
				'status' => 'success',
				'detail' => 'Session Established',
				'result' => session_id(),
			));

			break;
		}

	}

	/**
		Connect to a LeafData System
	*/
	function _leafdata($RES, $rbe)
	{
		$lic = trim($_POST['rbe-meta-license']);
		$lic = strtoupper($lic);

		$key = trim($_POST['rbe-meta-client-apikey']);

		if (!preg_match('/^(G|J|L|M|R)\w+$/', $lic)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'CAC#124 Invalid License',
			));
		}

		if (empty($key)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'CAC#131 Invalid API Key',
			));
		}

		$_SESSION['rbe'] = $rbe;
		$_SESSION['rbe-auth'] = array(
			'license' => $lic,
			'secret' => $key,
		);

		$api = \RCE::factory($rbe);
		$res = $api->ping();

		if (empty($res)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'CAC#192 Invalid License or API Key',
				'_s' => $_SESSION,
				'_api' => $api,
				'_res' => $res,
			));
		}

		return $RES->withJSON(array(
			'status' => 'success',
			'result' => session_id(),
		));

	}

	/**
		Connect to a METRC system
	*/
	function _metrc($RES, $rbe)
	{
		//_var_dump($rbe);

		$_SESSION['rbe-auth'] = array(
			'vendor-key' => $_POST['rbe-meta-vendor-apikey'],
			'client-key' => $_POST['rbe-meta-client-apikey'],
			'license' => $_POST['rbe-meta-license'],
		);

		$rce = \RCE::factory($rbe);
		//_var_dump($rce);

		$res = $rce->ping();
		if ($res) {
			return $RES->withJSON(array(
				'status' => 'success',
				'result' => session_id(),
			));
		}

		return $RES->withJSON(array(
			'status' => 'failure',
			'detail' => '',
		));

	}

	/**
		Validate the RBE
	*/
	private function validateRBE()
	{

		$rbe_file = sprintf('%s/etc/rce.ini', APP_ROOT);
		$rbe_data = parse_ini_file($rbe_file, true, INI_SCANNER_RAW);
		// var_dump($rbe_data);

		$rbe_want = strtolower(trim($_POST['rbe']));

		// Re-Map Legacy Name
		//switch ($rbe_want) {
		//if ('wa/leaf' == $rbe_want) {
		//	$rbe_want = 'wa/mjf';
		//}

		$rbe_info = $rbe_data[ $rbe_want ];

		if (!empty($rbe_info)) {
			$rbe_info['code'] = $rbe_want;
			$rbe_info['agency'] = $rbe_want;
			return $rbe_info;
		}
	}

	/**
		Create a Database for Caching Records
	*/
	private function _createDatabase()
	{

		$sql_hash = md5(json_encode($_POST));

		$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $sql_hash);
		if (!is_file($sql_file)) {

			// Create Database
			SQL::init('sqlite:' . $sql_file);
			SQL::query('CREATE TABLE cfg_app (key TEXT PRIMARY KEY, val TEXT)');
			SQL::query('CREATE TABLE cfg_product (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE cfg_strain (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE cfg_zone (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');

			SQL::query('CREATE TABLE lot (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE plant (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE qa (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE transfer (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE waste (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');

			SQL::query("CREATE TABLE log_alert (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE log_delta (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE log_event (cts not null default (strftime('%s','now')), code, meta TEXT)");

			SQL::query('INSERT INTO cfg_app VALUES (?, ?)', array('Created', date(\DateTime::RFC3339)));
			SQL::query('INSERT INTO log_event (code, meta) VALUES (?, ?)', array('App Created', date(\DateTime::RFC3339)));

			$_SESSION['sql-hash'] = $sql_hash;

		}

	}

}
