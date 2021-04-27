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
<link rel="stylesheet" href="/css/app.css">
<title><?= h(strip_tags($_ENV['title'])) ?></title>
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

<script src="https://cdn.openthc.com/zepto/1.2.0/zepto.js" integrity="sha256-vrn14y7WH7zgEElyQqm2uCGSQrX/xjYDjniRUQx3NyU=" crossorigin="anonymous"></script>
<?= $this->foot_script ?>
</body>
</html>
