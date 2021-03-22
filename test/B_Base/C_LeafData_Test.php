<?php
/**
 * LeafData PIPE Fire Test
 */

namespace Test\B_Base;

class C_LeafData_Test extends \Test\Base_Case
{
	function x_test_license()
	{
		$req = $this->_curl_init('/mmes');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);
		$this->assertNotEmpty($res);

		$res = json_decode($res, true);
		$this->assertNotEmpty($res);
		$this->assertCount(3145, $res);
	}

	function test_contact()
	{
		$req = $this->_curl_init('/users');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_section()
	{
		$req = $this->_curl_init('/areas');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_product()
	{
		$req = $this->_curl_init('/inventory_types');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_variety()
	{
		$req = $this->_curl_init('/strains');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_crop()
	{
		$req = $this->_curl_init('/plants');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_lot()
	{
		$req = $this->_curl_init('/inventories');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_lot_delta()
	{
		$req = $this->_curl_init('/inventory_adjustments');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_lab_result()
	{
		$req = $this->_curl_init('/lab_results');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_b2b()
	{
		$req = $this->_curl_init('/inventory_transfers');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_b2c()
	{
		$req = $this->_curl_init('/sales');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_batch()
	{
		$req = $this->_curl_init('/batches');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function test_waste()
	{
		$req = $this->_curl_init('/disposals');
		$res = curl_exec($req);
		$res_info = curl_getinfo($req);

		$res = $this->assertValidResponse($res);
	}

	function assertValidResponse($res)
	{
		$this->assertNotEmpty($res);
		$res = json_decode($res, true);
		$this->assertNotEmpty($res);
		$this->assertCount(9, $res);

		$this->assertArrayHasKey('current_page', $res);
		$this->assertArrayHasKey('data', $res);
		$this->assertArrayHasKey('from', $res);
		$this->assertArrayHasKey('last_page', $res);
		$this->assertArrayHasKey('next_page_url', $res);
		$this->assertArrayHasKey('per_page', $res);
		$this->assertArrayHasKey('prev_page_url', $res);
		$this->assertArrayHasKey('to', $res);
		$this->assertArrayHasKey('total', $res);

		$ret = $res['data'];
		$this->assertIsArray($ret);

		return $ret;
	}

	function _curl_init($path)
	{
		$path = ltrim($path, '/');

		$url = sprintf('https://%s/leafdata/wa/test/%s', getenv('OPENTHC_TEST_HOST'), $path);
		$req = _curl_init($url);
		$head = [
			'content-type: application/json',
			// sprintf('host: %s', $this->_api_host),
			sprintf('x-mjf-mme-code: %s', $_ENV['license']),
			sprintf('x-mjf-key: %s', $_ENV['license-key']),
		];
		curl_setopt($req, CURLOPT_HTTPHEADER, $head);

		return $req;

	}

}
