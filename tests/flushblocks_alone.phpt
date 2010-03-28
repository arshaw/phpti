--TEST--

flushblocks can be called when there are no blocks, just won't do anything

--FILE--

<?
require_once '../src/ti.php';
echo "here1";
flushblocks();
echo "here2";
?>

--EXPECT--

here1here2
