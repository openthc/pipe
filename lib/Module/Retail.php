<?php
/**
	Retail Interface Module
*/

namespace App\Module;

class Retail extends \OpenTHC\Module\Base
{
	/**
		@param $a App
	*/
	function __invoke($a)
	{

		$a->get('', function($REQ, $RES, $ARG) {
			return _from_cre_file('retail/search.php', $RES, $ARG);
		});

		$a->post('', function($REQ, $RES, $ARG) {
			return _from_cre_file('retail/create.php', $RES, $ARG);
		});

		$a->get('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
			print_r($_POST);
			return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
		});

		$a->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
			return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
		});

		$a->delete('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
			return $RES->withJSON(array('status' => 'failure', 'detail' => 'Not Implemented'), 500);
		});

	}
}
