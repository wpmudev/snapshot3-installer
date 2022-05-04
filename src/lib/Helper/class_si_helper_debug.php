<?php

/**
 * Deals with all our debugging needs
 */
class Si_Helper_Debug {

	/**
	 * Formats the output variables
	 *
	 * @param array $args What to inspect
	 *
	 * @return string
	 */
	static public function inspect ($args) {
		if (is_array($args) && count($args) === 1) $args = array_pop($args);
		return var_export($args, 1);
	}

	/**
	 * Outputs text-only
	 *
	 * @param mixed Whatever
	 *
	 * @return void
	 */
	static public function text () {
		$args = 1 === func_num_args() ? func_get_arg(0) : func_get_args();
		echo self::inspect($args);
	}

	static public function textx () {
		$args = 1 === func_num_args() ? func_get_arg(0) : func_get_args();
		self::text($args);
		die;
	}

	static public function html () {
		$args = 1 === func_num_args() ? func_get_arg(0) : func_get_args();
		echo '<pre>' . self::inspect($args) . '</pre>';
	}

	static public function htmlx () {
		$args = 1 === func_num_args() ? func_get_arg(0) : func_get_args();
		self::html($args);
		die;
	}

	static public function log () {
		$args = 1 === func_num_args() ? func_get_arg(0) : func_get_args();
		Si_Helper_Log::log(self::inspect($args));
	}
}
