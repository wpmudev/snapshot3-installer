<?php

abstract class Si_Model_ConfigConsumer extends Si_Model {

	protected $_file;
	protected $_raw;
	protected $_data = array();

	/**
	 * Consume the config file
	 *
	 * @return bool
	 */
	abstract public function consume ();

	/**
	 * Update raw values in config file
	 *
	 * @param string $key Key to update
	 * @param string $value New value to set
	 *
	 * @return bool
	 */
	abstract public function update_raw ($key, $value);

	/**
	 * Sets internal file path
	 *
	 * @param string $path Absolute path to a file
	 *
	 * @return bool
	 */
	public function set_file ($path) {
		return !!($this->_file = $path);
	}

	/**
	 * Checks whether we have the file and are able to access it
	 *
	 * @return bool
	 */
	public function has_file () {
		return !empty($this->_file) && is_readable($this->_file);
	}

	/**
	 * Data element setter
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value
	 */
	public function set ($key, $value) {
		if (!is_array($this->_data)) $this->_data = array();
		return $this->_data[$key] = $value;
	}

	/**
	 * Data element getter
	 *
	 * @param string $key Key to get
	 * @param mixed $fallback Optional fallback value
	 *
	 * @return mixed Key value on success, or fallback value on failure
	 */
	public function get ($key, $fallback=false) {
		return isset($this->_data[$key])
			? $this->_data[$key]
			: $fallback
		;
	}

	/**
	 * Writes buffered content to the destination file
	 *
	 * @return bool
	 */
	public function write () {
		if (empty($this->_file) || !is_writable($this->_file)) return false;
		return !!file_put_contents($this->_file, $this->_raw);
	}
}
