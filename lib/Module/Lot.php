<?php
/**
	A Module contains a Slim Group
*/

namespace App\Module;

class Lot
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

		$a->get('', function($REQ, $RES, $ARG) {
			return _from_rce_file('lot/search.php', $RES, $ARG);
		});

		$a->get('/history', function($REQ, $RES, $ARG) {
			return _from_rce_file('lot/history/search.php', $RES, $ARG);
		});

		//	$a->post('/', function($REQ, $RES, $ARG) {
		//		die('Create Inventory');
		//	});
		//
		//	// Combine Inventory to a new Type
		//	$a->post('/combine', function($REQ, $RES, $ARG) {
		//		return $RES->withJson(array(
		//			'ulid' => ULID::generate(), // '1234567890123456',
		//			'weight' => 123.45,
		//			'weight_unit' => 'g',
		//			'quantity' => 1,
		//		));
		//	});
		//
		//	// Convert Inventory to a new Type
		//	$a->post('/convert', function($REQ, $RES, $ARG) {
		//		return $RES->withJson(array(
		//			'code' => '123456',
		//			'weight' => '',
		//			'weight_unit' => 123.45,
		//			'quantity' => 1,
		//		));
		//	})->add(function($req, $RES) {
		//		// Enfore Type => Type Rules
		//		//die(print_r($_POST));
		//	});
		//

		// View Item
		$a->get('/{guid}', function($REQ, $RES, $ARG) {
			return _from_rce_file('lot/single.php', $RES, $ARG);
		});

		//	// Update Item
		//	$a->post('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		//		die('Update Inventory Item');
		//	});
		//
		//	// Delete Item
		//	$a->delete('/{guid:[0-9a-f]+}', function($REQ, $RES, $ARG) {
		//		// First send a 202, Pending
		//		// Second send a 204, Deleted/No Content
		//		die('Update Inventory Item');
		//	});

	}
}
