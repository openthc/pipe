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
		$a->map(['GET', 'POST'], '', 'OpenTHC\Pipe\Controller\Auth\Open');
		$a->get('/back', 'OpenTHC\Pipe\Controller\Auth\Back');
		$a->get('/shut', 'OpenTHC\Controller\Auth\Shut');
	}

}
