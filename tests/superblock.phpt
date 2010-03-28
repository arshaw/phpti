--TEST--

superblock function, before/after/inside content, blocks in different order, when empty parent too

--FILE--

<? require_once 'ti.php' ?>
<? include 'templates/base.php' ?>

<? startblock('footer') ?>
Copyright 2009
<? superblock() ?>
<? endblock() ?>

<? startblock('title') ?>
<? superblock() ?>
the page title
<? endblock() ?>

<? startblock('head') ?>
<link rel='stylesheet' type='text/css' href='something.css' />
<? superblock() ?>
<script type='text/javascript' src='something.js'></script>
<? endblock() ?>

--EXPECT--

<html>
<head>
<link rel='stylesheet' type='text/css' href='something.css' />
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
<script type='text/javascript' src='something.js'></script>
</head>
<body>
<div id='header'>
<h1>
the page title
</h1>
</div>
<div id='content'>
</div>
<div id='footer'>
Copyright 2009
this is the default footer
</div>
</body>
</html>
