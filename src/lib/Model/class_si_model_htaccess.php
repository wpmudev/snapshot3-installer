<?php

class Si_Model_Htaccess extends Si_Model_ConfigConsumer {

	const REWRITE_BASE = 'RewriteBase';
	const REWRITE_RULE = 'RewriteRule';

	const FILE_NAME = '.htaccess';

	public static function load ($destination) {
		$path = false;
		if (is_object($destination) && $destination->exists(self::FILE_NAME)) {
			$path = $destination->resolve(self::FILE_NAME);
		}

		$me = new self;

		if (!empty($path)) {
			$me->_file = $path;
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
		$raw = $this->read_file();
		return $this->process_content($raw);
	}

	/**
	 * Reads file content
	 *
	 * @return bool|string Bool false on failure, content on success
	 */
	public function read_file () {
		if (empty($this->_file) || !is_readable($this->_file)) return false;

		$raw = file_get_contents($this->_file);
		$this->_raw = $raw;

		return $raw;
	}

	/**
	 * Processes raw file content into data blocks
	 *
	 * @param string $raw Raw file content.
	 *
	 * @return bool
	 */
	public function process_content ($raw) {
		if (empty($raw)) return false;

		$lines = array_filter(array_map('trim', explode("\n", $raw)));
		$data = array();
		foreach ($lines as $line) {
			if (!preg_match('/^[A-Z]/', trim($line))) continue;
			if (!strstr($line, ' ')) continue;
			$tuple = explode(' ', $line, 2);

			if (self::REWRITE_RULE === $tuple[0]) {
				$triple = explode(' ', $line, 3);
				$key = $triple[0] . ' ' . $triple[1];
				if (isset($data[$key])) continue;
				$data[$key] = $triple[2];
			} else {
				if (isset($data[$tuple[0]])) continue;
				$data[$tuple[0]] = $tuple[1];
			}

		}
		$this->_data = $data;

		return !empty($this->_data);
	}

	/**
	 * Gets internal data storage content
	 *
	 * @return array
	 */
	public function get_data () {
		return $this->_data;
	}

	/**
	 * Gets full text content of the file
	 *
	 * @return string
	 */
	public function get_raw () {
		return !empty($this->_raw) ? $this->_raw : '';
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
		if (!in_array($key, array_keys($this->_data))) return false;

		$old = $this->get($key);
		$rx = '/' .
			preg_quote($key, '/') .
			'\s+' .
			preg_quote($old, '/') .
		'/';

		$this->_raw = preg_replace($rx, "{$key} {$value}", $this->_raw);

		return true;
	}

	/**
	 * Updates rewrite bases using the internal API
	 *
	 * @param string $new_base New base to use
	 *
	 * @return bool
	 */
	public function update_raw_base ($new_base) {
		$old = $this->get(self::REWRITE_BASE);
		$old_rx = '/' . preg_quote($old, '/') . '/';

		foreach ($this->_data as $key => $value) {
			if (!preg_match($old_rx, $value)) continue;
			$new_value = preg_replace($old_rx, $new_base, $value, 1); // Only first instance, so we keep rewrite rules
			$this->update_raw($key, $new_value);
		}

		return true;
	}


}
