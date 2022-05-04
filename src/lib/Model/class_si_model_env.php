<?php

class Si_Model_Env extends Si_Model {

	const TEMP_DIR = 'TEMP_DIR';
	const PATH_ROOT = 'PATH_ROOT';
	const ARCHIVE = 'ARCHIVE';
	const TARGET = 'TARGET';
	const TARGET_URL = 'TARGET_URL';

	private $_overrides = array();
	private $_can_override = false;

	public function __construct () {
		if (@session_start()) {
			$_SESSION['si_overrides'] = !empty($_SESSION['si_overrides']) && is_array($_SESSION['si_overrides'])
				? $_SESSION['si_overrides']
				: array()
			;
			$this->_overrides = $_SESSION['si_overrides'];
			$this->_can_override = true;
		}
	}

	/**
	 * Check if we can offer data overrides
	 *
	 * @return bool
	 */
	public function can_override () {
		return !!$this->_can_override;
	}

	/**
	 * Checks if we have any overrides
	 *
	 * @return bool
	 */
	public function has_overrides () {
		if (!$this->can_override()) return false;
		return !empty($this->_overrides) && !empty($_SESSION['si_overrides']);
	}

	/**
	 * Option getter
	 *
	 * @param string $what Value key to get
	 * @param mixed $fallback Optional fallback
	 *
	 * @return mixed Value or fallback
	 */
	public function get ($what, $fallback=false) {
		$define = 'SI_' . strtoupper($what);
		$method = 'get_' . strtolower($what);

		if (method_exists($this, $method)) return call_user_func(array($this, $method));

		if (isset($this->_overrides[$what])) return $this->_overrides[$what];
		if (defined($define)) return constant($define);

		return $fallback;
	}

	/**
	 * Sets override value
	 *
	 * @param string $what Value key to set
	 * @param mixed $value Value to set
	 *
	 * @return bool
	 */
	public function set ($what, $value) {
		if (!$this->can_override()) return false;

		$this->_overrides[$what] = $value;
		$_SESSION['si_overrides'][$what] = $value;

		return true;
	}

	/**
	 * Clears an override value
	 *
	 * @param string $what Value key to unset
	 *
	 * @return bool
	 */
	public function drop ($what) {
		if (!$this->can_override()) return false;

		unset($this->_overrides[$what]);
		if (array_key_exists($what, $this->_overrides)) return false;

		unset($_SESSION['si_overrides'][$what]);
		if (array_key_exists($what, $_SESSION['si_overrides'])) return false;

		return true;
	}

	/**
	 * Gets the path root define
	 *
	 * @return string Full path to root
	 */
	public function get_path_root () {
		$path = defined('SI_PATH_ROOT') && SI_PATH_ROOT
			? SI_PATH_ROOT
			: dirname(__FILE__)
		;
		$path = Si_Model_Fs::normalize_any($path);

		return preg_match('/\/(src|build)(\/|$)/', $path)
			? dirname($path)
			: $path
		;
	}

	/**
	 * Gets archive source file to extract
	 *
	 * @return mixed Archive file relative path as string, or (bool)false on failure
	 */
	public function get_archive () {
		$archive = '';
		$fs = Si_Model_Fs::path(false);
		$locations = array(
			str_repeat('[0-9a-f]', 12) . '.zip',    // Snapshot v4
			'full_*.zip',                           // Snapshot v3
			'build/data/*.zip',
		);

		foreach ($locations as $loc) {
			if (!$fs->exists_rx($loc)) continue;
			$archive = $fs->resolve_rx($loc);
		}

		return !empty($archive)
			? Si_Model_Fs::self_resolve($archive)
			: false
		;
	}

	/**
	 * Gets target directory relative path
	 *
	 * @return string Target directory
	 */
	public function get_target () {
		$fs = Si_Model_Fs::path(false);
		if ($fs->exists('/src') && $fs->exists('/build')) return 'build/target';
		return '';
	}

	/**
	 * Gets target URL
	 *
	 * @return string
	 */
	public function get_target_url () {
		if (!empty($this->_overrides[self::TARGET_URL])) return $this->_overrides[self::TARGET_URL];

		$target = $this->get(self::TARGET);
		return Si_Controller::get_url($target);
	}
}
