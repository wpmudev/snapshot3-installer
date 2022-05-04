<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_Archive')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_archive.php');

class ArchiveModelTest extends PHPUnit_Framework_TestCase {

	public function test_exists () {
		$this->assertTrue(
			class_exists('Si_Model_Archive'),
			"Config model not loaded"
		);
	}

	public function test_load_fail () {
		$test = Si_Model_Archive::load(false);
		$this->assertTrue($test instanceof Si_Model_Archive, "Class object returned on invalid args");

		$res = $test->check();
		$this->assertFalse(empty($res));
		$this->assertTrue(is_string($res));
	}

	public function test_load_success () {
		$test = Si_Model_Archive::load(dirname(__FILE__) . '/data/full_test.zip');
		$this->assertTrue($test instanceof Si_Model_Archive, "Class object returned on valid args");

		$res = $test->check();
		$this->assertFalse(empty($res));
		$this->assertTrue(true === $res);
	}
}
