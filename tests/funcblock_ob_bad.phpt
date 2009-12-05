--TEST--

test of the funcblock function

--FILE--

<? require_once '../src/templateblocks.php' ?>
<? include 'templates/base.php' ?>

<?

error_reporting(0); // hide the fatal error that will occur

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

?>

--EXPECT--

<html>
<head>
