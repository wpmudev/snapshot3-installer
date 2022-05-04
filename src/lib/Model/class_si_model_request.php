<?php

class Si_Model_Request extends Si_Model {

	const REQUEST_GET = 'get';
	const REQUEST_POST = 'post';
	const REQUEST_COOKIE = 'cookie';
	const REQUEST_EMPTY = 'empty';

	private $_data = array();

	private function __construct () {}

	public static function load ($method) {
		$method = !empty($method) && in_array($method, array(self::REQUEST_GET, self::REQUEST_POST, self::REQUEST_COOKIE, self::REQUEST_EMPTY))
			? $method
			: self::REQUEST_GET
		;
		$source = array();
		if (self::REQUEST_GET === $method) $source = $_GET;
		if (self::REQUEST_POST === $method) $source = $_POST;
		if (self::REQUEST_COOKIE === $method) $source = $_COOKIE;

		$me = new self;
		$me->_data = self::strip($source);

		return $me;
	}

	/**
	 * Recursively strip slashes from source if magic quotes are on
	 *
	 * @param mixed $val Value to process
	 *
	 * @return mixed
	 */
	public static function strip ($val) {
		if (version_compare(PHP_VERSION, '7.4.0', '>=') || !get_magic_quotes_gpc()) {
			return $val;
		}

		$val = is_array($val)
			? array_map(array(__CLASS__, 'strip'), $val)
			: stripslashes($val)
		;

		return $val;
	}

	/**
	 * Gets a value from request
	 *
	 * @param string $what Value key to get
	 * @param mixed $fallback Optional fallback value
	 *
	 * @return mixed
	 */
	public function get ($what, $fallback=false) {
		return isset($this->_data[$what])
			? $this->_data[$what]
			: $fallback
		;
	}

	/**
	 * Set request value by key
	 *
	 * @param string $what Value key to set
	 * @param mixed $value Value to set
	 *
	 * @return bool
	 */
	public function set ($what, $value) {
		return !!$this->_data[$what] = $value;
	}

	/**
	 * Convert the whole request to a GET-type query
	 *
	 * @return string
	 */
	public function to_query () {
		$str = http_build_query($this->_data, null, '&');
		return !empty($str)
			? "?{$str}"
			: ''
		;
	}

	/**
	 * Query string spawning convenience method
	 *
	 * Gets a query witout affecting the current request
	 *
	 * @param mixed $arg Optional array key (string), or arguments map (array)
	 * @param mixed $value Optional value
	 *
	 * @return string
	 */
	public function get_query ($arg=array(), $value=false) {
		$rq = new self;
		$rq->_data = $this->_data;

		if (!empty($arg) && is_array($arg) && empty($value)) {
			foreach ($arg as $key => $val) $rq->set($key, $val);
		} else if (!empty($arg)) {
			$rq->set($arg, $value);
		}

		return $rq->to_query();
	}

	/**
	 * Clean query string spawning convenience method
	 *
	 * Gets a "clean slate" query witout affecting the current request
	 *
	 * @param mixed $arg Optional array key (string), or arguments map (array)
	 * @param mixed $value Optional value
	 *
	 * @return string
	 */
	public function get_clean_query ($arg=array(), $value=false) {
		$rq = new self;

		if (!empty($arg) && is_array($arg) && empty($value)) {
			foreach ($arg as $key => $val) $rq->set($key, $val);
		} else if (!empty($arg)) {
			$rq->set($arg, $value);
		}

		return $rq->to_query();
	}
}
