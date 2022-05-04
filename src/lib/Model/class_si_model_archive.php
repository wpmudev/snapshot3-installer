<?php

class Si_Model_Archive extends Si_Model {

	private $_archive_path;
	private $_zip;

	private function __construct () {
		if (class_exists('ZipArchive')) {
			$this->_zip = new ZipArchive;
		}
	}

	public static function load ($file) {
		$me = new self;
		$me->set_archive_path($file);
		return $me;
	}

	/**
	 * Sets archive path to fully qualified one
	 *
	 * @param string $file Path to archive file
	 */
	public function set_archive_path ($file) {
		$this->_archive_path = false;
		if (empty($file) || !file_exists($file)) return false;
		$file = Si_Model_Fs::normalize_real($file);

		if (!is_readable($file)) return $file;
		return !!$this->_archive_path = $file;
	}

	/**
	 * Checks zip archive validity
	 *
	 * @return string|bool Error string on failure, (bool)true on success
	 */
	public function check () {
		if (!class_exists('ZipArchive')) return "Unable to open archive - ZipArchive class is missing.";
		if (empty($this->_archive_path)) return "Archive file missing.";

		$status = true;
		$errors = array(
			ZipArchive::ER_EXISTS => 'File already exists.',
			ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
			ZipArchive::ER_INVAL => 'Invalid argument.',
			ZipArchive::ER_MEMORY => 'Malloc failure.',
			ZipArchive::ER_NOENT => 'No such file.',
			ZipArchive::ER_NOZIP => 'Not a zip archive.',
			ZipArchive::ER_OPEN => "Can't open file.",
			ZipArchive::ER_READ => 'Read error.',
			ZipArchive::ER_SEEK => 'Seek error.',
		);

		$handle = $this->_zip->open($this->_archive_path);
		if (true !== $handle) {
			$status = in_array($handle, array_keys($errors))
				? $errors[$handle]
				: "Generic archive open error"
			;
		}
		if (true === $handle) $this->_zip->close();

		return $status;
	}

	/**
	 * Extract *specific* files to a TMP-relative destination directory
	 *
	 * @param object $destination A Si_Model_Fs instance describing the destination
	 *
	 * @return bool
	 */
	public function extract_specific ($destination, $files) {
		$status = false;
		if (empty($this->_archive_path)) return $status;

		if (!($destination instanceof Si_Model_Fs)) return $status;
		if (!$destination->exists()) return false;

		if (empty($files)) return false;
		if (!is_array($files)) return false;

		$path = $destination->get_root();
		if (empty($path)) return false;

		$handle = $this->_zip->open($this->_archive_path);
		if (!$handle) return false;

		$status = $this->_zip->extractTo($path, $files);
		$this->_zip->close();

		return $status;
	}

	/**
	 * Extract *all* files to a TMP-relative destination directory
	 *
	 * @param object $destination A Si_Model_Fs instance describing the destination
	 *
	 * @return bool
	 */
	public function extract_to ($destination) {
		$status = false;
		if (empty($this->_archive_path)) return $status;

		if (!($destination instanceof Si_Model_Fs)) return $status;
		if (!$destination->exists()) return false;

		$path = $destination->get_root();
		if (empty($path)) return false;

		$handle = $this->_zip->open($this->_archive_path);
		$status = $this->_zip->extractTo($path);
		$this->_zip->close();

		return !!$status;
	}

}
