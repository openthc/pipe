<?php
/**
 * Load the RCE
 */

namespace App\Middleware;

class RCE
{

	public function __invoke($REQ, $RES, $NMW)
	{

		if (empty($_SESSION['rce'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication State [LMR#015]',
			), 403);
		}

		if (empty($_SESSION['rce-auth'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication State [LMR#022]',
			), 403);
		}

		$RES = $NMW($REQ, $RES);

		return $RES;
	}

}
