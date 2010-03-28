--TEST--

throws a warning when omitted end tag in child template

--FILE--

<? error_reporting(E_ALL) ?>
<? require_once 'ti.php'?>
<? include 'templates/base.php' ?>

<? endblock() ?>

<? startblock('content') ?>
this is the content

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
this is the content

</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>
Warning: orphan endblock() in %s/mismatch_child.php on line 6

Warning: missing endblock() for startblock('content') in %s/mismatch_child.php on line 8
