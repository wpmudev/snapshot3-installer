<?php
/**
 * Loads everything and bootstraps the restore process
 */

/**
 * Class loader function
 *
 * @param string $class Class to look for.
 *
 * @return bool
 */
function si_load_class ($class) {
	if (!preg_match('/^Si_/', $class)) return false;
	$rqsimple = preg_replace('/^Si_/', '', $class);

	$pathparts = explode('_', $rqsimple);
	$path = array();
	foreach ($pathparts as $part) {
		$path[] = $part;
	}
	array_pop($path);
	$rqsimple = strtolower($rqsimple);
	$rqfile = rtrim(join('/', $path), '/') . '/class_si_' . $rqsimple;

	$rqpath = dirname(__FILE__) . '/lib/' . $rqfile . '.php';
	if (!file_exists($rqpath)) {
		xd(array("{$rqpath} doesnot exist, for {$class}", debug_backtrace()));
		return false;
	}
	require_once $rqpath;

	if (!class_exists($class)) {
		xd(array("{$class} doesnot exist in {$rqfile}", debug_backtrace()));
		return false;
	}

	return true;
}
spl_autoload_register('si_load_class');

//ini_set('memory_limit','1024M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!function_exists('d')) {
	function d () { echo '<pre>'; var_export(func_get_args()); echo '</pre>'; }
}
if (!function_exists('xd')) {
	function xd () { d(func_get_args()); die; }
}

/**
 * Boots the standalone installer
 *
 * @return void
 */
function si_boot () {
	define('SI_PATH_ROOT', dirname(__FILE__));

	$dirname = 'si_test';

	$env = new Si_Model_Env;
	if ($env->can_override()) {
		$value = $env->get('temp_dir');
		if (!empty($value)) {
			$dirname = $value;
		} else {
			$dirname = uniqid($dirname);
			$env->set('temp_dir', $dirname);
		}
	}

	define('SI_TEMP_DIR', $dirname);

	$front = new Si_Controller_Install;
	$front->route();
}
si_boot();
