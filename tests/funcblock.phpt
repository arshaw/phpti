--TEST--

test of the funcblock function

--FILE--

<? require_once '../src/templateblocks.php' ?>
<? include 'templates/base.php' ?>

<?

funcblock('content', 'content_func');
function content_func() {
	return "dynamically generated content";
}

funcblock('footer', 'footer_func');
function footer_func() {
	return "dynamically generated footer";
}

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
dynamically generated content</div>
<div id='footer'>
dynamically generated footer</div>
</body>
</html>
