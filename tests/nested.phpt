--TEST--

nested blocks

--FILE--

<? require_once '../src/templateblocks.php' ?>
<? include 'templates/3col.php' ?>

<? startblock('left') ?>
<div>left content</div>
<? endblock() ?>

<? startblock('center') ?>
<p>
center content
</p>
<? endblock() ?>

<? startblock('right') ?>
<div>right content</div>
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
<div id='left'>
<div>left content</div>
</div>
<div id='center'>
<p>
center content
</p>
</div>
<div id='right'>
<div>right content</div>
</div>
</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>
