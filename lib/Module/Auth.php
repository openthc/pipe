<?php
/**
 * Authentication Stuffs
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Module;

class Auth extends \OpenTHC\Module\Base
{
	/**
	 *
	 */
	function __invoke($a)
	{
		$a->map(['GET', 'POST'], '/open', 'OpenTHC\Pipe\Controller\Auth\Open');

		$a->get('/shut', 'OpenTHC\Controller\Auth\Shut');

	}

}
