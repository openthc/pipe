<?php
/**
 * Connect and Authenticate to a CRE
 */

namespace App\Controller\Auth;


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
				$_SESSION['cre-auth']['license'] = $_POST['license'];
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
		$cre = $this->validateCRE();

		if (empty($cre)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => sprintf('Invalid CRE: "%s" [CAC#017]', strtolower(trim($_POST['cre']))),
			), 400);
		}

		$_SESSION['cre'] = $cre;
		$_SESSION['cre-auth'] = array();
		$_SESSION['cre-base'] = null;
		$_SESSION['sql-hash'] = null;

		switch ($cre['engine']) {
		case 'biotrack':
			$_SESSION['cre-base'] = 'biotrack';
			$RES = $this->_biotrack($RES);
			break;
		case 'leafdata':
			$_SESSION['cre-base'] = 'leafdata';
			$RES = $this->_leafdata($RES);
			break;
		case 'metrc':
			$_SESSION['cre-base'] = 'metrc';
			$RES = $this->_metrc($RES);
			break;
		}

		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		$_SESSION['sql-hash'] = md5(json_encode($_POST));

		// Someone asked for redirect
		if (!empty($_GET['r'])) {
			return $RES->withRedirect($_GET['r']);
		}

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

		// From our webform
		if ('auth-web' == $_POST['a']) {
			return $RES->withRedirect('/browse');
		}

		return $RES;
	}

	/**
		Render the Connection Form
	*/
	function renderForm($REQ, $RES, $ARG)
	{

		$cre_file = sprintf('%s/etc/cre.ini', APP_ROOT);
		$cre_data = parse_ini_file($cre_file, true, INI_SCANNER_RAW);

		$data = array();
		$data['cre_list'] = $cre_data;
		$data['cre_code'] = $_SESSION['cre']['code'];
		$data['cre_company'] = $_SESSION['cre-auth']['company'];
		$data['cre_license'] = $_SESSION['cre-auth']['license'];
		$data['cre_vendor_key'] = $_SESSION['cre-auth']['vendor-key'];
		$data['cre_client_key'] = $_SESSION['cre-auth']['client-key'];
		$data['cre_username'] = $_SESSION['cre-auth']['username'];
		$data['cre_password'] = $_SESSION['cre-auth']['password'];

		if (!empty($_GET['cre'])) {
			$data['cre_code'] = $_GET['cre'];
		}

		if (empty($data['cre_client_api_key'])) {
			$data['cre_client_api_key'] = $_SESSION['cre-auth']['secret'];
		}

		return $this->_container->view->render($RES, 'page/auth.html', $data);

	}

	/**
		Connect to a BT system
	*/
	function _biotrack($RES)
	{
		if (!empty($_POST['sid'])) {

			$_SESSION['cre-auth']['session'] = $_POST['sid'];

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
				'detail' => 'OCA#060: Provide UBI in the cre-company field',
			), 400);
		}

		$cre = \CRE::factory($_SESSION['cre']);
		$chk = $cre->login($ext, $uid, $pwd);

		// @todo Detect a 500 Layer Response from BioTrack

		switch (intval($chk['success'])) {
		case 0:
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'Invalid Username or Password [CAO#184]',
				'result' => $chk,
			), 400);

			break;

		case 1:

			$_SESSION['cre-auth']['company'] = $ext;
			$_SESSION['cre-auth']['username'] = $uid;
			$_SESSION['cre-auth']['password'] = $pwd;
			$_SESSION['cre-auth']['session'] = $chk['sessionid'];

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

		$key = trim($_POST['client-key']);

		if (!preg_match('/^(G|J|L|M|R)\w+$/', $lic)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid License [CAO#209]',
			));
		}

		if (empty($key)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid API Key [CAO#216]',
				'_post' => $_POST,
			));
		}

		$_SESSION['cre-auth'] = array(
			'license' => $lic,
			'client-key' => $key,
		);

		$cre = \CRE::factory($_SESSION['cre']);
		$res = $cre->ping();

		if (empty($res)) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid License or API Key [CAO#239]',
				'_post' => $_POST,
			), 403);
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
		$_SESSION['cre-auth'] = array(
			'vendor-key' => $_POST['vendor-key'],
			'client-key' => $_POST['client-key'],
			'license' => $_POST['license'],
		);

		$cre = \CRE::factory($_SESSION['cre']);
		//_var_dump($cre);

		$res = $cre->ping();
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
		Validate the CRE
	*/
	private function validateCRE()
	{
		$cre_file = sprintf('%s/etc/cre.ini', APP_ROOT);
		$cre_data = parse_ini_file($cre_file, true, INI_SCANNER_RAW);
		// var_dump($cre_data);

		$cre_want = strtolower(trim($_POST['cre']));
		switch ($cre_want) {
		case 'leafdata':
		case 'wa/leaf':
		case 'wa':
			$cre_want = 'usa/wa';
			break;
		}

		$cre_info = $cre_data[ $cre_want ];

		if (!empty($cre_info)) {
			$cre_info['code'] = $cre_want;
			return $cre_info;
		}
	}
}
