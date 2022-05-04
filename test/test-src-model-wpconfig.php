<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_ConfigConsumer')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_configconsumer.php');
if (!class_exists('Si_Model_Wpconfig')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_wpconfig.php');

class WpconfigModelTest extends PHPUnit_Framework_TestCase {

	public function test_exists () {
		$this->assertTrue(
			class_exists('Si_Model_Wpconfig'),
			"Config model not loaded"
		);
	}

	public function test_load_fail () {
		$m = Si_Model_Wpconfig::load('test');
		$this->assertTrue(
			$m instanceof Si_Model_Wpconfig,
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

	public function test_get_defaults () {
		$m = new Si_Model_Wpconfig;
		$d = $m->get_defaults();

		$this->assertTrue(array_key_exists('DB_NAME', $d));
		$this->assertTrue(array_key_exists('DB_USER', $d));
		$this->assertTrue(array_key_exists('DB_PASSWORD', $d));
		$this->assertTrue(array_key_exists('DB_HOST', $d));
		$this->assertTrue(array_key_exists('DB_CHARSET', $d));

		$this->assertSame($d['DB_HOST'], 'localhost');
		$this->assertSame($d['DB_CHARSET'], 'utf8');
	}

	public function test_load_success () {
		if (!class_exists('Si_Model_Fs')) {
			require_once(dirname(dirname(__FILE__)) . '/src/lib/Model/class_si_model_fs.php');
		}
		$d = Si_Model_Fs::load(dirname(__FILE__) . '/data/');
		$m = Si_Model_Wpconfig::load($d);

		$this->ensure_config_sanity($m);
	}

	public function test_consume () {
		$file = dirname(__FILE__) . '/data/' . Si_Model_Wpconfig::FILE_NAME;
		if (!file_exists($file)) return $this->fail("Missing required data: config");
		if (!is_readable($file)) return $this->fail("config not readable");

		$m = new Si_Model_Wpconfig;
		$this->assertTrue(
			$m->set_file($file),
			"File setting went fine"
		);

		$this->assertTrue(
			$m->consume(),
			"Consuming the config went fine"
		);

		$this->ensure_config_sanity($m);
	}

	public function ensure_config_sanity ($m) {
		$this->assertSame(
			$m->get('DB_NAME'), 'ms1'
		);
		$this->assertSame(
			$m->get('DB_USER'), 'root'
		);
		$this->assertSame(
			$m->get('DB_PASSWORD'), 'pass'
		);
		$this->assertSame(
			$m->get('DB_CHARSET'), 'utf8mb4'
		);

		$other = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
			'DOMAIN_CURRENT_SITE',
			'PATH_CURRENT_SITE',
		);
		foreach ($other as $define) {
			$test = $m->get($define);
			$this->assertFalse(empty($define), "Define {$define} not recognized");
		}

		// Raw updating check
		$this->assertTrue($m->update_raw('DB_NAME', 'test_database')); // Change values
		$this->assertTrue($m->parse()); // Re-parse raw
		$this->assertSame(
			$m->get('DB_NAME'), 'test_database',
			"Reparsed updated raw"
		);
	}

}
