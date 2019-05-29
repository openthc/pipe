<?php
/**
 * Ping the BioTrack Connexion
 */

use Edoceo\Radix;

$cre = \RCE::factory($_SESSION['cre']);

// Ping the host
$cre_host = parse_url($_SESSION['cre']['server'], PHP_URL_HOST);

$cre_host_ipv4 = dns_get_record($cre_host, DNS_A);
//$tmp = shell_exec(sprintf('/usr/bin/mtr -4 --report --report-cycles 4 --json %s', escapeshellarg($cre_host)));
//$cre_host_ipv4_route = json_decode($tmp, true);

$cre_host_ipv6 = dns_get_record($cre_host, DNS_AAAA);
if (count($cre_host_ipv6)) {
	$cre_host_ipv6_route = shell_exec(sprintf('/usr/bin/mtr -6 --report --report-cycles 4 --json %s', escapeshellarg($cre_host)));
} else {
	$cre_host_ipv6 = null;
	$cre_host_ipv6_route = null;
}


// HTTP to the host
$req = _curl_init($_SESSION['cre']['server']);
curl_setopt($req, CURLOPT_CERTINFO, true);
$res = curl_exec($req);
$inf = curl_getinfo($req);
if (200 != $inf['http_code']) {
	// Inspect!
	return $RES->withJSON(array(
		'status' => 'failure',
		'detail' => sprintf('HTTP Failure Code: "%d"', $inf['http_code']),
	));
}

$ssl_info = curl_getinfo($req, CURLINFO_CERTINFO);

$ret_ping = array();

$arg = array(
	'data' => array(),
);

$obj_list = $cre->listSyncObjects();
$obj_list = array_keys($obj_list);
foreach ($obj_list as $obj) {
	$arg['data'][] = array(
		'table' => $obj,
		'transaction_start' => 0,
	);
}

$res = $cre->sync_check($arg);
switch (intval($res['success'])) {
case 0:
	return $RES->withJson(array(
		'status' => 'failure',
		'detail' => 'CPB#039: CRE Error',
		'result' => $res,
	), 400);
}
$ret_ping['sync_check'] = $res;

$have = $want = 0;

foreach ($obj_list as $obj) {

	$want++;

	$sfn = sprintf('sync_%s', $obj);
	$res = $cre->$sfn(array(
		'min' => 999999999,
		'max' => 999999999 + 1,
	));

	$ret_ping[$sfn] = $res;
}

// Can See QA?
// $ret_ping['inventory_qa_check_all'] = $cre->inventory_qa_check_all(9999999999999999);
// $ret_ping['inventory_qa_check'] = $cre->inventory_qa_check(9999999999999999);

// And the Other Four Magic Things
// Need to Known Location First!
// $ret_ping['inventory_manifest_lookup'] = $cre->inventory_manifest_lookup('123456');
// $ret_ping['inventory_transfer_outbound_return_lookup'] = $cre->inventory_transfer_outbound_return_lookup('123456');

return $RES->withJson(array(
	'status' => 'success',
	'result' => array(
		'cre' => array(
			'service' => $_SESSION['cre']['server'],
		),
		'host' => $cre_host,
		'ipv4' => $cre_host_ipv4,
		'ipv4_route' => $cre_host_ipv4_route,
		'ipv6' => $cre_host_ipv6,
		'ipv6_route' => $cre_host_ipv6_route,
		'ssl-cert' => $ssl_info,
		'_session_id' => session_id(),
		'_session' => $_SESSION,
		'_source' => $ret_ping,
	),
), 200, JSON_PRETTY_PRINT);
