<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_ConfigConsumer')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_configconsumer.php');
if (!class_exists('Si_Model_Htaccess')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_htaccess.php');

class HtaccessModelTest extends PHPUnit_Framework_TestCase {

	public function test_exists () {
		$this->assertTrue(
			class_exists('Si_Model_Htaccess'),
			"Config model not loaded"
		);
	}

	public function test_process_content () {
		$failed = "this is a test, whatever do we do here is not really relevant";
		$acc = new Si_Model_Htaccess;
		$this->assertFalse($acc->process_content($failed));

		$raw = $acc->get_raw();
		$this->assertTrue(is_string($raw));
		$this->assertTrue(empty($raw));

		$data = $acc->get_data();
		$this->assertTrue(is_array($data));
		$this->assertTrue(empty($data));
	}

	public function test_load_fail () {
		$test = Si_Model_Htaccess::load('test');
		$this->assertTrue($test instanceof Si_Model_Htaccess, "Loading returns instance even with invalid data");

		$this->assertFalse(
			$test->has_file(),
			"Invalid args result in no file"
		);

		$this->assertFalse(
			$test->consume(),
			"Invalid args cause consume to fail"
		);

		$raw = $test->get_raw();
		$this->assertTrue(is_string($raw));
		$this->assertTrue(empty($raw));

		$data = $test->get_data();
		$this->assertTrue(is_array($data));
		$this->assertTrue(empty($data));
	}

	public function test_load_success () {
		if (!class_exists('Si_Model_Fs')) {
			require_once(dirname(dirname(__FILE__)) . '/src/lib/Model/class_si_model_fs.php');
		}
		$d = Si_Model_Fs::load(dirname(__FILE__) . '/data/');
		$test = Si_Model_Htaccess::load($d);

		$this->assertTrue($test instanceof Si_Model_Htaccess, "Loading returns instance with valid data");

		$this->assertTrue(
			$test->has_file(),
			"We have the file loaded, yay"
		);

		$this->assertTrue(
			$test->consume(),
			"Valid args cause consume to not fail"
		);

		$raw = $test->get_raw();
		$this->assertTrue(is_string($raw));
		$this->assertFalse(empty($raw));

		$data = $test->get_data();
		$this->assertTrue(is_array($data));
		$this->assertFalse(empty($data));
	}

	public function test_update () {
		if (!class_exists('Si_Model_Fs')) {
			require_once(dirname(dirname(__FILE__)) . '/src/lib/Model/class_si_model_fs.php');
		}
		$d = Si_Model_Fs::load(dirname(__FILE__) . '/data/');
		$test = Si_Model_Htaccess::load($d);

		$base = $test->get(Si_Model_Htaccess::REWRITE_BASE);
		$this->assertEquals('/', $base);

		$expected = 'test-domain-base';
		$this->assertFalse(!!preg_match('/' . preg_quote($expected, '/') . '/', $test->get_raw()));
		$test->update_raw_base($expected);
		$this->assertTrue(!!preg_match('/' . preg_quote($expected, '/') . '/', $test->get_raw()));
	}

}
