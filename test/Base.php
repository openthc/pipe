<?php
/**
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Pipe\Test;

class Base_Case extends \PHPUnit\Framework\TestCase
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
			'base_uri' => rtrim(getenv('OPENTHC_TEST_BASE'), '/'),
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
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
	*/
	protected function _data_stash_get()
	{
		if (is_file($this->_tmp_file)) {
			$x = file_get_contents($this->_tmp_file);
			$x = json_decode($x, true);
			return $x;
		}
	}


	/**
	*/
	protected function _data_stash_put($d)
	{
		file_put_contents($this->_tmp_file, json_encode($d));
	}

}
