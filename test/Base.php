<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test;

class Base extends \OpenTHC\Test\Base //\PHPUnit\Framework\TestCase
{
	protected $_cre;
	protected $_tmp_file = '/tmp/pipe-test-case.dat';


	protected function setUp() : void
	{
		$this->httpClient = $this->_api();
	}

	/**
	*/
	protected function _api()
	{
		// create our http client (Guzzle)
		$c = new \GuzzleHttp\Client(array(
			'base_uri' => rtrim(OPENTHC_TEST_ORIGIN, '/'),
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'cookies' => true,
			'http_errors' => false,
			'headers' => [
				'openthc-contact-id' => $_ENV['OPENTHC_TEST_CONTACT_ID'],
				'openthc-company-id' => $_ENV['OPENTHC_TEST_COMPANY_ID'],
				'openthc-license-id' => $_ENV['OPENTHC_TEST_LICENSE_ID'],
			],
			'request.options' => array(
				'exceptions' => false,
			),
		));

		return $c;
	}


	/**
	*/
	protected function _post($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'form_params' => $a ]);
		return $res;
	}


	/**
	*/
	protected function _post_json($u, $a)
	{
		$res = $this->httpClient->post($u, [ 'json' => $a ]);
		return $res;
	}

	/**
	 *
	 */
	function assertValidResponse($res, $code_expect=200, $type_expect='application/json', $dump=null) : array
	{
		$ret = parent::assertValidResponse($res, $code_expect, $type_expect, $dump);

		switch ($type_expect) {
		case 'application/json':
			$this->assertIsArray($ret);
			// $this->assertArrayHasKey('data', $ret);
			// $this->assertArrayHasKey('meta', $ret);
			break;
		}

		return $ret;
	}

}
