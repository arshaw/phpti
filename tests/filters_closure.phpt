--TEST--

test the filter arg for the startblock function, with PHP5.3 closures. both as a single closure and array

--SKIPIF--

<?php
if (PHP_MAJOR_VERSION < 5 || PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION < 3) {
	die("skip this test for PHP versions older than v5.3");
}
?>

--FILE--

<?php
require_once 'ti.php';
include 'templates/base.php';

$filter1 = function($s) {
	return '*' . trim($s) . '*';
};

$filter2 = function($s) {
	return trim($s) . '...';
};

startblock('title', $filter1)
?>
this is the title
<? endblock() ?>

<? startblock('content', array($filter1, $filter2)) ?>
this is the content
<? endblock() ?>

--EXPECT--

<html>
<head>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
</head>
<body>
<div id='header'>
<h1>
*this is the title*</h1>
</div>
<div id='content'>
*this is the content*...</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>
