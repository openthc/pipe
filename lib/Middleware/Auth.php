<?php
/**
 * Ensure we have an Authenticated Session
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Middleware;

class Auth extends \OpenTHC\Middleware\Base
{
	/**
	 *
	 */
	public function __invoke($REQ, $RES, $NMW)
	{
		// Authenticated
		if (empty($_SESSION['Contact']['id'])) {
			return $RES->withRedirect(sprintf('/auth?%s', http_build_query([
				'e' => 'LMA-020',
				'r' => $_SERVER['REQUEST_URI']
			])));
		}

		switch ($_SESSION['Contact']['stat']) {
		// case \App\Contact::STAT_LIVE:
		case 200:
			// OK
			break;
		default:
			return $RES->withRedirect('/auth?e=LMA-031');
		}

		return $NMW($REQ, $RES);

	}
}
