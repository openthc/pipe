<?php
/**
	A Module contains a Slim Group
*/

namespace App\Module;

class Config extends \OpenTHC\Module\Base
{
	/**
		@param $a App
	*/
	function __invoke($a)
	{

		// Company
		$a->group('/company', function() {

			$this->get('', function($REQ, $RES, $ARG) {
				return _from_cre_file('company/search.php', $RES, $ARG);
			});

		});

		// Product Type
		$a->get('/license-type', function($REQ, $RES, $ARG) {
			return _from_cre_file('license-type.php', $RES, $ARG);
		});

		// License
		$a->group('/license', function() {

			$this->get('', function($REQ, $RES, $ARG) {
				return _from_cre_file('license/search.php', $RES, $ARG);
			});

			$this->get('/{guid}', function($REQ, $RES, $ARG) {
				return _from_cre_file('license/single.php', $RES, $ARG);
			});

			//$this->get('/company', function() {
			//	die('List Licenses by Company?');
			//});

		});

		// Contact / Users / Drivers / Employees
		$a->get('/contact', function($REQ, $RES, $ARG) {
			return _from_cre_file('contact/search.php', $RES, $ARG);
		});

		//	$a->post('/{guid:[0-9a-f]+}/', function($REQ, $RES, $ARG) {
		//		return _from_cre_file('contacts/update.php', $RES, $ARG);
		//	});

		// Products

		// Search
		$a->get('/product', function($REQ, $RES, $ARG) {
			return _from_cre_file('product/search.php', $RES, $ARG);
		})
			//->add('App\Middleware\Output\CSV')
			//->add('App\Middleware\Output\CSV')
			;

		// Product Type
		$a->get('/product-type', function($REQ, $RES, $ARG) {
			return _from_cre_file('product-type.php', $RES, $ARG);
		});


		// Create
		//$a->post('/product', function($REQ, $RES, $ARG) {
		//	return _from_cre_file('product/create.php', $RES, $ARG);
		//});

		// Single
		$a->get('/product/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('product/single.php', $RES, $ARG);
		});

		// Modify
		//$this->post('/product/{guid}', function($REQ, $RES, $ARG) {
		//	return _from_cre_file('product/update.php', $RES, $ARG);
		//});

		// Delete
		$a->delete('/product/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('product/delete.php', $RES, $ARG);
		});

		/*
			Strain
		*/
		// Search
		$a->get('/strain', function($REQ, $RES, $ARG) {
			return _from_cre_file('strain/search.php', $RES, $ARG);
		});
		// Single
		$a->get('/strain/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('strain/single.php', $RES, $ARG);
		});

		/*
			Vehicle
		*/
		// Search
		$a->get('/vehicle', function($REQ, $RES, $ARG) {
			return _from_cre_file('vehicle/search.php', $RES, $ARG);
		});

		// For Areas/Rooms/Zones
		$a->get('/zone', function($REQ, $RES, $ARG) {
			return _from_cre_file('zone/search.php', $RES, $ARG);
		});

		$a->get('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('zone/select.php', $RES, $ARG);
		});

		$a->post('/zone', function($REQ, $RES, $ARG) {
			return _from_cre_file('zone/create.php', $RES, $ARG);
		});

		$a->post('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('zone/update.php', $RES, $ARG);
		});

		$a->delete('/zone/{guid}', function($REQ, $RES, $ARG) {
			return _from_cre_file('zone/delete.php', $RES, $ARG);
		});

	}

}
