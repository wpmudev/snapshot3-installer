<?php

/**
 * Deals with output logging
 */
class Si_Helper_Log {

	const FILENAME = 'si-error.log';

	static public function log ($msg) {
		$env = new Si_Model_Env;
		$file = Si_Model_Fs::trailing($env->get_path_root()) . self::FILENAME;
		$date = date("Y-m-d@H:i:sP");
		return error_log("[{$date}] {$msg}\n", 3, $file);
	}
}
