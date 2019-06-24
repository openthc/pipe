<?php
/**
 * A Module contains a Slim Group
 */

namespace App\Module;

class Lab extends \OpenTHC\Module\Base
{
	/**
		@param $a App
	*/
	function __invoke($a)
	{
		$a->get('', function($REQ, $RES, $ARG) {
			return _from_cre_file('lab/search.php', $RES, $ARG);
		});

		$a->get('/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('lab/single.php', $RES, $ARG);
		});

	}
}
