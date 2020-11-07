<?php
/**
 * View the Logs from the STEM service
 */

use Edoceo\Radix\DB\SQL;

if (empty($_GET['d'])) {
	$text = <<<TEXT
The PIPE Log Parameters

Data File:
  d= <system>/<date>/<hash>

Formatting:
  f= normal* | pretty

Query:
  q= search query

Records:
  o= offset 0*|int
  l= limit  20*|int


Example:

  https://{$_SERVER['SERVER_NAME']}/log?d=biotrack%2F20140101%2FHASH1234&f=pretty&q=Blue%20Dream&o=100&l=10

TEXT;
	_exit_text($text);
}


$file = $_GET['d'];
if (!preg_match('/^(biotrack|leafdata|metrc)\/(\d+)\/(\w+)/', $file)) {
	_exit_text("Invalid Log File\nUse '\$system/\$date/\$hash' pattern", 400);
}

$sql_file = sprintf('%s/var/%s.sqlite', APP_ROOT, $file);
if (!is_file($sql_file)) {
	_exit_text("No File: $sql_file", 400);
}

$dbc = new SQL('sqlite:' . $sql_file);

?>
<html>
<head>
<title>Log</title>
<style>
body {
	font-family: monospace;
	margin: 0;
	padding: 0;
}
pre {
	background: #333;
	color: #f0f0f0;
	margin: 0;
	padding: 0;
	/* overflow: hidden; */
	/* white-space: nowrap; */
	white-space: pre-wrap;
	word-wrap: break-word;
}
table {
	margin: 0;
	padding: 0;
	width: 100%;
}
td {
	font-size: 8pt;
	vertical-align: top;
}
td.r {
	text-align: right;
}
tr.tr1:hover {
	background: #ff0;
}
tr.tr2 {
	display: none;
}
tr.tr3 {
	display: none;
}
</style>
<script src="https://cdn.openthc.com/zepto/1.2.0/zepto.js" integrity="sha256-vrn14y7WH7zgEElyQqm2uCGSQrX/xjYDjniRUQx3NyU=" crossorigin="anonymous"></script>
</head>
<body>
<table>
<thead>
	<tr>
		<th>Date</th>
		<th>Path</th>
		<th>HTTP Status</th>
	</tr>
</thead>
<tbody>
<?php

$arg = [];
$sql = 'SELECT * FROM log_audit';
if (!empty($_GET['q'])) {
	$sql.= ' WHERE req LIKE :q0 OR res LIKE :q1';
	$arg[':q0'] = sprintf('%%%s%%', $_GET['q']);
	$arg[':q1'] = sprintf('%%%s%%', $_GET['q']);
}
$sql.= ' ORDER BY cts DESC limit 50';

$idx = 0;
$res = $dbc->fetchAll($sql, $arg);
foreach ($res as $rec) {

	$idx++;

	$len = strlen($rec['res']);

	$req = strtok($rec['req'], "\n");
	$res = strtok($rec['res'], "\n");

	echo sprintf('<tr class="tr1" id="row-%d-1">', $idx);
	echo '<td>' . _date('m/d H:i:s', $rec['cts']) . '</td>';
	echo '<td>' . h($req) . '</td>';
	echo '<td class="r">' . h($res) . '</td>';
	echo '<td class="r">' . strlen($rec['res']) . ' bytes</td>';
	echo '</tr>';

	$req = explode("\r\n\r\n", $rec['req']);
	if ('pretty' == $_GET['f']) {
		if (!empty($req[1])) {
			$req[1] = json_decode($req[1], true);
			$req[1] = json_encode($req[1], JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
	}

	echo sprintf('<tr class="tr2" id="row-%d-2">', $idx);
	echo '<td colspan="4">';
	echo '<pre>';
	echo h(_view_data_scrub($req[0]));
	echo "\n";
	// echo '\r\n';
	echo "\n";
	echo h(_view_data_scrub($req[1]));
	echo '</pre>';
	echo '</td>';
	echo '</tr>';

	$res = explode("\r\n\r\n", $rec['res']);
	if ('pretty' == $_GET['f']) {
		if (!empty($res[1])) {
			$res[1] = json_decode($res[1], true);
			$res[1] = json_encode($res[1], JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
	}

	echo sprintf('<tr class="tr3" id="row-%d-3">', $idx);
	echo '<td colspan="4">';
	echo '<pre>';
	echo h(_view_data_scrub($res[0]));
	echo "\n";
	// echo '\r\n';
	echo "\n";
	echo h(_view_data_scrub($res[1]));
	echo '</pre>';
	echo '</td>';
	echo '</tr>';

	// echo '<td>' . h($rec['path']) . '</td>';
	// if ($len <= 2048) {
	// 	echo '<td><pre>' . h($rec['res']) . '</pre></td>';
	// } else {
	// 	echo '<td><pre>' . $len . '</pre></td>';
	// }

	echo '</tr>';
}
?>
</tbody>
</table>

<script>
var tr1 = null;
var tr2 = null;
var tr3 = null;
$(function() {
	$('.tr1').on('click', function() {

		var id1 = this.id;
		var id2 = id1.replace(/-1$/, '-2');
		var id3 = id1.replace(/-1$/, '-3');

		if (tr2) {
			tr2.hide();
		}
		if (tr3) {
			tr3.hide();
		}

		if (tr1 === this) {
			tr1 = null;
			return;
		}

		tr1 = this;

		tr2 = $('#' + id2);
		tr2.show();

		tr3 = $('#' + id3);
		tr3.show();
	});
})
</script>
</body>
</html>

<?php
/**
 * Sanatize REQ or RES
 */
function _view_data_scrub($x)
{
	$x = preg_replace('/^x\-mjf\-key:.+$/im', 'x-mjf-key: **redacted**', $x);
	$x = preg_replace('/^authorization:.+$/im', 'authorization: **redacted**', $x);

	$x = preg_replace('/^set\-cookie:.+$/im', 'set-cookie: **redacted**', $x);

	$x = preg_replace('/"transporter_name1":\s*"[^"]+"/im', '"transporter_name1":"**redacted**"', $x);
	$x = preg_replace('/"transporter_name2":\s*"[^"]+"/im', '"transporter_name2":"**redacted**"', $x);

	$x = preg_replace('/"vehicle_license_plate":\s*"[^"]+"/im', '"vehicle_license_plate":"**redacted**"', $x);
	$x = preg_replace('/"vehicle_vin":\s*"[^"]+"/im', '"vehicle_license_plate":"**redacted**"', $x);

	return $x;

}
