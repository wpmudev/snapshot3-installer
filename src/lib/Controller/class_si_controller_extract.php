<?php

class Si_Controller_Extract extends Si_Controller {

	public function run () {
		$archive = Si_Model_Archive::load($this->_env->get(Si_Model_Env::ARCHIVE));
		$destination = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));

		if ($destination->exists()) {
			$all = count($destination->ls());
			// Clean up manifest and config first
			if ($all > 0 && $all < 3) {
				$path = $destination->resolve('www/wp-config.php');
				if ($path && file_exists($path)) {
					$destination->rmdir();
				}

				$path = $destination->resolve(Si_Model_Manifest::FILE_NAME);
				if ($path && file_exists($path)) unlink($path);
			}
		}

		if (!$destination->exists() || $destination->is_empty()) {
			$this->_extract($archive, $destination);
		} else {
			$this->_request->set('state', 'files');
			$this->reroute();
		}

	}

	private function _extract ($archive, $destination) {
		$status = $archive->extract_to($destination);
		return $this->get_view()->out(array(
			'status' => $status,
			'progress' => array(
				'percentage' => 5,
				'action' => 'Extracting package',
			),
			'next_url' => $this->_request->to_query(), // Auto-reload to this, and let controller take over
			'cleanup_url' => $this->_request->get_query('state', 'cleanup'),
		));
	}

}
