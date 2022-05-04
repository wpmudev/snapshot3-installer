<?php

class Si_Controller_Files extends Si_Controller {

	public function run () {
		$status = true;

		$target = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));
		if (!$target->exists() || !$target->chroot('www')) {
			$status = false;
		}
		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));

		$chunk = $this->_request->get('chunk');
		$chunk = !empty($chunk) && is_numeric($chunk)
			? (int)$chunk
			: 0
		;
		$chunk_size = $this->_get_chunk_size();
		$offset = $chunk * $chunk_size;

		if ($status) $status = $destination->exists();
		$all_files = $files = array();

		if ($status) {
			$all_files = $target->ls();
			$files = array_slice($all_files, $chunk * $chunk_size, $chunk_size);
			if (empty($files) && empty($all_files)) $status = false;
		}

		$processed = array();

		if ($status && !empty($files)) {
			$tmp = false;
			foreach ($files as $path) {
				$source = $target->relative($path);
				$tmp = $destination->cpr($target, $source);
				if (!$tmp) {
					$status = false;
					break;
				}
				$processed[] = $source;
			}
		}

		$next_url = $offset >= count($all_files)
			? $this->_request->get_clean_query('state', 'tables', true)
			: $this->_request->get_query('chunk', $chunk+1)
		;

		$current = count($processed) * (!empty($chunk) ? $chunk : 1);
		$percentage = (($current * 45) / count($all_files));

		if ($percentage < 1 && $chunk > 1) $percentage = 45; // Normalize last step refresh

		$percentage += 5; // Extract (5%) being the previous step

		$this->get_view()->out(array(
			'status' => $status,
			'progress' => array(
				'percentage' => $percentage,
				'action' => 'Copying files',
			),
			'next_url' => $next_url,
			'cleanup_url' => $this->_request->get_query('state', 'cleanup'),
		));

	}

	private function _get_chunk_size () {
		return 250;
	}
}
