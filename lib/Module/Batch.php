<?php
/**
	Routes for Batch shit
*/

namespace App\Module;

class Batch extends \OpenTHC\Module\Base
{
	/**
		@param $a App
	*/
	function __invoke($a)
	{
		$a->get('', function($REQ, $RES, $ARG) {
			return _from_rce_file('batch/search.php', $RES, $ARG);
		});

		// Single
		$a->get('/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('batch/single.php', $RES, $ARG);
		});

	}

}