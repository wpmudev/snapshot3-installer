<?php

class Si_Controller_Tables extends Si_Controller {

	public function run () {
		$source = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));
		$tables = $source->glob('sql/*.sql');   // Snapshot v4
		if (!count($tables)) {
			$tables = $source->glob('*.sql');   // Snapshot v3
		}

		$source->chroot('www');
		$config = Si_Model_Wpconfig::load($source);

		$db = Si_Model_Database::from_config($config);

		$chunk = $this->_request->get('chunk');
		$chunk = !empty($chunk) && is_numeric($chunk)
			? (int)$chunk
			: 0
		;

		$status = false;
		$error = false;

		$table = false;
		foreach ($tables as $idx => $table) {
			if ($idx < $chunk) continue;
			break;
		}

		if (!$db->connect()) {
			$status = false;
			$code = $db->get_connection_error_code();
			$error = "Unable to connect to database" . (!empty($code) ? " error code: {$code}" : '');
		} else {
			if (!empty($table)) {
				$status = $db->restore_from_file($table);
				if (!$status) {
					$tbl = basename($table, '.sql');
					$error = "Unable to restore table '{$tbl}' from '{$table}'";
					$reason = $db->last_query_error();
					if (!empty($reason)) $error .= ' because ' . $reason;
				}
			} else $error = 'Unable to determine table';
		}

		$next_url = $chunk >= count($tables)
			? $this->_request->get_clean_query('state', 'finalize', true)
			: $this->_request->get_query('chunk', $chunk+1)
		;

		$current = $chunk ? $chunk - 1 : 1;
		$percentage = (($current * 45) / count($tables));

		if ($percentage < 1 && $chunk > 1) $percentage = 45; // Normalize last step refresh

		$percentage += 50; // Extract (5%) and files(45%) being the previous step

		$this->get_view()->out(array(
			'status' => $status,
			'error' => $error,
			'progress' => array(
				'percentage' => $percentage,
				'action' => 'Restoring tables',
			),
			'next_url' => $next_url,
			'this_url' => $this->_request->to_query(),
			'cleanup_url' => $this->_request->get_query('state', 'cleanup'),
		));
	}
}
