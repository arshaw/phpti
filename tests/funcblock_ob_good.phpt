--TEST--

funcblock with output buffer stuff

--FILE--

<? require_once '../src/templateblocks.php' ?>
<? include 'templates/base.php' ?>

<?

funcblock('content', 'content_func');
function content_func() {
	ob_start();
	echo "content generated from an output buffer";
	return ob_get_clean();
}

funcblock('footer', 'footer_func');
function footer_func() {
	return "dynamically generated footer";
}

flushblocks();

?>

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
content generated from an output buffer</div>
<div id='footer'>
dynamically generated footer</div>
</body>
</html>
