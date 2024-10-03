<?php
/**
 * OpenTHC HTML Layout
 */

use Edoceo\Radix\Session;

if (empty($_ENV['title'])) {
	$_ENV['title'] = $this->data['Page']['title'];
}

?>
<!DOCTYPE html>
<html lang="en" translate="no">
<head>
<meta charset="utf-8">
<meta name="application-name" content="OpenTHC">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#003100">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="google" content="notranslate">
<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="/css/app.css">
<title>PIPE || <?= __h(strip_tags($_ENV['title'])) ?></title>
</head>
<body>
<?php

$x = Session::flash();
if (!empty($x)) {

	$x = str_replace('<div class="good">', '<div class="alert alert-success" role="alert">', $x);
	$x = str_replace('<div class="info">', '<div class="alert alert-info" role="alert">', $x);
	$x = str_replace('<div class="warn">', '<div class="alert alert-warning" role="alert">', $x);
	$x = str_replace('<div class="fail">', '<div class="alert alert-danger" role="alert">', $x);

	echo '<div class="radix-flash">';
	echo $x;
	echo '</div>';

}

echo $this->body;

?>

<?= $this->foot_script ?>

<script>
function rowOpen(tr1)
{
	var id2 = tr1.getAttribute('data-target');
	var tr2 = document.querySelector(id2);

	if (tr2.classList.contains('hide')) {
		tr2.classList.remove('hide');
		tr1.setAttribute('data-mode', 'open');
	}

}

function rowShut(tr1)
{
	var id2 = tr1.getAttribute('data-target');
	var tr2 = document.querySelector(id2);

	if (!tr2.classList.contains('hide')) {
		tr2.classList.add('hide');
		tr1.setAttribute('data-mode', 'shut');
	}

}

var tab = document.querySelector('#log-table');
tab.addEventListener('click', function(e) {

	var t = e.target;

	if ('A' == t.nodeName) {
		return true;
	}

	var p = t.parentElement;
	if (p.classList.contains('tr1')) {

		var mode = p.getAttribute('data-mode');
		if ('open' == mode) {
			rowShut(p);
			return false;
		}

		rowOpen(p);

	}
});

// Open each row by id in the hash
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
</script>
</body>
</html>
