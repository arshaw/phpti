--TEST--

test the filter argument of startblock, with filters as a string and array

--FILE--

<?
require_once '../src/templateinheritance.php';
include 'templates/base.php';

function filter1($s) {
	return '*' . trim($s) . '*';
}

function filter2($s) {
	return trim($s) . '...';
}

startblock('title', 'filter1|filter2')
?>
this is the title
<? endblock() ?>

<? startblock('content', array('filter2', 'filter1')) ?>
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
*this is the title*...</h1>
</div>
<div id='content'>
*this is the content...*</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>
