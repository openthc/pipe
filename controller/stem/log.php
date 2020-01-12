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
	_exit_text("No File: $sql_file", 400);
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
<?php
$idx = 0;
$sql = <<<SQL
SELECT * FROM log_audit
ORDER BY cts DESC limit 50
SQL;
$res = $dbc->fetchAll($sql);
foreach ($res as $rec) {

	$idx++;

	$len = strlen($rec['res']);

	$req = strtok($rec['req'], "\n");
	$res = strtok($rec['res'], "\n");

	echo sprintf('<tr class="tr1" id="row-%d-1">', $idx);
	echo '<td>' . _date('H:i:s.v', $rec['cts']) . '</td>';
	echo '<td>' . h($req) . '</td>';
	echo '<td>' . h($res) . '</td>';
	echo '<td>' . $rec['code'] . '</td>';
	// echo '<td>' . $rec['err'] . '</td>';
	echo '</tr>';

	$req = explode("\r\n\r\n", $rec['req']);
	$req[0] = preg_replace('/^x\-mjf\-key:.+$/im', 'x-mjf-key: ********', $req[0]);
	$req[0] = preg_replace('/^authorization:.+$/im', 'authorization: ********', $req[0]);
	// if ('pretty' == $_GET['f']) {
	// 	if (!empty($req[1])) {
	// 		$req[1] = json_decode($req[1], true);
	// 		$req[1] = json_encode($req[1], JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	// 	}
	// }

	echo sprintf('<tr class="tr2" id="row-%d-2">', $idx);
	echo '<td colspan="4">';
	echo '<pre>';
	echo h($req[0]);
	echo "\n";
	// echo '\r\n';
	echo "\n";
	echo h($req[1]);
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
	echo h($res[0]);
	echo "\n";
	// echo '\r\n';
	echo "\n";
	echo h($res[1]);
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
