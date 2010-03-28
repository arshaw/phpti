--TEST--

getsuperblock function

--FILE--

<? require_once '../src/ti.php' ?>
<? include 'templates/base.php' ?>

<? startblock('footer') ?>
Copyright 2009
<? echo str_repeat(trim(getsuperblock()), 2) ?>
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
</h1>
</div>
<div id='content'>
</div>
<div id='footer'>
Copyright 2009
this is the default footerthis is the default footer</div>
</body>
</html>
