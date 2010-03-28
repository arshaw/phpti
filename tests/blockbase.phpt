--TEST--

Make sure blockbase works on a base template that has no blocks

--FILE--

<? require_once 'ti.php' ?>
<? include 'templates/base_no_blocks.php' ?>

<? startblock('content') ?>
this is the content
<? endblock() ?>

--EXPECT--

<html>
<head>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
</head>
<body>
</body>
</html>
