<?php

class Si_Model_Wpconfig extends Si_Model_ConfigConsumer {

	const FILE_NAME = 'wp-config.php';

	public static function load ($destination) {
		$path = false;
		if (is_object($destination) && $destination->exists(self::FILE_NAME)) {
			$path = $destination->resolve(self::FILE_NAME);
		}

		$me = new self;

		if (!empty($path)) {
			$me->set_file($path);
			$me->consume();
		}

		return $me;
	}

	/**
	 * Consume the config file
	 *
	 * @return bool
	 */
	public function consume () {
		if (!$this->has_file()) return false;

		$raw = file_get_contents($this->_file);
		$this->_raw = $raw;
		if (empty($raw)) return false;

		return $this->parse();
	}

	/**
	 * Parses raw content
	 *
	 * @return bool
	 */
	public function parse () {
		$raw = $this->_raw;
		if (empty($raw)) return false;

		$tokens = token_get_all($raw);

		$result = array();
		$gathering = false;
		foreach ($tokens as $tok) {
			if ($gathering) {
				//if (is_array($tok) && !empty($tok[0]) && 315 === $tok[0]) { // Found a string
				if (is_array($tok) && !empty($tok[0]) && 'T_CONSTANT_ENCAPSED_STRING' === token_name($tok[0])) { // Found a string
					$tmp = isset($tok[1]) ? $tok[1] : false;
					if (!isset($key)) $key = trim($tmp, "'\"");
					else if (!isset($value)) $value = trim($tmp, "'\"");

					if (isset($key) && isset($value)) $result[$key] = $value;
				}

				// End condition, we're not gathering anymore
				if (in_array($tok, array(')', ';'))) {
					$gathering = false;
				}
			}

			// Restart the gathering cycle for defines
			//if (!$gathering && is_array($tok) && !empty($tok[0]) && 307 === $tok[0]) {
			if (!$gathering && is_array($tok) && !empty($tok[0]) && 'T_STRING' === token_name($tok[0])) {
				$gathering = true;
				unset($key);
				unset($value);
			}

			// Restart the gathering cycle for variables
			//if (!$gathering && is_array($tok) && !empty($tok[0]) && 309 === $tok[0]) {
			if (!$gathering && is_array($tok) && !empty($tok[0]) && 'T_VARIABLE' === token_name($tok[0])) {
				$gathering = true;
				$key = !empty($tok[1]) ? $tok[1] : false;
				if (empty($key)) unset($key);
				unset($value);
			}
		}
		$this->_data = array_merge($this->get_defaults(), $result);

		return !empty($this->_data);
	}

	/**
	 * Update raw values in config file
	 *
	 * @param string $key Key to update
	 * @param string $value New value to set
	 *
	 * @return bool
	 */
	public function update_raw ($key, $value) {
		$pattern = strstr($key, '$')
			? preg_quote($key, '/') . '\s*=\s*[\'"].*?[\'"];'
			: 'define\s*\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*,\s*[\'"].*?[\'"]\s*\);'
		;
		$value = strstr($key, '$')
			? "{$key} = '{$value}';"
			: "define('{$key}', '{$value}');"
		;

		$this->_raw = preg_replace("/{$pattern}/", $value, $this->_raw);

		return true;
	}

	/**
	* Gets the defaults so we have bare minimum we'll need to know defined
	*
	* @return array
	*/
	public function get_defaults () {
		return array(
			'DB_NAME' => '',
			'DB_USER' => '',
			'DB_PASSWORD' => '',
			'DB_HOST' => 'localhost',
			'DB_CHARSET' => 'utf8',
			'DB_COLLATE' => '',
			'$table_prefix' => 'wp_',
		);
	}

}
