--TEST--

Output outside of template, after template, gets sent to standard out

--FILE--

<?php

require_once '../src/templateinheritance.php';

include 'templates/page1.php';

echo "this is after";

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
this is a cool title
</h1>
</div>
<div id='content'>
thie is some cool content
</div>
<div id='footer'>
this is the default footer
</div>
</body>
</html>this is after
