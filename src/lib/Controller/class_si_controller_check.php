<?php

class Si_Controller_Check extends Si_Controller {

	public function run () {
		$archive = $this->_env->get(Si_Model_Env::ARCHIVE);
		$archive_valid = Si_Model_Archive::load($archive)->check();
		$checks = array(
			'PhpVersion' => array(
				'test' => version_compare(PHP_VERSION, '5.2') >= 0,
				'value' => PHP_VERSION
			),
			'OpenBasedir' => array(
				'test' => !ini_get('open_basedir'),
			),
			'MaxExecTime' => array(
				'test' => 0 === (int)ini_get('max_execution_time') || (int)ini_get('max_execution_time') >= 150,
				'value' => (int)ini_get('max_execution_time'),
			),
			'Mysqli' => array(
				'test' => (bool)function_exists('mysqli_connect'),
			),
			'Zip' => array(
				'test' => class_exists('ZipArchive')
			),
			'Archive' => array(
				'test' => !empty($archive),
			),
			'ArchiveValid' => array(
				'test' => (true === $archive_valid),
				'value' => $archive_valid,
			),
		);

		$all_good = true;
		foreach ($checks as $check) {
			if (!empty($check['test'])) continue;
			$all_good = false;
			break;
		}
		if (!!$this->_request->get('preview')) $all_good = false; // Don't fast-forward in preview

		if ($all_good) {
			// We're good - just push forward
			$this->_request->set('state', 'configuration');
			$this->reroute();
		} else {
			// We have errors - render page
			$this->get_view()->out(array(
				'checks' => $checks,
			));
		}

	}
}
