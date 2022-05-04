<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_ConfigConsumer')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_configconsumer.php');
if (!class_exists('Si_Model_Manifest')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_manifest.php');

class ManifestModelTest extends PHPUnit_Framework_TestCase {

	public function test_exists () {
		$this->assertTrue(
			class_exists('Si_Model_Manifest'),
			"Manifest model not loaded"
		);
	}

	public function test_deep_trim () {
		$m = new Si_Model_Manifest;
		$source = array(
			'1' => 1,
			'1.0' => '1.0',
			' abcd ' => 'abcd',
			"\nggg" => 'ggg',
		);
		$result = $m->deep_trim($source);
		$this->assertTrue(
			is_array($result),
			"Deep trimming array returns array"
		);

		foreach ($source as $raw => $expected) {
			$this->assertSame(
				$expected, $m->deep_trim($raw),
				"Deep trimming failed to produce proper result for {$expected}"
			);
			$this->assertSame(
				$result[$raw], $expected,
				"Deep trimming array with values failed to produce proper result for {$expected}"
			);
		}
	}

	public function test_load_fail () {
		$m = Si_Model_Manifest::load('test');
		$this->assertTrue(
			$m instanceof Si_Model_Manifest,
			"Loader returns instance even for invalid args"
		);

		$this->assertFalse(
			$m->has_file(),
			"Invalid args result in no file"
		);

		$this->assertFalse(
			$m->consume(),
			"Invalid args cause consume to fail"
		);
	}

	public function test_interface () {
		$m = new Si_Model_Manifest;
		$this->assertFalse(
			$m->update_raw('test', 'test'),
			"Manifest model just sattisfies interface"
		);

		$test = 'test value';
		$this->assertSame(
			$m->get('test'), false,
			"Getting unset value returns false by default"
		);
		$this->assertSame(
			$m->get('test', 'fback'), 'fback',
			"Getting unset value returns fallback if set"
		);

		$this->assertSame(
			$m->set('test', $test), $test,
			"Setting values return value"
		);
		$this->assertSame(
			$m->get('test'), $test,
			"Set value gets got"
		);
	}

	public function test_get_queue () {
		$m = new Si_Model_Manifest;
		$q = $m->get_queue('test');

		$this->assertTrue(is_array($q), "Queue is always an array");
		$this->assertTrue(empty($q), "Invalid queue is always empty");
	}

	public function test_get_sources () {
		$m = new Si_Model_Manifest;
		$s = $m->get_sources('test');

		$this->assertTrue(is_array($s), "Sources are always an array");
		$this->assertTrue(empty($s), "Invalid queue sources are empty");
	}

	public function test_load_success () {
		if (!class_exists('Si_Model_Fs')) {
			require_once(dirname(dirname(__FILE__)) . '/src/lib/Model/class_si_model_fs.php');
		}
		$d = Si_Model_Fs::load(dirname(__FILE__) . '/data/');
		$m = Si_Model_Manifest::load($d);

		$this->ensure_manifest_sanity($m);
	}

	public function test_consume () {
		$file = dirname(__FILE__) . '/data/' . Si_Model_Manifest::FILE_NAME;
		if (!file_exists($file)) return $this->fail("Missing required data: manifest");
		if (!is_readable($file)) return $this->fail("Manifest not readable");

		$m = new Si_Model_Manifest;
		$this->assertTrue(
			$m->set_file($file),
			"File setting went fine"
		);

		$this->assertTrue(
			$m->consume(),
			"Consuming the manifest went fine"
		);

		$this->ensure_manifest_sanity($m);
	}

	public function ensure_manifest_sanity ($m) {
		$this->assertSame(
			$m->get('WP_VERSION'), '4.6.1',
			"Got proper WP version"
		);
		$this->assertSame(
			$m->get('WP_DB_VERSION'), '37965',
			"DB version getting"
		);

		$queues = $m->get('QUEUES');
		$this->assertSame(
			count($queues), 2,
			"Exactly two queues in a manifest"
		);

		$fileset = $m->get_queue('fileset');
		$this->assertTrue(
			is_array($fileset),
			"Queue is always an array"
		);
		$this->assertFalse(
			empty($fileset),
			"Proper queue is never empty"
		);

	}

}
