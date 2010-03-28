--TEST--

Filters work with the array('class','method')

--FILE--

<?

require_once 'ti.php';
include 'templates/base.php';

class MyFilters {
	function filter1($s) {
		return '*' . trim($s) . '*';
	}
}

?>

<? startblock('content', array(array('MyFilters','filter1'), array('Nothing','method'))) ?>
this is the content
<? endblock() ?>

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
*this is the content*</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>

Warning: filter Nothing::method is not defined in /home/adam/Projects/PHPTI/tests/filters_class.php on line 15
