<?php

class Si_Controller_Cleanup extends Si_Controller {

	public function run () {
		$temp = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));
		$temp_status = $temp->exists()
			? $temp->rmdir()
			: false
		;

		$source_cleanup = $self_cleanup = false;
		$source_path = $self_path = false;
		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));
		if ($destination->exists()) {
			$source_path = $this->_env->get(Si_Model_Env::ARCHIVE);
			$archive = $destination->relative($source_path, true);
			if (!empty($archive)) $source_cleanup = $destination->rm($archive);

			$self_path = Si_Model_Fs::trailing(getcwd()) . basename($_SERVER['PHP_SELF']);
			$script = basename($self_path);
			if ($destination->exists($script)) $self_cleanup = $destination->rm($script);
			if ($destination->exists(Si_Helper_Log::FILENAME)) {
				$destination->rm(Si_Helper_Log::FILENAME);
			}
		}

		$this->get_view()->out(array(
			'status' => $temp_status && $source_cleanup && $self_cleanup,
			'temp_status' => $temp_status,
			'temp_path' => $temp->get_root(),
			'source_status' => $source_cleanup,
			'source_path' => $source_path,
			'self_status' => $self_cleanup,
			'self_path' => $self_path,
			'next_url' => $this->_request->get_query('state', ''),
			'this_url' => $this->_request->to_query(),
			'view_url' => $this->_env->get(Si_Model_Env::TARGET_URL),
		));
	}
}
