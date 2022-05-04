<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_Env')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_env.php');

class EnvModelTest extends PHPUnit_Framework_TestCase {

	public function test_override () {
		$m = new Si_Model_Env;
		$this->assertTrue(
			$m->can_override()
		);
		$this->assertFalse(
			$m->has_overrides()
		);
	}

	public function test_get_set () {
		$m = new Si_Model_Env;
		$this->assertFalse($m->has_overrides());

		$this->assertFalse($m->get('test'));

		$this->assertTrue($m->set('test', 'test'));
		$this->assertSame($m->get('test'), 'test');

		$this->assertTrue($m->has_overrides());

		$this->assertTrue($m->drop('test'));
		$this->assertFalse($m->get('test'));
		$this->assertFalse($m->has_overrides());
	}

	public function test_paths () {
		if (!class_exists('Si_Model_Fs')) {
			require_once(dirname(dirname(__FILE__)) . '/src/lib/Model/class_si_model_fs.php');
		}
		$m = new Si_Model_Env;

		if (!defined('SI_PATH_ROOT')) {
			$path = $m->get_path_root();
			$this->assertSame(
				$path, realpath(dirname(dirname(__FILE__)) . '/src/lib')
			);
			$this->assertFalse(
				$m->get_archive()
			);
			//define('SI_PATH_ROOT', realpath(dirname(__FILE__) . '/data/'));
		}
/*
		if (defined('SI_PATH_ROOT')) {
			$path = $m->get_path_root();
			$this->assertSame(
				$path, SI_PATH_ROOT
			);
			$archive = $m->get_archive();
			$this->assertFalse(
				empty($archive)
			);
		}
 */
		$target = $m->get_target();
		$this->assertTrue(empty($target));
	}

	public function test_urls () {
		$m = new Si_Model_Env;

		$m->set(Si_Model_Env::TARGET_URL, 'test');
		$this->assertSame(
			$m->get_target_url(), 'test'
		);

		$m->drop(Si_Model_Env::TARGET_URL);
	}
}
