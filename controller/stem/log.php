<?php
/**
 * View the Logs from the STEM service
 */

$date = date('Ymd');
if (!empty($_GET['date'])) {
	$date = $_GET['date'];
}
$hash = $_GET['hash'];

$sql_file = sprintf('%s/var/stem%s/%s.sqlite', APP_ROOT, $date, $hash);
if (!is_file($sql_file)) {
	_exit_text('No File', 400);
}

$dbc = new \Edoceo\Radix\DB\SQL('sqlite:' . $sql_file);

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
tr:hover {
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
<!-- <script src="https://cdn.openthc.com/jquery/3.4.1/jquery.js"></script> -->
</head>
<body>
<table>
<?php
$idx = 0;
$res = $dbc->fetchAll('SELECT * FROM log_audit ORDER BY cts DESC limit 20');
foreach ($res as $rec) {

	$idx++;

	$len = strlen($rec['res']);

	$req = strtok($rec['req'], "\n");
	$res = strtok($rec['res'], "\n");

	echo sprintf('<tr class="tr1" id="row-%d-1">', $idx);
	echo '<td>' . _date('H:i:s', $rec['cts']) . '</td>';
	echo '<td>' . h($req) . '</td>';
	echo '<td>' . h($res) . '</td>';
	echo '<td>' . $rec['code'] . '</td>';
	echo '</tr>';

	echo sprintf('<tr class="tr2" id="row-%d-2">', $idx);
	echo '<td colspan="4">';
	echo '<pre>' . h($rec['req']) . '</pre>';
	echo '</td>';
	echo '</tr>';

	echo sprintf('<tr class="tr3" id="row-%d-3">', $idx);
	echo '<td colspan="4">';
	echo '<pre>' . h($rec['res']) . '</pre>';
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
</table>
<script>
var tr2 = null;
var tr3 = null;
$(function() {
	$('.tr1').on('click', function() {

		if (tr2) {
			tr2.hide();
		}
		if (tr3) {
			tr3.hide();
		}

		var id1 = this.id;
		var id2 = id1.replace(/-1$/, '-2');
		var id3 = id1.replace(/-1$/, '-3');

		tr2 = $('#' + id2);
		tr2.show();

		tr3 = $('#' + id3);
		tr3.show();
	});
})
</script>
</body>
</html>
