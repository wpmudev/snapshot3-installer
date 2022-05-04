<?php

abstract class Si_View {

	abstract public function out ($params=array());

	private $_state;

	public function get_state () { return $this->_state; }
	public function set_state ($state) { return !!$this->_state = $state; }

	/**
	 * Quick, trimmed down string convention replacer
	 *
	 * @param string $str String to process
	 *
	 * @return string
	 */
	public function quickdown ($str) {
		$str = preg_replace('/```([^`]+)```/', '<pre><code>\\1</code></pre>', $str);
		$str = preg_replace('/`([^`]+)`/', '<code>\\1</code>', $str);
		return $str;
	}
}
