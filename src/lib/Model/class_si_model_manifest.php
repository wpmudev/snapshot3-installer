<?php

class Si_Model_Manifest extends Si_Model_ConfigConsumer {

	const LINE_DELIMITER = "\n";
	const ENTRY_DELIMITER = ":";

	const FILE_NAME = 'snapshot_manifest.txt';

	protected $_file;
	protected $_data;

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
	 * Satisfy interface
	 */
	public function update_raw ($key, $value) { return false; }

	/**
	 * Consume the manifest file
	 *
	 * @return bool
	 */
	public function consume () {
		if (!$this->has_file()) return false;

		$raw = file_get_contents($this->_file);
		if (empty($raw)) return false;

		$data = explode(self::LINE_DELIMITER, $raw);
		if (empty($data)) return false;

		foreach ($data as $line) {
			$line = trim($line);
			if (empty($line)) continue;

			list($key, $value) = explode(self::ENTRY_DELIMITER, $line, 2);

			// @TODO this is quite simplistic, improve!
			$value = preg_match('/\{.*?:/', $value)
				? unserialize($this->deep_trim($value))
				: $value
			;

			$this->_data[$key] = $value;
		}

		return true;
	}

	/**
	 * Get manifest queue for a given type
	 *
	 * @param string $type Queue type
	 *
	 * @return array Queue sources
	 */
	public function get_queue ($type) {
		$queues = $this->get('QUEUES', array());
		$result = array();
		if (is_array($queues)) foreach ($queues as $queue) {
			if (!empty($queue['type']) && $type === $queue['type']) {
				$result = $queue;
				break;
			}
		}
		return $result;
	}

	/**
	 * Get manifest sources for a queue type
	 *
	 * @param string $type Queue type
	 *
	 * @return array Queue sources
	 */
	public function get_sources ($type) {
		$queue = $this->get_queue($type);
		return !empty($queue['sources']) && is_array($queue['sources'])
			? $queue['sources']
			: array()
		;
	}

}
