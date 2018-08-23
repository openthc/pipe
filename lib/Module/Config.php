<?php
/**
	A Module contains a Slim Group
*/

namespace App\Module;

class Config
{
	protected $_container;

	/**
	
	*/
	function __construct($c)
	{
		$this->_container = $c;
	}

	/**
		@param $a App
	*/
	function __invoke($a)
	{
		// Company
		$a->group('/company', function() {
	
			// GET default
			$this->any('', function($REQ, $RES, $ARG) {
				$RES = new \Response_From_File();
				return $RES->execute(sprintf('%s/config/company/search.php', $_SESSION['rbe-base']), $ARG);
			});
	
		});
	
		$a->group('/license', function() {
	
			$this->get('', function($REQ, $RES, $ARG) {
				$RES = new \Response_From_File();
				return $RES->execute(sprintf('%s/config/license/search.php', $_SESSION['rbe-base']), $ARG);
			});
	
			$this->get('/{guid}', function($REQ, $RES, $ARG) {
				$f = sprintf('%s/controller/%s/config/license/single.php', APP_ROOT, $_SESSION['rbe-base']);
				$RES = require_once($f);
				return $RES;
			});
	
			//$this->get('/company', function() {
			//	die('List Licenses by Company?');
			//});
	
		});
	
		// Contact / Users / Drivers / Employees
		$a->get('/contact', function($REQ, $RES, $ARG) {
			$RES = new \Response_From_File();
			return $RES->execute(sprintf('%s/config/contact/search.php', $_SESSION['rbe-base']), $ARG);
		});
	
		//	$a->post('/{guid:[0-9a-f]+}/', function($REQ, $RES, $ARG) {
		//		require_once(APP_ROOT . '/v2016/contacts/update.php');
		//		return $RES;
		//	});
	
		// Products
	
		// Search
		$a->get('/product', function($REQ, $RES, $ARG) {
			$RES = new \Response_From_File();
			return $RES->execute(sprintf('%s/config/product/search.php', $_SESSION['rbe-base']), $ARG);
		})
			//->add('App\Middleware\Output\CSV')
			//->add('App\Middleware\Output\CSV')
			;
	
		// Product Type
		$a->get('/product-type', function($REQ, $RES, $ARG) {
			$RES = new \Response_From_File();
			return $RES->execute(sprintf('%s/config/product-type.php', $_SESSION['rbe-base']), $ARG);
		});
	
	
		// Create
		//$a->post('/product', function($REQ, $RES, $ARG) {
		//	$f = sprintf('%s/controller/%s/config-product-create.php', APP_ROOT, $_SESSION['rbe-base']);
		//	$RES = require_once($f);
		//	return $RES;
		//});
	
		// Single
		$a->get('/product/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/config-product-single.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});
	
		// Modify
		//$this->post('/product/{guid}', function($REQ, $RES, $ARG) {
		//	$f = sprintf('%s/controller/%s/config-product-update.php', APP_ROOT, $_SESSION['rbe-base']);
		//	$RES = require_once($f);
		//	return $RES;
		//});
	
		// Delete
		$a->delete('/product/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/config-product-delete.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});
	
		/*
			Strain
		*/
		// Search
		$a->get('/strain', function($REQ, $RES, $ARG) {
			$RES = new \Response_From_File();
			return $RES->execute(sprintf('%s/config/strain/search.php', $_SESSION['rbe-base']), $ARG);
		});
	
		/*
			Vehicle
		*/
		// Search
		$a->get('/vehicle', function($REQ, $RES, $ARG) {
			//$f = sprintf('%s/controller/%s/vehicle/search.php', APP_ROOT, $_SESSION['rbe-base']);
			//$RES = require_once($f);
			return _exit_501($RES);
		});
	
		// For Areas/Rooms/Zones
		$a->get('/zone', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/zone/search.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		$a->get('/zone/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/zone/select.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		$a->post('/zone', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/zone/create.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		$a->post('/zone/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/zone/update.php', APP_ROOT, $_SESSION['rbe-base']);
			$RES = require_once($f);
			return $RES;
		});

		$a->delete('/zone/{guid}', function($REQ, $RES, $ARG) {
			$f = sprintf('%s/controller/%s/zone/delete.php', APP_ROOT, $_SESSION['rbe-base']);
			require_once($f);
			return $RES;
		});

	}

}