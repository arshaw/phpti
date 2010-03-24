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
<? startblock('footer') ?>
this is the default footer
<? endblock() ?>
</div>
</body>
</html>
