<?php
/**
 * OpenTHC HTML Layout
 *
 * SPDX-License-Identifier: MIT
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
<meta name="theme-color" content="#069420">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="google" content="notranslate">
<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="/css/app.css">
<title>PIPE || <?= __h(strip_tags($_ENV['title'])) ?></title>
</head>
<body data-bs-theme="light">
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

<script id="tr-open-shut">
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
</script>

<script id="btn-snap-script">
var btnSnap = document.querySelector('.btn-snap');
if (btnSnap) {
	btnSnap.addEventListener('click', function() {

		var output_dom = document.cloneNode(true);

		var rem_node = output_dom.querySelector('#search-filter-wrap');
		rem_node.remove();

		var rem_node = output_dom.querySelector('#btn-snap-script');
		rem_node.remove();

		var source_html = '';
		try {
			var x = new XMLSerializer().serializeToString(output_dom);
			source_html = x.toString();
		} catch (e) {
			source_html = '-exception-';
		}

		var form = new FormData();
		form.set('source-html', source_html);
		// form.append('file', e.dataTransfer);
		fetch('/log/snap', {
			method: 'POST',
			body: form,
		}).then(function(res) {
			return res.json();
		}).then(function(res) {
			console.log(res);
			window.open(res.data);
		});
		// These Shortcuts Encoded Funny on POST
		// }).then(res => {
		// 	return res.json();
		// }).then(res => {
		// 	debugger;
		// 	console.log(res);
		// 	window.open(res.data);
		// });
		// $.get('/help/bug', function(res) {
		// 	res = $(res);
		// 	$(document.body).append(res);
		// 	$('#modal-support-form').modal('show');
		// 	$('#modal-support-form').on('hidden.bs.modal', function() {
		// 			res.remove();
		// 	});
		// 	$('#modal-support-form #source-html').val(source_html);
		// });


		// Clone DOM



		// Remove Script
		// Remove Form
		// POST
		// fetch('/log/snap', {
		// 	method: 'POST',
		// 	body: htmlSnapshot;
		// });

	});
}
</script>
</body>
</html>
