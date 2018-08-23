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

			$this->get('', function($REQ, $RES, $ARG) {
				return _from_rce_file('config/company/search.php', $RES, $ARG);
			});

		});

		// Product Type
		$a->get('/license-type', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/license-type.php', $RES, $ARG);
		});

		// License
		$a->group('/license', function() {

			$this->get('', function($REQ, $RES, $ARG) {
				return _from_rce_file('config/license/search.php', $RES, $ARG);
			});

			$this->get('/{guid}', function($REQ, $RES, $ARG) {
				return _from_rce_file('config/license/single.php', $RES, $ARG);
			});

			//$this->get('/company', function() {
			//	die('List Licenses by Company?');
			//});

		});

		// Contact / Users / Drivers / Employees
		$a->get('/contact', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/contact/search.php', $RES, $ARG);
		});

		//	$a->post('/{guid:[0-9a-f]+}/', function($REQ, $RES, $ARG) {
		//		require_once(APP_ROOT . '/v2016/contacts/update.php');
		//		return $RES;
		//	});

		// Products

		// Search
		$a->get('/product', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/product/search.php', $RES, $ARG);
		})
			//->add('App\Middleware\Output\CSV')
			//->add('App\Middleware\Output\CSV')
			;

		// Product Type
		$a->get('/product-type', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/product-type.php', $RES, $ARG);
		});


		// Create
		//$a->post('/product', function($REQ, $RES, $ARG) {
		//	return _from_rce_file('config/product/create.php', $RES, $ARG);
		//});

		// Single
		$a->get('/product/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/product/single.php', $RES, $ARG);
		});

		// Modify
		//$this->post('/product/{guid}', function($REQ, $RES, $ARG) {
		//	return _from_rce_file('config/product/update.php', $RES, $ARG);
		//});

		// Delete
		$a->delete('/product/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/product/delete.php', $RES, $ARG);
		});

		/*
			Strain
		*/
		// Search
		$a->get('/strain', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/strain/search.php', $RES, $ARG);
		});

		/*
			Vehicle
		*/
		// Search
		$a->get('/vehicle', function($REQ, $RES, $ARG) {
			//return _from_rce_file('config/vehicle/search.php', $RES, $ARG);
			return _exit_501($RES);
		});

		// For Areas/Rooms/Zones
		$a->get('/zone', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/zone/search.php', $RES, $ARG);
		});

		$a->get('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/zone/select.php', $RES, $ARG);
		});

		$a->post('/zone', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/zone/create.php', $RES, $ARG);
		});

		$a->post('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/zone/update.php', $RES, $ARG);
		});

		$a->delete('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('config/zone/delete.php', $RES, $ARG);
		});

	}

}
