--TEST--

Warns and does not execute filters that do not exist

--FILE--

<?php

require_once '../src/templateinheritance.php';
include 'templates/base.php';

function filter1($s) {
	return '*' . trim($s) . '*';
}

?>

<? startblock('content', 'filter9,filter1') ?>
this is the content
<? endblock() ?>

--EXPECTF--

<html>
<head>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
</head>
<body>
<div id='header'>
<h1>
</h1>
</div>
<div id='content'>
*this is the content*</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>

Warning: filter 'filter9' is not defined in %s/filters_bad.php on line 13
