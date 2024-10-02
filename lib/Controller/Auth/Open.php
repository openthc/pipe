<?php
/**
 * Connect and Authenticate to a CRE
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller\Auth;

use OpenTHC\JWT;

class Open extends \OpenTHC\Controller\Auth\oAuth2
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		switch ($REQ->getMethod()) {
		case 'GET':

			// Magic v2024 Token
			// if ( ! empty($_GET['act'])) {
			// 	return $this->openACT($RES, $_GET['act'])
			// 		->withRedirect('/log')
			// 	;
			// }

			$r = $_GET['r'];
			switch ($r) {
			case '1':
			case 'r':
				$r = $_SERVER['HTTP_REFERER'];
				break;
			}

			$p = $this->getProvider($r);

			$arg = array(
				'scope' => 'contact company license cre pipe',
			);
			$url = $p->getAuthorizationUrl($arg);

			// Get the state generated for you and store it to the session.
			$_SESSION['oauth2-state'] = $p->getState();

			return $RES->withRedirect($url);

			break;

		case 'POST':
			switch ($_POST['a']) {
			case 'set-license':
				$_SESSION['License']['id'] = $_POST['license'];
				$_SESSION['cre-auth']['license'] = $_POST['license'];
				return $RES->withRedirect('/log');
				break;
			}
			break;
		}

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
