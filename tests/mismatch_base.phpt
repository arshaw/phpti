--TEST--

orphan endblock and dangling startblock in a base template

--FILE--

<? error_reporting(E_ALL) ?>
<? require_once '../src/templateinheritance.php' ?>
<? include 'templates/base_broken.php' ?>

<? startblock('content') ?>
my content
<? endblock() ?>

--EXPECTF--

<html>
<head>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
</head>

Warning: orphan endblock() in %s/base_broken.php on line 8
<body>
<div id='header'>
<h1>
</h1>
</div>
<div id='content'>
my content
</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>

Warning: missing endblock() for startblock('nothing') in %s/base_broken.php on line 18
