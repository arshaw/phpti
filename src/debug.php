<?php

$debug_file = dirname(__FILE__) . '/debug_out.txt';

if (file_exists($debug_file)) {
	unlink($debug_file);
}

function debug($s) {
	global $debug_file;
	$f = fopen($debug_file, 'a');
	fwrite($f, "$s\n");
	fclose($f);
}

?>
