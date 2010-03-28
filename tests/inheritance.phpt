--TEST--

Basic inheritance. Notice block order doesn't matter.

--FILE--

<? require_once 'ti.php' ?>
<? include 'templates/base.php' ?>

<? startblock('content') ?>
<p>
the page content
</p>
<? endblock() ?>

<? startblock('title') ?>
the page title
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
the page title
</h1>
</div>
<div id='content'>
<p>
the page content
</p>
</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>
