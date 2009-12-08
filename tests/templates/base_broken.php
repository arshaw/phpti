<html>
<head>
<? startblock('head') ?>
<link rel='stylesheet' type='text/css' href='main.css' />
<script type='text/javascript' src='main.js'></script>
<? endblock() ?>
</head>
<? endblock() ?>
<body>
<div id='header'>
<h1>
<? block('title') ?>
</h1>
</div>
<div id='content'>
<? block('content') ?>
</div>
<? startblock('nothing') ?>
<div id='footer'>
<? startblock('footer') ?>
this is the default footer
<? endblock() ?>
</div>
</body>
</html>
