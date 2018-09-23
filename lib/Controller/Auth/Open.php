<?php
/**
	Connect and Authenticate to an RCE
*/

namespace App\Controller\Auth;

use Edoceo\Radix\DB\SQL;

class Open extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		switch ($REQ->getMethod()) {
		case 'GET':
			$RES = $this->renderForm($REQ, $RES, $ARG);
			break;
		case 'POST':
			switch ($_POST['a']) {
			case 'set-license':
				$_SESSION['rce-auth']['license'] = $_POST['license'];
				return $RES->withRedirect('/browse');
				break;
			}
			return $this->connect($REQ, $RES, $ARG);
			break;
		}

	}

	/**
		Connect
	*/
	function connect($REQ, $RES, $ARG)
	{
		$rce = $this->validateRCE();

		if (empty($rce)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => sprintf('CAC#017: Invalid RCE: "%s"', strtolower(trim($_POST['rce']))),
			), 400);
		}

		$_SESSION['rce'] = $rce;
		$_SESSION['rce-auth'] = array();
		$_SESSION['rce-base'] = null;
		$_SESSION['sql-hash'] = null;

		switch ($rce['engine']) {
		case 'biotrack':
			$_SESSION['rce-base'] = 'biotrack';
			$RES = $this->_biotrack($RES);
			break;
		case 'leafdata':
			$_SESSION['rce-base'] = 'leafdata';
			$RES = $this->_leafdata($RES);
			break;
		case 'metrc':
			$_SESSION['rce-base'] = 'metrc';
			$RES = $this->_metrc($RES);
			break;
		}

		$this->_createDatabase();

		if (!empty($_GET['client_id'])) {
			// Need to Connect to OpenTHC Here
			// Perhaps this is Middleware?
			switch ($_GET['client_id']) {
			case 'dump.openthc.com':
				return $RES->withRedirect('https://dump.openthc.com/auth/open?pipe-token=' . session_id());
				break;
			case 'qa.openthc.org':
				return $RES->withRedirect('https://qa.openthc.org/auth/open?pipe-token=' . session_id());
				break;
			}
		}

		if (!empty($_POST['a'])) {
			if ('auth-web' == $_POST['a']) {
				return $RES->withRedirect('/browse');
			}
		}

		return $RES;
	}

	/**
		Render the Connection Form
	*/
	function renderForm($REQ, $RES, $ARG)
	{

		$rce_file = sprintf('%s/etc/rce.ini', APP_ROOT);
		$rce_data = parse_ini_file($rce_file, true, INI_SCANNER_RAW);

		$data = array();
		$data['rce_list'] = $rce_data;
		$data['rce_code'] = $_SESSION['rce']['code'];
		$data['rce_company'] = $_SESSION['rce-auth']['company'];
		$data['rce_license'] = $_SESSION['rce-auth']['license'];
		$data['rce_vendor_psk'] = $_SESSION['rce-auth']['vendor-key'];
		$data['rce_client_psk'] = $_SESSION['rce-auth']['client-key'];
		$data['rce_username'] = $_SESSION['rce-auth']['username'];;
		$data['rce_password'] = $_SESSION['rce-auth']['password'];;

		if (empty($data['rce_client_api_key'])) {
			$data['rce_client_api_key'] = $_SESSION['rce-auth']['secret'];
		}

		return $this->_container->view->render($RES, 'page/auth.html', $data);

	}

	/**
		Connect to a BT system
	*/
	function _biotrack($RES)
	{
		if (!empty($_POST['sid'])) {

			$_SESSION['rce-auth']['session'] = $_POST['sid'];

			$RES = $RES->withJson(array(
				'status' => 'success',
				'detail' => 'Session Continues',
				'result' => session_id(), // $chk,
			));

			return $RES;
		}

		$uid = strtolower(trim($_POST['username']));

		if (!preg_match('/\w+@\w+/', $uid)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#023: Invalid Username',
				'_post' => $_POST,
			), 400);
		}

		// Password
		$pwd = trim($_POST['password']);
		if (!preg_match('/^.{10}/', $pwd)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#031: Invalid Password',
			), 400);
		}

		$ext = preg_replace('/[^\d]+/', null, $_POST['company']);
		$ext = substr($ext, 0, 9);

		if (!preg_match('/^\d{9}$/', $ext)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#060: Provide UBI in the rce-company field',
			), 400);
		}

		$rce = \RCE::factory($_SESSION['rce']);
		$chk = $rce->login($ext, $uid, $pwd);

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

			$_SESSION['rce-auth']['company'] = $ext;
			$_SESSION['rce-auth']['username'] = $uid;
			$_SESSION['rce-auth']['password'] = $pwd;
			$_SESSION['rce-auth']['session'] = $chk['sessionid'];

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
	function _leafdata($RES)
	{
		$lic = trim($_POST['license']);
		$lic = strtoupper($lic);

		$key = trim($_POST['client-psk']);

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
				'_post' => $_POST,
			));
		}

		$_SESSION['rce-auth'] = array(
			'license' => $lic,
			'secret' => $key,
		);

		$rce = \RCE::factory($_SESSION['rce']);
		$res = $rce->ping();

		if (empty($res)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'CAC#192 Invalid License or API Key',
				'_s' => $_SESSION,
				'_rce' => $rce,
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
	function _metrc($RES)
	{
		$_SESSION['rce-auth'] = array(
			'vendor-key' => $_POST['vendor-psk'],
			'client-key' => $_POST['client-psk'],
			'license' => $_POST['license'],
		);

		$rce = \RCE::factory($_SESSION['rce']);
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
		Validate the RCE
	*/
	private function validateRCE()
	{

		$rce_file = sprintf('%s/etc/rce.ini', APP_ROOT);
		$rce_data = parse_ini_file($rce_file, true, INI_SCANNER_RAW);
		// var_dump($rce_data);

		$rce_want = strtolower(trim($_POST['rce']));

		// Re-Map Legacy Name
		//switch ($rce_want) {
		//if ('wa/leaf' == $rce_want) {
		//	$rce_want = 'wa/mjf';
		//}

		$rce_info = $rce_data[ $rce_want ];

		if (!empty($rce_info)) {
			$rce_info['code'] = $rce_want;
			return $rce_info;
		}
	}

	/**
		Create a Database for Caching Records
	*/
	private function _createDatabase()
	{

		$_SESSION['sql-hash'] = md5(json_encode($_POST));

		$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $_SESSION['sql-hash']);
		if (!is_file($sql_file)) {

			// Create Database
			SQL::init('sqlite:' . $sql_file);
			SQL::query('CREATE TABLE _config (key TEXT PRIMARY KEY, val TEXT)');
			SQL::query("CREATE TABLE _log_audit (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE _log_alert (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query("CREATE TABLE _log_delta (cts not null default (strftime('%s','now')), code, meta TEXT)");
			SQL::query('CREATE TABLE contact (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE license (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE lot (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE lot_delta (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE plant (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE product (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE qa (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE strain (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE transfer (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE waste (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE vehicle (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');
			SQL::query('CREATE TABLE zone (guid TEXT PRIMARY KEY, hash TEXT, meta TEXT)');

			SQL::query('INSERT INTO _config VALUES (?, ?)', array('Created', date(\DateTime::RFC3339)));
			SQL::query('INSERT INTO _log_audit (code, meta) VALUES (?, ?)', array('App Created', date(\DateTime::RFC3339)));

		}

	}

}
