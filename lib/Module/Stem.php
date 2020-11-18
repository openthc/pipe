<?php
/**
 * @deprecated
 * Slim Module for the Stem interface
 */

namespace App\Module;

class Stem extends \OpenTHC\Module\Base
{
	function __invoke($app)
	{
		$app->map([ 'GET', 'POST' ], '/biotrack/{system}', 'App\Controller\BioTrack')->add('OpenTHC\Middleware\Session');

		$app->map([ 'GET', 'POST', 'DELETE' ], '/leafdata/{path:.*}', 'App\Controller\Leafdata');

		$app->map([ 'GET', 'POST', 'PUT', 'DELETE' ], '/metrc/{path:.*}', 'App\Controller\METRC');
	}
}
