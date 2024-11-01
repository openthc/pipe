<?php
/**
 *
 * SPDX-License-Identifier: MIT
 *
 * Notes about the Auth module
 * The "program-key" cooresponds to a code that is a company object identifier
 * The "license-key" cooresponds to a code that is a license object identifier
 *
 * Licenses can belong to a company in a 1:M way
 * Companies can have different permissions to act on a license's object
 *
 */

namespace OpenTHC\Pipe\Test\CRE;

class Ping_Test extends \OpenTHC\Pipe\Test\Base
{
	/**
	 *
	 */
	public function test_ping_cre()
	{
		$cre_list = \OpenTHC\Pipe\CRE::getEngineList();
		$this->assertCount(31, $cre_list);

		foreach ($cre_list as $cre) {

			$cre_pipe = trim(OPENTHC_TEST_ORIGIN, '/');
			$cre_path = parse_url($cre['server'], PHP_URL_HOST);

			$url = sprintf('%s/%s/%s/ping', $cre_pipe, $cre['engine'], $cre_path);
			// echo "PING: $url\n";

			$req = _curl_init($url);
			$head = [
				'content-type: application/json',
				sprintf('openthc-contact-id: %s', $_ENV['OPENTHC_TEST_CONTACT_ID']),
				sprintf('openthc-company-id: %s', $_ENV['OPENTHC_TEST_COMPANY_ID']),
				sprintf('openthc-license-id: %s', $_ENV['OPENTHC_TEST_LICENSE_ID']),
			];
			curl_setopt($req, CURLOPT_HTTPHEADER, $head);

			$res = curl_exec($req);
			// var_dump($res);

			$inf = curl_getinfo($req);
			curl_close($req);

			$this->assertEquals(200, $inf['http_code'], sprintf('Failed Fetching %s', $url));
			$this->assertNotEmpty($res);
			$res = json_decode($res, true);
			$this->assertIsArray($res);
			$this->assertCount(2, $res);
			$this->assertIsArray($res['data']);
			// $this->assertNotEmpty($res['data']['cre']);
			// $this->assertNotEmpty($res['data']['cre_base']);
			// $this->assertNotEmpty($res['meta']['note']);
			// $this->assertNotEmpty($res['meta']['source']);

		}
	}
}
