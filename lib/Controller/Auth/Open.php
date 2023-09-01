<?php
/**
 * Connect and Authenticate to a CRE
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Bong\Controller\Auth;

use OpenTHC\Bong\CRE;
use OpenTHC\JWT;

class Open extends \OpenTHC\Controller\Base
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		switch ($REQ->getMethod()) {
		case 'GET':

			if ( ! empty($_GET['jwt'])) {
				return $this->openJWT($RES, $_GET['jwt'])
					->withRedirect('/browse')
				;
			}

			$RES = $this->renderForm($REQ, $RES, $ARG);

			break;

		case 'POST':
			switch ($_POST['a']) {
			case 'set-license':
				$_SESSION['License']['id'] = $_POST['license'];
				$_SESSION['cre-auth']['license'] = $_POST['license'];
				return $RES->withRedirect('/browse');
				break;
			}
			return $this->connect($REQ, $RES, $ARG);
			break;
		}

	}

	/**
	 * Connect
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
				'meta' => [ 'note' => sprintf('Invalid CRE: "%s" [CAC-017]', $_POST['cre']) ],
			], 400);
		}

		$_SESSION['cre'] = $cre;
		$_SESSION['cre-auth'] = [];
		$_SESSION['sql-conn'] = null;
		$_SESSION['sql-name'] = null;

		switch ($cre['engine']) {
		case 'biotrack':
			$RES = $this->_biotrack($RES);
			break;
		case 'ccrs':
			$RES = $this->_ccrs($RES);
			break;
		case 'leafdata':
			$RES = $this->_leafdata($RES);
			break;
		case 'metrc':
			$RES = $this->_metrc($RES);
			break;
		}

		if (200 != $RES->getStatusCode()) {
			return $RES;
		}

		// From our webform
		if ('auth-web' == $_POST['a']) {
			return $RES->withRedirect('/browse');
		}

		return $RES;
	}

	/**
	 *
	 */
	private function openJWT($RES, $jwt)
	{
		try {
			$chk = JWT::decode($jwt);

			// Mostly Real Now
			$_SESSION['Company'] = [
				'id' => $chk['company'],
			];
			$_SESSION['Contact'] = [
				'id' => $chk['sub'],
			];
			$_SESSION['License'] = [
				'id' => $chk['license'],
			];

			if (empty($_SESSION['Company']['id'])) {
				return $RES->withJSON(['meta' => [ 'note' => 'Invalid Company' ]], 400);
			}
			if (empty($_SESSION['Contact']['id'])) {
				return $RES->withJSON(['meta' => [ 'note' => 'Invalid Contact' ]], 400);
			}
			if (empty($_SESSION['License']['id'])) {
				return $RES->withJSON(['meta' => [ 'note' => 'Invalid License' ]], 400);
			}

		} catch (\Exception $e) {
			// Ignore
			__exit_text($e->getMessage(), 500);
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
		$data['cre_list'] = \OpenTHC\CRE::getEngineList();
		$data['cre_code'] = $_SESSION['cre']['code'];
		$data['cre_company'] = $_SESSION['cre-auth']['company'];
		$data['cre_license'] = $_SESSION['cre-auth']['license'];
		$data['cre_service_key'] = $_SESSION['cre-auth']['service-key'];
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

		$html = $this->render('auth.php', $data);

		return $RES->write($html);

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
				'result' => session_id(),
				'meta' => [
					'note' => 'Session Continues',
				],
			));

			return $RES;
		}

		$cfg = $_SESSION['cre'];
		$cfg['company'] = trim($_POST['company']);
		$cfg['username'] = trim($_POST['username']);
		$cfg['password'] = trim($_POST['password']);
		$cfg['session-id'] = '';

		$cre = \OpenTHC\CRE::factory($cfg);
		$res = $cre->auth();
		// $res = $cre->ping();

		// @todo Detect a 500 Layer Response from BioTrack

		switch ($res['code']) {
		case 0:
		case 403:

			return $RES->withJson(array(
				'data' => $res,
				'meta' => [ 'note' => 'Invalid Username or Password [CAO-155]' ],
			), 400);

			break;

		case 200:
		case 201:
		case 1:

			$cfg['session-id'] = $res['data']['sessionid'];

			$_SESSION['cre-auth'] = $cfg;
			$_SESSION['sql-name'] = sprintf('openthc_bong_%s', md5($_SESSION['cre-auth']['company']));

			return $RES->withJson(array(
				'data' => session_id(),
				'meta' => [ 'note' => 'Session Established' ],
			));

			break;

		default:
			throw new \Exception('Invalid Response from BioTrack');
		}

		return $RES;

	}

	/**
	 * "Authenticate" to CCRS
	 */
	function _ccrs($RES)
	{
		$_SESSION['cre-auth']['company'] = $_POST['company'];
		$_SESSION['cre-auth']['license'] = $_POST['license'];
		$_SESSION['sql-name'] = 'openthc_bong_ccrs';

		// $hash = md5($_POST['service-key'] . $_POST['company'] . $_POST['license']);

		$dbc = _dbc();

		$C = $dbc->fetchRow('SELECT * FROM company WHERE id = :c0', [
			':c0' => $_SESSION['cre-auth']['company']
		]);
		if (empty($C['id'])) {
			$dbc->insert('company', [
				'id' => $_SESSION['cre-auth']['company']
			]);
		}

		$L = $dbc->fetchRow('SELECT * FROM license WHERE id = :l0', [
			':l0' => $_SESSION['cre-auth']['license']
		]);
		if (empty($L['id'])) {
			$dbc->insert('license', [
				'id' => $_SESSION['cre-auth']['license'],
				'stat' => 200,
				'code' => $_SESSION['cre-auth']['license'],
				'name' => $_SESSION['cre-auth']['license'],
			]);
		}

		return $RES->withJSON([
			'data' => session_id(),
			'meta' => [],
		]);

	}

	/**
	 * Connect to a LeafData System
	 */
	function _leafdata($RES)
	{
		$lic = trim($_POST['license']);
		$lic = strtoupper($lic);

		$key = trim($_POST['license-key']);

		if (!preg_match('/^(G|J|L|M|R|T)\w+$/', $lic)) {
			return $RES->withJSON(array(
				'meta' => [ 'note' => 'Invalid License [CAO-209]' ],
			), 400);
		}

		if (empty($key)) {
			return $RES->withJSON(array(
				'meta' => [ 'note' => 'Invalid API Key [CAO-216]' ],
			), 400);
		}

		$_SESSION['cre-auth'] = array(
			'license' => $lic,
			'license-key' => $key,
		);

		$cfg = array_merge($_SESSION['cre'], $_SESSION['cre-auth']);

		$cre = \OpenTHC\CRE::factory($cfg);
		$res = $cre->ping();

		if (empty($res)) {
			return $RES->withJSON(array(
				'meta' => [ 'note' => 'Invalid License or API Key [CAO-239]' ],
			), 403);
		}

		$_SESSION['sql-name'] = sprintf('openthc_bong_%s', md5($_SESSION['cre-auth']['license']));

		return $RES->withJSON([
			'data' => session_id(),
			'meta' => [],
		]);

	}

	/**
	 * Connect to a METRC system
	 */
	function _metrc($RES)
	{
		$_SESSION['cre-auth'] = array(
			'license' => $_POST['license'],
			'service-key' => $_POST['service-key'],
			'license-key' => $_POST['license-key'],
		);

		$cfg = $_SESSION['cre'];
		$cfg = array_merge($cfg, $_SESSION['cre-auth']);

		$cre = \OpenTHC\CRE::factory($cfg);

		// $res = $cre->ping();

		$res = $cre->license()->search();
		if (200 == $res['code']) {
			$_SESSION['license-list'] = $res['data'];
		}

		if ($res) {

			$_SESSION['sql-name'] = sprintf('openthc_bong_%s', md5($_SESSION['cre-auth']['license']));

			$ret = [
				'data' => session_id(),
			];
			return $RES->withJSON($ret);
		}

		return $RES->withJSON(array(
			'meta' => [ 'note' => 'Failed to Connect to METRC' ],
		), 500);

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
		$cre_want = strtolower(trim($_POST['cre']));
		$cre_info = \OpenTHC\CRE::getEngine($cre_want);

		if ( ! empty($cre_info)) {
			return $cre_info;
		}

		return false;

	}
}
