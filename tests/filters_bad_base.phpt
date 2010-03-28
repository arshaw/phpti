--TEST--

Warning for nonexistant filter happens where block is defined

--FILE--

<? require '../src/ti.php' ?>
<html>
<head>
<? startblock('head') ?>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
<? endblock() ?>
</head>
<body>
<div id='header'>
<h1>
<? emptyblock('title') ?>
</h1>
</div>
<div id='content'>
<? emptyblock('content') ?>
</div>
<div id='footer'>
<? startblock('footer','badfilter') ?>
this is the default footer
<? endblock() ?>
</div>
</body>
</html>

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
</div>
<div id='footer'>

Warning: filter 'badfilter' is not defined in %s/filters_bad_base.php on line 20
this is the default footer
</div>
</body>
</html>
