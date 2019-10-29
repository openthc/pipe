<?php
/**
	A Module contains a Slim Group
*/

namespace App\Module;

class Plant extends \OpenTHC\Module\Base
{
	/**
		@param $a App
	*/
	function __invoke($a)
	{
		// Search
		$a->get('', function($REQ, $RES, $ARG) {
			return _from_cre_file('plant/search.php', $RES, $ARG);
		});

		// Create
		$a->post('', function($REQ, $RES, $ARG) {
			return $RES->withJSON(array(
				'status' => 'failure',
				'detail' => 'Not Implemented [LMP#024]'
			), 501);
		});

		// Single
		$a->get('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
			return _from_cre_file('plant/single.php', $RES, $ARG);
		});

		// Update
		$a->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
			return _from_cre_file('plant/update.php', $RES, $ARG);
		});

		// Convenience Functions
		$a->post('/{guid:[0-9a-f]+}/move', function($REQ, $RES, $ARG) {
			return _from_cre_file('plant/update.php', $RES, $ARG);
		});

		//$a->post('/{guid:[0-9a-f]+}/collect', function($REQ, $RES, $ARG) {
		//	$f = sprintf('%s/controller/%s/plants-get.php', APP_ROOT, $_SESSION['cre-base']);
		//	require_once($f);
		//	return $RES;
		//});

	}
}
