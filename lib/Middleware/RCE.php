<?php
/**
 * Load the RCE
 */

namespace App\Middleware;

class RCE
{

	public function __invoke($REQ, $RES, $NMW)
	{

		if (empty($_SESSION['cre'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication State [LMR#015]',
			), 403);
		}

		if (empty($_SESSION['cre-auth'])) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Invalid Authentication State [LMR#022]',
			), 403);
		}

		$RES = $NMW($REQ, $RES);

		return $RES;
	}

}
