<?php
/**
 * View the Audit Log
 */

$tz = new \DateTimezone($data['tz']);

$snap_data = $_GET;
unset($snap_data['snap-get']);
$snap_data = array_filter($snap_data);
$snap_data = base64_encode(json_encode($snap_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

$snap_mode = ('snap' == $data['snap'])

?>

<div class="container-fluid">
<h1><?= $data['Page']['title'] ?></h1>
</div>

<?php
if (empty($snap_mode)) {
?>
<div class="container-fluid">

<form autocomplete="off">
<div class="search-filter">
	<div>
		<input autocomplete="off" autofocus class="form-control" name="q" placeholder="search" value="<?= h($_GET['q']) ?>">
	</div>
	<div>
		<input autocomplete="off" class="form-control" name="l" placeholder="license hash" value="<?= h($_GET['l']) ?>">
	</div>
	<div>
		<input class="form-control" name="d0" type="date" value="<?= h($_GET['d0']) ?>">
	</div>
	<div>
		<input class="form-control" name="t0" type="time" value="<?= h($_GET['t0']) ?>">
	</div>
	<div>
		<input class="form-control" name="d1" type="date" value="<?= h($_GET['d1']) ?>">
	</div>
	<div>
		<input class="form-control" name="t1" type="time" value="<?= h($_GET['t1']) ?>">
	</div>
	<div>
		<button class="btn btn-primary" type="submit">Go</button>
	</div>
	<div>
		<button class="btn btn-secondary" formtarget="_blank" name="a" type="submit" value="snap">Snap</button>
	</div>
	<div>
		<a class="btn btn-secondary" href="?<?= $data['link_newer'] ?>">Newer</a>
	</div>
	<div>
		<a class="btn btn-secondary" href="?<?= $data['link_older'] ?>">Older</a>
	</div>
	<div>
		<input name="snap-get" type="hidden" value="<?= $snap_data ?>">
		<button class="btn btn-secondary" name="a" type="submit" value="x">Clear</button>
	</div>
</div>
</form>

<div class="sql-debug"><?= h(trim($data['sql_debug'])) ?></div>

</div>

<?php
}
?>

<div class="container-fluid">
<table class="table table-sm table-bordered table-hover" id="log-table">
<thead class="table-dark">
	<tr>
		<th></th>
		<th>Date</th>
		<th>Request</th>
		<th>Status</th>
		<th>Size</th>
	</tr>
</thead>
<tbody>
<?php
$idx = $offset;
foreach ($data['log_audit'] as $rec) {

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

	// $req = strtok($rec['req_head'], "\n");
	$res = strtok($rec['res_head'], "\n");

	printf('<tr class="tr1" data-target="#row-%d-2" id="row-%d">', $idx, $idx);
	if ($snap_mode) {
		printf('<td class="c">%d</td>', $idx);
	} else {
		printf('<td class="c"><a href="/log/view?id=%s">%d</a></td>', $rec['id'], $idx);
	}
	// echo '<td>' . _date('m/d H:i:s', $rec['req_time']) . '</td>';
	// echo '<td>' . h($rec['req_time']) . '</td>';
	printf('<td title="%s">%s [%0.1f s]</td>', __h($rec['req_time']), $dt0->format('m/d H:i:s'), $res_wait);
	echo '<td>' . __h($rec['req_name']) . '</td>';
	echo '<td>' . __h($res) . '</td>';
	echo '<td>' . strlen($rec['res_body']) . '</td>';
	echo '</tr>';

	printf('<tr class="tr2 %s" id="row-%d-2">', ($snap_mode ? 'snap' : 'hide'), $idx);
	echo '<td></td>';
	echo '<td colspan="4">';
	echo '<div style="align-item: flex-start; display: flex; flex-direction: row; justify-content: space-around;">';

		echo '<div style="flex: 1 1 50%; padding:0.25rem;">';
			echo '<h3>Request</h3>';
			echo '<pre class="head">' . __h(_view_data_scrub($rec['req_head'])) . '</pre>';
			echo '<pre class="body">' . __h(_view_data_scrub($rec['req_body'])) . '</pre>';
		echo '</div>';

		echo '<div style="flex: 1 1 50%; padding:0.25rem;">';
			echo '<h3>Response</h3>';
			echo '<pre class="head">' . __h(_view_data_scrub($rec['res_head'])) . '</pre>';
			echo '<pre class="body">' . __h(_view_data_scrub($rec['res_body'])) . '</pre>';
		echo '</div>';

	echo '</div>';
	echo '</td>';
	echo '</tr>';

}
?>
</tbody>
</table>

</div>

<?php
if (empty($snap_mode)) {
	// Had Script Include Here
}


/**
 * Sanatize String w/RegEx (sloppy)
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
