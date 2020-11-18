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
		//$RES = $this->validateCaptcha($RES);
		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		$cre = $this->validateCRE();

		if (empty($cre)) {
			return $RES->withJson([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid CRE: "%s" [CAC#017]', strtolower(trim($_POST['cre']))) ],
			], 400);
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

		// Sloppy Shit Right Here /djb
		if (!empty($_GET['client_id'])) {
			// Need to Connect to OpenTHC Here
			// Perhaps this is Middleware?
			switch ($_GET['client_id']) {
			case 'dump.openthc.com':
				return $RES->withRedirect('https://dump.openthc.com/auth/open?pipe-token=' . session_id());
				break;
			case 'lab.openthc.org':
				return $RES->withRedirect('https://lab.openthc.org/auth/open?pipe-token=' . session_id());
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
	 * Render the Connection Form
	 */
	function renderForm($REQ, $RES, $ARG)
	{

		$data = [];
		$data['Page'] = [ 'title' => 'Authenticate' ];
		$data['cre_list'] = \CRE::getEngineList();
		$data['cre_code'] = $_SESSION['cre']['code'];
		$data['cre_company'] = $_SESSION['cre-auth']['company'];
		$data['cre_license'] = $_SESSION['cre-auth']['license'];
		$data['cre_program_key'] = $_SESSION['cre-auth']['program-key'];
		$data['cre_license_key'] = $_SESSION['cre-auth']['license-key'];
		$data['cre_username'] = $_SESSION['cre-auth']['username'];
		$data['cre_password'] = $_SESSION['cre-auth']['password'];

		$data['google_recaptcha_v2'] = [];
		$data['google_recaptcha_v2']['public'] = \OpenTHC\Config::get('google_recaptcha_v2.public');

		$data['google_recaptcha_v3'] = [];
		$data['google_recaptcha_v3']['public'] = \OpenTHC\Config::get('google_recaptcha_v3.public');


		if (!empty($_GET['cre'])) {
			$data['cre_code'] = $_GET['cre'];
		}

		return $this->_container->view->render($RES, 'page/auth.html', $data);

	}

	/**
	 * Connect to a BT system
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

		$uid = trim($_POST['username']);
		// $uid = strtolower($uid);
		// if (!preg_match('/\w+@\w+/', $uid)) {
		// 	return $RES->withJson(array(
		// 		'status' => 'failure',
		// 		'detail' => 'OCA#023: Invalid Username',
		// 		'_post' => $_POST,
		// 	), 400);
		// }

		// Password
		$pwd = trim($_POST['password']);
		if (!preg_match('/^.{10}/', $pwd)) {
			return $RES->withJson(array(
				'status' => 'failure',
				'detail' => 'OCA#031: Invalid Password',
			), 400);
		}

		$ext = trim($_POST['company']);
		// $ext = preg_replace('/[^\d]+/', null, $ext);
		// $ext = substr($ext, 0, 9);
		// if (!preg_match('/^\d{9}$/', $ext)) {
		// 	return $RES->withJson(array(
		// 		'status' => 'failure',
		// 		'detail' => 'OCA#060: Provide UBI in the cre-company field',
		// 	), 400);
		// }

		$cre = \CRE::factory($_SESSION['cre']);
		// $cre->setTestMode();
		$chk = $cre->login($ext, $uid, $pwd);
		// var_dump($cre); die("FSDFS");
		// exit;

		// @todo Detect a 500 Layer Response from BioTrack

		switch (intval($chk['success'])) {
		case 0:

			return $RES->withJson(array(
				'meta' => [ 'detail' => 'Invalid Username or Password [CAO#184]' ],
				'data' => $chk,
			), 400);

			break;

		case 1:

			$_SESSION['cre-auth']['company'] = $ext;
			$_SESSION['cre-auth']['username'] = $uid;
			$_SESSION['cre-auth']['password'] = $pwd;
			$_SESSION['cre-auth']['session'] = $chk['sessionid'];

			return $RES->withJson(array(
				'meta' => [ 'detail' => 'Session Established' ],
				'data' => session_id(),
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

		$key = trim($_POST['license-key']);

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
			'license-key' => $key,
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

		return $RES->withJSON([
			'data' => session_id(),
			'meta' => [],
			'status' => 'success',
			'result' => session_id(),
		]);

	}

	/**
		Connect to a METRC system
	*/
	function _metrc($RES)
	{
		$_SESSION['cre-auth'] = array(
			'license' => $_POST['license'],
			'program-key' => $_POST['program-key'],
			'license-key' => $_POST['license-key'],
		);

		$cre = \CRE::factory($_SESSION['cre']);

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
	 * [validateCaptcha description]
	 * @param Response $RES [description]
	 * @return Response [description]
	 */
	private function validateCaptcha($RES)
	{
		if (empty($_POST['g-recaptcha-response'])) {
			return $RES->withRedirect('/auth/fail?e=cao290');
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$arg = array('form_params' => array(
			'secret' => \OpenTHC\Config::get('google_recaptcha.secret'),
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		));
		$ghc = new \GuzzleHttp\Client();
		$res = $ghc->post($url, $arg);

		if (200 != $res->getStatusCode()) {
			return $RES->withRedirect('/auth/fail?e=cao316');
		}

		$res = json_decode($res->getBody(), true);
		if (empty($res['success'])) {
			return $RES->withRedirect('/auth/fail?e=cao321');
		}

		return $RES;
	}

	/**
	 * Validate the CRE
	 */
	private function validateCRE()
	{
		$cre_list = \CRE::getEngineList();

		$cre_want = strtolower(trim($_POST['cre']));
		$cre_info = $cre_list[ $cre_want ];

		if (!empty($cre_info)) {
			return $cre_info;
		}

		return false;

	}
}
