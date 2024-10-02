<?php
/**
 * oAuth2 Returns Here
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Controller\Auth;

use Edoceo\Radix\Session;

class Back extends \OpenTHC\Controller\Auth\oAuth2
{
	/**
	 *
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$p = $this->getProvider();

		$Profile = $this->getProfileFromToken($p);
		if (empty($Profile)) {
			_exit_text('Invalid Request [CAB-023]', 400);
		}

		// Scope Permission?
		if ( ! in_array('pipe', $Profile['scope'])) {
			_exit_json([
				'data' => $Profile,
				'meta' => [ 'note' => 'Scope Not Permitted [CAB-030]' ]
			], 403);
		}

		$_SESSION['email'] = $Profile['Contact']['username'];
		$_SESSION['Contact'] = $Profile['Contact'];
		$_SESSION['Company'] = $Profile['Company'];

		Session::flash('info', sprintf('Signed in as: %s', $_SESSION['Contact']['username']));

		$r = $_GET['r'];
		if (empty($r)) {
			$r = '/log';
		}

		return $RES->withRedirect($r);

	}
}
