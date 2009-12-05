<?php


// author: Adam Shaw
// Dual licensed under the MIT and GPL licenses (MIT-LICENSE.txt and GPL-LICENSE.txt)


// defines a block who's content is empty, or a string

function block($name, $content='') {
	TemplateBlock::init();
	$block = new TemplateBlock($name, ob_get_length());
	$block->content = $content;
	TemplateBlock::report($block);
}



// defines a block who's content will be the stdout of a function
// $func may be a function's name or a anonymous function (PHP >= 5.3)

function funcblock($name, $func) {
	TemplateBlock::init();
	$block = new TemplateFuncBlock($name, ob_get_length(), $func);
	TemplateBlock::report($block);
}



// defines a block who's content will be the global scope's stdout,
// until an accompanying endblock() is called

function startblock($name, $filters='') {
	TemplateBlock::init();
	$block = new TemplateBlock($name, ob_get_length());
	if ($filters) {
		if (is_string($filters)) {
			$filters = explode('|', $filters);
		}
		else if (!is_array($filters)) {
			$filters = array($filters);
		}
		$block->filters = $filters;
	}
	TemplateBlock::report($block);    // for establishing parent/child relationships
	TemplateBlock::$stack[] = $block; // needs to go after report()
	ob_start();
}



// marks the end of a block initiated by startblock()

function endblock() {
	$block = array_pop(TemplateBlock::$stack);
	if ($block) {
		$block->content = ob_get_clean();
	}else{
		// throw warning
	}
}



// inserts the content of the current block's overriden 'super block' into the output

function superblock() {
	TemplateBlock::init();
	$block = end(TemplateBlock::$stack);
	if ($block) {
		if ($block->super) {
			return $block->super->strval();
		}
	}else{
		// throw warning
	}
}



// ends the current template and sends the final output to the browser

function flushblocks() {
	ob_end_flush(); // will call buffer_callback
}





/************************************** INTERNAL *****************************************/



class TemplateBlock {
	
	
	
	/* global
	------------------------------------------------------------------------*/
	
	static $base;
	static $base_trace; // 0th element is the base, 1st is child template, rest is other stuff...
	static $base_cutoff;
	static $stack;
	static $output;
	
	
	// initialize the base block and output buffer (if need be)
	
	static function init() {
		if (self::$stack && !self::in_base_child()) {
			ob_end_flush();
			self::$stack = null;
		}
		if (!self::$stack) {
			$block = new TemplateBlock();
			self::$base = $block;
			self::$base_trace = self::calling_trace();
			self::$base_cutoff = null;
			self::$stack = array($block);
			ob_start(array('TemplateBlock', 'buffer_callback'));
		}
	}
	
	
	// just encountered a new block, find a place for it
	
	static function report($block) {
		if (count(self::$stack) == 1 && !self::in_base()) {
			if (self::$base_cutoff === null) {
				self::$base_cutoff = ob_get_length();
			}
			// block is top-level, but not part of the base
			// override any existing blocks with same name
			self::$base->override($block);
		}else{
			// either block is a child of the implicit base block
			// or is a child block within another block
			$parent = end(self::$stack);
			$parent->children[] = $block;
		}
	}
	
	
	// called when the base output buffer is cleaned/flushed
	
	static function buffer_callback($content) {
		$block = array_pop(self::$stack);
		if (self::$stack) {
			foreach (self::$stack as $b) {
				// throw warning
			}
			self::$stack = null;
		}
		self::$base = null;
		self::$base_trace = null;
		$block->content = $content; //self::$base_cutoff ? substr($content, 0, self::$base_cutoff) : $content; //!! was causing double to fail
		self::$base_cutoff = null;
		return $block->strval();
	}
	
	
	
	
	
	/* backtrace utilities
	------------------------------------------------------------------------*/
	
	static function in_base() {
		$calling_trace = self::calling_trace();
		$base_trace = self::$base_trace;
		return
			$calling_trace && $base_trace &&
			$calling_trace[0]['file'] === $base_trace[0]['file'] &&
			array_slice($calling_trace, 1) === array_slice($base_trace, 1);
	}
	
	static function in_base_child() {
		$calling_trace = self::calling_trace();
		$base_trace = self::$base_trace;
		return
			$calling_trace && $base_trace &&
			self::is_subtrace(array_slice($calling_trace, 1), $base_trace) &&
			$calling_trace[0]['file'] === $base_trace[count($base_trace)-count($calling_trace)]['file'];
	}
	
	static function calling_trace() {
		$trace = debug_backtrace();
		foreach ($trace as $i => $location) {
			if ($location['file'] !== __FILE__) {
				return array_slice($trace, $i);
			}
		}
	}
	
	static function is_subtrace($trace1, $trace2) { // is trace1 a subtrace of trace2
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
	
	
	function TemplateBlock($name='', $index=0) {
		$this->name = $name;
		$this->index = $index;
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
					$block->index = $child->index;
					$block->super = $child;
					$this->children[$i] = $block;
				}else{
					$child->override($block);
				}
			}
		}
	}
	
	
	// compile all children and generate string output
	
	function strval() {
		$parts = array();
		$content = $this->get_content();
		foreach ($this->filters as $filter) {
			$content = call_user_func($filter, $content);
		}
		$previ = 0;
		foreach ($this->children as $child) {
			$i = $child->index; // a string index of $content
			$parts[] = substr($content, $previ, $i-$previ);
			$parts[] = $child->strval();
			$previ = $i;
		}
		$parts[] = substr($content, $previ);
		return implode($parts);
	}
	
	
	// abstractable way to get a block's inner-content
	
	function get_content() {
		return $this->content;
	}
	
	
}



/* class for supporting funcblock()
------------------------------------------------------------------------*/

class TemplateFuncBlock extends TemplateBlock {

	
	function TemplateFuncBlock($name, $index, $func) {
		parent::TemplateBlock($name, $index);
		$this->func = $func;
	}
	
	
	function get_content() {
		if (get_class($this->func) == 'Closure') {
			// an anonymous function
			return $this->func();
		}else{
			return call_user_func($this->func);
		}
	}
	
	
}


?>
