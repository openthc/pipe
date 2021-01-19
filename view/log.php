<?php
/**
 *
 */

$tz = \OpenTHC\Config::get('tz');
$tz = new \DateTimezone($tz);

$l = $this->query_limit;
$offset = intval($_GET['o']);

$link_back = http_build_query(array_merge($_GET, [ 'o' => max(0, $offset - $l) ] ));
$link_next = http_build_query(array_merge($_GET, [ 'o' => $offset + $l ] ));

?>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC PIPE">
<link rel="stylesheet" href="/css/app.css" crossorigin="anonymous">
<title>Log Search :: OpenTHC PIPE</title>
</head>
<body>

<form autocomplete="off">
<div class="search-filter">
	<div>
		<input autocomplete="off" name="l" placeholder="license hash" value="<?= h($_GET['l']) ?>">
	</div>
	<div>
		<input autocomplete="off" autofocus name="q" placeholder="search" value="<?= h($_GET['q']) ?>">
	</div>
	<div>
		<input name="dt0" placeholder="after" type="date" value="<?= h($_GET['dt0']) ?>">
	</div>
	<div>
		<input name="dt1" placeholder="before" type="date" value="<?= h($_GET['dt1']) ?>">
	</div>
	<div>
		<button type="submit">Go</button>
	</div>
	<div>
		<button formtarget="_blank" name="a" type="submit" value="snap">Snap</button>
	</div>
	<div style="padding: 0 0.50rem;">
		<a href="?<?= $link_back ?>">Back</a> | <a href="?<?= $link_next ?>">Next</a>
	</div>
</div>
</form>

<div class="sql-debug"><?= h(trim($this->sql_debug)) ?></div>

<table>
<thead>
	<tr>
		<th></th>
		<th>Date</th>
		<th>Path</th>
		<th>Status</th>
		<th>Size</th>
	</tr>
</thead>
<tbody>
<?php
$idx = $offset;
foreach ($res as $rec) {

	$idx++;

	$dt0 = new \DateTime($rec['req_time']);
	$dt0->setTimezone($tz);

	$dt1 = new \DateTime($rec['res_time']);
	$dt1->setTimezone($tz);

	$diX = $dt1->diff($dt0);
	$res_wait = ($diX->i * 60) + $diX->s + $diX->f;

	$len = strlen($rec['res_body']);

	// Re-Code
	if (!empty($rec['req_body'])) {
		$x = json_decode($rec['req_body'], true);
		if (!empty($x)) {
			$rec['req_body'] = json_encode($x, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
	}

	if (!empty($rec['res_body'])) {
		$rec['res_body'] = json_decode($rec['res_body'], true);
		$rec['res_body'] = json_encode($rec['res_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	$req = strtok($rec['req_head'], "\n");
	$res = strtok($rec['res_head'], "\n");

	printf('<tr class="tr1" id="row-%d-1">', $idx);
	printf('<td class="c"><a href="/log?id=%s">%d</a></td>', $rec['id'], $idx);
	// echo '<td>' . _date('m/d H:i:s', $rec['req_time']) . '</td>';
	// echo '<td>' . h($rec['req_time']) . '</td>';
	printf('<td title="%s">%s [%0.1f s]</td>', h($rec['req_time']), $dt0->format('m/d H:i:s'), $res_wait);
	echo '<td>' . h($req) . '</td>';
	echo '<td>' . h($res) . '</td>';
	echo '<td class="r">' . strlen($rec['res_body']) . ' bytes</td>';
	echo '</tr>';

	printf('<tr class="tr2" id="row-%d-2">', $idx);
	echo '<td></td>';
	echo '<td colspan="4">';
	echo '<div style="align-item: flex-start; display: flex; flex-direction: row; justify-content: space-around;">';

		echo '<div style="flex: 1 1 auto; padding:0.25rem;">';
			echo '<h3>Request</h3>';
			echo '<pre class="head">' . h(_view_data_scrub($rec['req_head'])) . '</pre>';
			echo '<pre class="body">' . h(_view_data_scrub($rec['req_body'])) . '</pre>';
		echo '</div>';

		echo '<div style="flex: 1 1 auto; padding:0.25rem;">';
			echo '<h3>Response</h3>';
			echo '<pre class="head">' . h(_view_data_scrub($rec['res_head'])) . '</pre>';
			echo '<pre class="body">' . h(_view_data_scrub($rec['res_body'])) . '</pre>';
		echo '</div>';

	echo '</div>';
	echo '</td>';
	echo '</tr>';

}
?>
</tbody>
</table>

<script src="https://cdn.openthc.com/zepto/1.2.0/zepto.js" integrity="sha256-vrn14y7WH7zgEElyQqm2uCGSQrX/xjYDjniRUQx3NyU=" crossorigin="anonymous"></script>

<script>
function rowOpen(row)
{
	var id1 = row.id;
	var id2 = id1.replace(/-1$/, '-2');
	var id3 = id1.replace(/-1$/, '-3');

	var tr2 = $('#' + id2);
	// var tr3 = $('#' + id3);

	tr2.show();
	// tr3.show();

	row.setAttribute('data-mode', 'open');


}

function rowShut(row)
{
	var id1 = row.id;
	var id2 = id1.replace(/-1$/, '-2');
	var id3 = id1.replace(/-1$/, '-3');

	var tr2 = $('#' + id2);
	// var tr3 = $('#' + id3);

	tr2.hide();
	// tr3.hide();

	row.setAttribute('data-mode', 'shut');
}

$(function() {

	$('.tr1').on('click', function() {

		var id1 = this.id;
		var id2 = id1.replace(/-1$/, '-2');
		var id3 = id1.replace(/-1$/, '-3');

		var mode = this.getAttribute('data-mode');
		if ('open' == mode) {
			rowShut(this);
			return false;
		}

		rowOpen(this);

	});

	var hash = window.location.hash;
	hash = hash.replace(/#/, '');
	var rec_list = hash.split(',');
	rec_list.forEach(function(v, i) {
		var key = `#row-${v}-1`;
		var row = document.querySelector(key);
		if (row) {
			rowOpen(row);
		}
	});

});
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
