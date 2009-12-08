<?php

// author: Adam Shaw
// Dual licensed under the MIT and GPL licenses (MIT-LICENSE.txt and GPL-LICENSE.txt)

//require dirname(__FILE__) . '/debug.php';



// defines a block who's content is empty, or a string

function block($name, $content='') {
	$block = new TemplateBlock($name);
	$block->content = $content;
	TemplateBlock::block($block);
}



// defines a block who's content will be the stdout of a function
// $func may be a function's name or a anonymous function (PHP >= 5.3)

function funcblock($name, $func) {
	$block = new TemplateFuncBlock($name, $func);
	TemplateBlock::block($block);
}



// defines a block who's content will be the global scope's stdout,
// until an accompanying endblock() is called

function startblock($name, $filters='') {
	$block = new TemplateBlock($name);
	if ($filters) {
		if (is_string($filters)) {
			$filters = explode('|', $filters);
		}
		else if (!is_array($filters)) {
			$filters = array($filters);
		}
		$block->filters = $filters;
	}
	TemplateBlock::startBlock($block);
}



// marks the end of a block initiated by startblock()

function endblock() {
	TemplateBlock::endBlock();
}



// inserts the content of the current block's overriden 'super block' into the output

function superblock() {
	return TemplateBlock::superBlock();
}



// ends the current template and flushes the output

function flushblocks() {
	TemplateBlock::flushBlocks();
}





/************************************** INTERNAL *****************************************/

class TemplateBlock {

	/* global
	------------------------------------------------------------------------*/
	
	static $base;      // can be used to see if currently in a template
	static $baseTrace; // 0th element is the base, 1st is child template, rest is other stuff...
	static $baseCutoff;
	static $stack;
	static $trailingWarnings;
	
	
	static function init() {
		if (self::$base && !self::inBaseOrChildTemplate()) {
			self::flushBlocks();
		}
		if (!self::$base) {
			$block = new TemplateBlock();
			self::$base = $block;
			self::$baseTrace = self::callingTrace();
			self::$baseCutoff = null;
			self::$stack = array();
			self::$trailingWarnings = '';
			ob_start(array('TemplateBlock', 'finalBufferCallback'));
		}
	}
	
	
	static function flushBlocks() {
		while (self::$stack) {
			ob_end_clean(); // will trigger nestedBufferCallback
		}
		$output = self::compileBase(ob_get_contents());
		self::$base = null;
		ob_end_clean(); // will trigger finalBufferCallback
		echo $output;
	}
	
	
	static function finalBufferCallback($content) {
		if (self::$base) {
			return self::compileBase($content);
		}
		return '';
	}

	
	
	
	
	
	static function startBlock($block) {
		self::init();
		self::registerBlock($block);
		self::$stack[] = $block;
		ob_start(array('TemplateBlock', 'nestedBufferCallback'));
	}
	
	
	static function endBlock() {
		if (self::$stack) {
			$block = end(self::$stack);
			$block->content = ob_get_contents();
			ob_end_clean(); // will trigger nestedBufferCallback
		}else{
			self::triggerWarning("orphan endblock()");
		}
	}
	
	
	static function nestedBufferCallback($content) {
		$block = array_pop(self::$stack);
		if ($block->content === null) {
			$block->content = $content;
			self::triggerWarning("missing endblock() for startblock('" . addslashes($block->name) . "')", $block->trace);
		}
		return '';
	}
	
	
	
	
	
	
	static function superBlock() {
		if (self::$stack) {
			$block = end(self::$stack);
			if ($block->super) {
				return $block->super->compile();
			}
		}else{
			self::triggerWarning('superblock() call must be within a block');
		}
	}
	
	
	static function block($block) {
		self::init();
		self::registerBlock($block);
	}
	
	
	static function registerBlock($block) {
		$calling_trace = self::callingTrace();
		while ($b = end(self::$stack)) {
			if (self::isSameFile($b->trace, $calling_trace)) {
				break;
			}else{
				self::endBlock();
				self::triggerWarning("missing endblock() for startblock('" . addslashes($b->name) . "')", $b->trace);
			}
		}
		$block->trace = $calling_trace;
		$block->position = ob_get_length();
		if (!self::$stack && !self::inBaseTemplate()) {
			if (self::$baseCutoff === null) {
				self::$baseCutoff = ob_get_length();
			}
			// block is top-level, but not part of the base
			// override any existing blocks with same name
			self::$base->override($block);
		}else{
			// either block is a child of the implicit base block
			// or is a child block within another block
			if (self::$stack) {
				$parent_block = end(self::$stack);
				$parent_block->children[] = $block;
			}else{
				self::$base->children[] = $block;
			}
		}
	}
	
	
	static function compileBase($content) {
		if (self::$baseCutoff) {
			// be smart about getting rid of accumulated trailing whitespace
			$content =
				rtrim(substr($content, 0, self::$baseCutoff)) .
				self::$trailingWarnings .
				ltrim(substr($content, self::$baseCutoff));
		}else{
			$content .= self::$trailingWarnings;
		}
		self::$base->content = $content;
		return self::$base->compile();
	}


	static function triggerWarning($message, $trace=null) {
		if (error_reporting() & E_USER_WARNING) {
			if (!$trace) {
				$trace = self::callingTrace();
			}
			$location = $trace[0];
			if (defined('STDIN')) {
				// from command line
				$format = "\nWarning: %s in %s on line %d\n";
			}else{
				// from browser
				$format = "<br />\n<b>Warning</b>:  %s in <b>%s</b> on line <b>%d</b><br />\n";
			}
			$s = sprintf($format, $message, $location['file'], $location['line']);
			if (!self::$base || self::inBaseTemplate()) {
				echo $s;
			}else{
				self::$trailingWarnings .= $s;
			}
		}
	}

	
	

	
	
	/* backtrace utilities
	------------------------------------------------------------------------*/
	
	
	static function inBaseTemplate() {
		return self::isSameFile(self::callingTrace(), self::$baseTrace);
	}
	
	
	static function inBaseOrChildTemplate() {
		$calling_trace = self::callingTrace();
		$base_trace = self::$baseTrace;
		return
			$calling_trace && $base_trace &&
			self::isSubtrace(array_slice($calling_trace, 1), $base_trace) &&
			$calling_trace[0]['file'] === $base_trace[count($base_trace)-count($calling_trace)]['file'];
	}
	
	
	static function callingTrace() {
		$trace = debug_backtrace();
		foreach ($trace as $i => $location) {
			if ($location['file'] !== __FILE__) {
				return array_slice($trace, $i);
			}
		}
	}
	
	
	static function isSameFile($trace1, $trace2) {
		return
			$trace1 && $trace2 &&
			$trace1[0]['file'] === $trace2[0]['file'] &&
			array_slice($trace1, 1) === array_slice($trace2, 1);
	}
	
	
	static function isSubtrace($trace1, $trace2) { // is trace1 a subtrace of trace2
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
	
	
	
	
	
	
	
	/* block methods
	------------------------------------------------------------------------*/
	
	
	function TemplateBlock($name='') {
		$this->name = $name;
		$this->position = 0;
		$this->content = null;
		$this->trace = null;
		$this->super = null;
		$this->children = array();
		$this->filters = array();
	}
	
	
	// replace any ancestor blocks that have same name
	
	function override($block) {
		$name = $block->name;
		if ($name) { // no-name blocks shouldn't override anything
			foreach ($this->children as $i => $child) {
				if ($child->name == $name) {
					// insert block in-place-of child, but record 'super block'
					$block->position = $child->position;
					$block->super = $child;
					$this->children[$i] = $block;
				}else{
					$child->override($block);
				}
			}
		}
	}
	
	
	// compile all children and generate string output
	
	function compile() {
		$parts = array();
		$content = $this->getContent();
		foreach ($this->filters as $filter) {
			$content = call_user_func($filter, $content);
		}
		$previ = 0;
		foreach ($this->children as $child) {
			$i = $child->position; // a string index of $content
			$parts[] = substr($content, $previ, $i-$previ);
			$parts[] = $child->compile();
			$previ = $i;
		}
		$parts[] = substr($content, $previ);
		return implode($parts);
	}
	
	
	// abstractable way to get a block's inner-content
	
	function getContent() {
		return $this->content;
	}
	
	
}



/* class for supporting funcblock()
------------------------------------------------------------------------*/

class TemplateFuncBlock extends TemplateBlock {

	
	function TemplateFuncBlock($name, $func) {
		parent::TemplateBlock($name);
		$this->func = $func;
	}
	
	
	function getContent() {
		return call_user_func($this->func);
	}
	
	
}


?>
