<?php

require_once dirname(__FILE__) . '/debug.php';



$GLOBALS['_HTML_Template_Inheritance_base'] = null;
$GLOBALS['_HTML_Template_Inheritance_stack'] = null;
$GLOBALS['_HTML_Template_Inheritance_hash'] = null;



function block($name, $content='') {
	$block = array(
		'name' => $name,
		'content' => $content
	);
	HTML_Template_Inheritance::registerBlock($block);
}


function startblock($name, $filters=array()) {
	if ($filters) {
		if (is_string($filters)) {
			$filters = explode('|', $filters);
		}
		else if (!is_array($filters)) {
			$filters = array($filters);
		}
	}else{
		$filters = array();
	}
	$block = array(
		'name' => $name,
		'filters' => $filters
	);
	HTML_Template_Inheritance::registerBlock($block);
	$GLOBALS['_HTML_Template_Inheritance_stack'][] =& $block;
	ob_start(array('HTML_Template_Inheritance', 'nestedBufferCallback'));
}


function endblock() {
	$stack =& $GLOBALS['_HTML_Template_Inheritance_stack'];
	if ($stack) {
		$block =& $stack[count($stack)-1]; // would use end(), but can't do references
		$block['content'] = ob_get_contents();
		ob_end_clean(); // will trigger nestedBufferCallback
	}else{
		HTML_Template_Inheritance::triggerWarning("orphan endblock()");
	}
}


function superblock() {
	$stack = $GLOBALS['_HTML_Template_Inheritance_stack'];
	if ($stack) {
		$block = end($stack);
		if (isset($block['super'])) {
			return HTML_Template_Inheritance::compileBlock($block['super']);
		}
	}else{
		HTML_Template_Inheritance::triggerWarning('superblock() call must be within a block');
	}
}


function flushblocks() {
	$base =& $GLOBALS['_HTML_Template_Inheritance_base'];
	$stack =& $GLOBALS['_HTML_Template_Inheritance_stack'];
	if ($base) {
		while ($stack) {
			ob_end_clean(); // will trigger nestedBufferCallback
		}
		$output = HTML_Template_Inheritance::compileBase(ob_get_contents());
		$base = null;
		ob_end_clean(); // will trigger finalBufferCallback
		echo $output;
	}
}





class HTML_Template_Inheritance {


	function init() {
		$base =& $GLOBALS['_HTML_Template_Inheritance_base'];
		if ($base && !HTML_Template_Inheritance::inBaseOrChildTemplate()) {
			flushblocks();
		}
		if (!$base) {
			$base = array(
				'trace' => HTML_Template_Inheritance::callingTrace(),
				'children' => array(),
				'after' => ''
			);
			$GLOBALS['_HTML_Template_Inheritance_stack'] = array();
			$GLOBALS['_HTML_Template_Inheritance_hash'] = array();
			ob_start(array('HTML_Template_Inheritance', 'finalBufferCallback'));
		}
	}
	
	
	function registerBlock(&$block) {
		HTML_Template_Inheritance::init();
		$base =& $GLOBALS['_HTML_Template_Inheritance_base'];
		$stack =& $GLOBALS['_HTML_Template_Inheritance_stack'];
		$hash =& $GLOBALS['_HTML_Template_Inheritance_hash'];
		$calling_trace = HTML_Template_Inheritance::callingTrace();
		while ($b = end($stack)) {
			if (HTML_Template_Inheritance::isSameFile($b['trace'], $calling_trace)) {
				break;
			}else{
				endblock(); // will pop a block off the stack
				HTML_Template_Inheritance::triggerWarning("missing endblock() for startblock('" . addslashes($b['name']) . "')", $b['trace']);
			}
		}
		$name = $block['name'];
		$block['trace'] = $calling_trace;
		$block['offset'] = ob_get_length();
		$block['children'] = array();
		if ($stack || HTML_Template_Inheritance::inBaseTemplate()) {
			// block is a nested block OR part of the base
			// insert it as a child, and store its location in the hash
			if ($stack) {
				$parent =& $stack[count($stack)-1]; // would use end(), but can't do references
			}else{
				$parent =& $base;
			}
			$pinpoint = array(
				'siblings' => &$parent['children'],
				'index' => count($parent['children'])
			);
			if (isset($hash[$name])) {
				$hash[$name][] = $pinpoint;
			}else{
				$hash[$name] = array($pinpoint);
			}
			$parent['children'][] =& $block;
		}else{
			if (!isset($base['cutoff'])) {
				$base['cutoff'] = ob_get_length(); // base is done, record cutoff
			}
			// block is top-level, but not part of the base
			// override any existing blocks with same name
			if (isset($hash[$name])) {
				foreach ($hash[$name] as $loc) {
					$super =& $loc['siblings'][$loc['index']];
					$block['offset'] = $super['offset'];
					$loc['siblings'][$loc['index']] =& $block;
				}
				$block['super'] =& $super;
			}
		}
	}
	
	
	function triggerWarning($message, $trace=null) {
		if (error_reporting() & E_USER_WARNING) {
			if (!$trace) {
				$trace = HTML_Template_Inheritance::callingTrace();
			}
			$loc = $trace[0];
			if (defined('STDIN')) {
				// from command line
				$format = "\nWarning: %s in %s on line %d\n";
			}else{
				// from browser
				$format = "<br />\n<b>Warning</b>:  %s in <b>%s</b> on line <b>%d</b><br />\n";
			}
			$s = sprintf($format, $message, $loc['file'], $loc['line']);
			$base =& $GLOBALS['_HTML_Template_Inheritance_base'];
			if (!$base || HTML_Template_Inheritance::inBaseTemplate()) {
				echo $s;
			}else{
				$base['after'] .= $s;
			}
		}
	}
	
	
	function nestedBufferCallback($content) {
		$stack =& $GLOBALS['_HTML_Template_Inheritance_stack'];
		$block =& $stack[count($stack)-1]; // would use array_pop() or end(), but doesn't work with references
		array_pop($stack);
		if (!isset($block['content'])) {
			$block['content'] = $content;
			HTML_Template_Inheritance::triggerWarning("missing endblock() for startblock('" . addslashes($block['name']) . "')", $block['trace']);
		}
		return '';
	}
	
	
	function finalBufferCallback($content) {
		if ($GLOBALS['_HTML_Template_Inheritance_base']) {
			return HTML_Template_Inheritance::compileBase($content);
		}else{
			return '';
		}
	}
	
	
	function compileBase($content) {
		$base =& $GLOBALS['_HTML_Template_Inheritance_base'];
		if (isset($base['cutoff'])) {
			$content = rtrim(substr($content, 0, $base['cutoff'])) . $base['after'] . ltrim(substr($content, $base['cutoff']));
		}else{
			$content .= $base['after'];
		}
		$base['content'] = $content;
		return HTML_Template_Inheritance::compileBlock($base);
	}
	
	
	function compileBlock($block) {
		$parts = array();
		$content = $block['content'];
		if (isset($block['filters'])) {
			foreach ($block['filters'] as $filter) {
				$content = call_user_func($filter, $content);
			}
		}
		$prev_offset = 0;
		foreach ($block['children'] as $child) {
			$offset = $child['offset'];
			$parts[] = substr($content, $prev_offset, $offset - $prev_offset);
			$parts[] = HTML_Template_Inheritance::compileBlock($child);
			$prev_offset = $offset;
		}
		$parts[] = substr($content, $prev_offset);
		return implode($parts);
	}
	
	
	
	
	
	
	/* backtrace utilities
	------------------------------------------------------------------------*/
	
	
	function inBaseTemplate() {
		return HTML_Template_Inheritance::isSameFile(
			HTML_Template_Inheritance::callingTrace(),
			$GLOBALS['_HTML_Template_Inheritance_base']['trace']
		);
	}
	
	
	function inBaseOrChildTemplate() {
		$calling_trace = HTML_Template_Inheritance::callingTrace();
		$base_trace = $GLOBALS['_HTML_Template_Inheritance_base']['trace'];
		return
			$calling_trace && $base_trace &&
			HTML_Template_Inheritance::isSubtrace(array_slice($calling_trace, 1), $base_trace) &&
			$calling_trace[0]['file'] === $base_trace[count($base_trace)-count($calling_trace)]['file'];
	}
	
	
	function callingTrace() {
		$trace = debug_backtrace();
		foreach ($trace as $i => $location) {
			if ($location['file'] !== __FILE__) {
				return array_slice($trace, $i);
			}
		}
	}
	
	
	function isSameFile($trace1, $trace2) {
		return
			$trace1 && $trace2 &&
			$trace1[0]['file'] === $trace2[0]['file'] &&
			array_slice($trace1, 1) === array_slice($trace2, 1);
	}
	
	
	function isSubtrace($trace1, $trace2) { // is trace1 a subtrace of trace2
		$len1 = count($trace1);
		$len2 = count($trace2);
		if ($len1 > $len2) {
			return false;
		}
		for ($i=0; $i<$len1; $i++) {
			if ($trace1[$len1-1-$i] !== $trace2[$len2-1-$i]) {
				return false;
			}
		}
		return true;
	}
	

}


?>
