--TEST--

referencing a superblock, its filter should be applied

--FILE--

<? require_once 'ti.php' ?>
<? include 'templates/base_w_filter.php' ?>

<? startblock('footer') ?>
Copyright 2009
<? superblock() ?>
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
*this is the default footer*</div>
</body>
</html>
