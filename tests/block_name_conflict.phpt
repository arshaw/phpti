--TEST--

Cannot have two blocks with same name in one file

--FILE--

<?
require_once '../src/templateinheritance.php';
include 'templates/base.php';
?>

<? startblock('content') ?>
content1
<? endblock() ?>

<? startblock('content') ?>
content2
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
content1
</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>

Warning: cannot define another block called 'content' in %s/block_name_conflict.php on line 11
