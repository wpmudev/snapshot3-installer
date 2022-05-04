<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_Request')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_request.php');

class RequestModelTest extends PHPUnit_Framework_TestCase {

	public function test_exists () {
		$this->assertTrue(
			class_exists('Si_Model_Request'),
			"Config model not loaded"
		);
	}

	public function test_load () {
		$m = Si_Model_Request::load(null);
		$this->assertTrue(
			$m instanceof Si_Model_Request,
			"Loader returns instance even for invalid args"
		);
	}

	public function test_constants () {
		$consts = $this->get_expected_consts();
		foreach ($consts as $const) {
			$this->assertTrue(
				defined("Si_Model_Request::{$const}"),
				"Class constant {$const} defined"
			);
		}
	}

	public function test_loader_getter () {
		$consts = $this->get_expected_consts();
		$test_key = 'test';
		$test_val = 'test value';

		foreach ($consts as $const) {
			if (false === $this->set_superglobal($const, $test_key, $test_val)) continue;

			$m = Si_Model_Request::load(constant("Si_Model_Request::{$const}"));
			$this->assertSame(
				$m->get($test_key), $test_val,
				"Loading from {$const} failed"
			);
			$this->unset_superglobal($const, $test_key, $test_val);
		}
	}

	public function test_setter () {
		$test_key = 'test';
		$test_val = 'test value to check';
		$m = Si_Model_Request::load(Si_Model_Request::REQUEST_GET);

		$this->assertFalse(
			$m->get($test_key),
			"No initial value"
		);
		$m->set($test_key, $test_val);

		$this->assertSame(
			$m->get($test_key), $test_val,
			"Test value set"
		);
		$this->assertFalse(
			isset($_GET[$test_key]),
			"Superglobal left alone"
		);
	}

	public function test_query_converter () {
		$test_key = 'test';
		$test_val = 1312;

		$m1 = Si_Model_Request::load(Si_Model_Request::REQUEST_GET);
		$this->assertSame(
			$m1->to_query(), '',
			"Empty GET request results in empty query"
		);

		$this->set_superglobal('REQUEST_GET', $test_key, $test_val);
		$m2 = Si_Model_Request::load(Si_Model_Request::REQUEST_GET);
		$this->assertSame(
			$m2->to_query(), "?{$test_key}={$test_val}",
			"Query getting test successful"
		);
		$this->unset_superglobal('REQUEST_GET', $test_key, $test_val);
	}

	public function test_query_getters () {
		$tk1 = 'testkey1';
		$tv1 = 1312;
		$tk2 = 'testkey2';
		$tv2 = 161;

		$this->set_superglobal('REQUEST_GET', $tk1, $tv1);

		$m = Si_Model_Request::load(Si_Model_Request::REQUEST_GET);
		$this->assertFalse(
			$m->get($tk2),
			"Key {$tk2} not set initially"
		);
		$query = $m->get_query($tk2, $tv2);
		$this->assertSame(
			$query, "?{$tk1}={$tv1}&{$tk2}={$tv2}",
			"Query got"
		);
		$this->assertFalse(
			$m->get($tk2),
			"Value not set internally when using normal `get_query`"
		);
		$test = array();
		$test[$tk2] = $tv2;
		$this->assertSame(
			$m->get_query($test), "?{$tk1}={$tv1}&{$tk2}={$tv2}"
		);

		$query2 = $m->get_clean_query($tk2, $tv2);
		$this->assertSame(
			$query2, "?{$tk2}={$tv2}",
			"Clean query getter is not affected with data state"
		);
		$test2 = array();
		$test2[$tk2] = $tv2;
		$this->assertSame(
			$m->get_clean_query($test2), "?{$tk2}={$tv2}"
		);

		$this->unset_superglobal('REQUEST_GET', $tk1, $tv1);
	}

	public function test_strip () {
		$test = array(
			'1312' => 1312,
			'no quotes' => 'no quotes',
			"slashed \'" => get_magic_quotes_gpc() ? "slashed '" : "slashed \'",
			'slashed \"' => get_magic_quotes_gpc() ? 'slashed "' : 'slashed \"',
		);
		$this->assertSame(
			Si_Model_Request::strip(1312), 1312,
			"Slashing numbers works"
		);
		foreach ($test as $src => $expected) {
			$this->assertSame(
				Si_Model_Request::strip($src), $expected
			);
		}
		$res = Si_Model_Request::strip(array_keys($test));
		foreach (array_values($test) as $k => $expected) {
			$this->assertSame($res[$k], $expected);
		}
	}

	public function get_expected_consts () {
		return array(
			'REQUEST_GET',
			'REQUEST_POST',
			'REQUEST_COOKIE',
			'REQUEST_EMPTY',
		);
	}

	public function set_superglobal ($source, $key, $val) {
		switch ($source) {
			case 'REQUEST_GET':
				return $_GET[$key] = $val;

			case 'REQUEST_POST':
				return $_POST[$key] = $val;

			case 'REQUEST_COOKIE':
				return $_COOKIE[$key] = $val;

		}
		return false;
	}

	public function unset_superglobal ($source, $key, $val) {
		switch ($source) {
			case 'REQUEST_GET':
				unset($_GET[$key]);
				return true;

			case 'REQUEST_POST':
				unset($_POST[$key]);
				return true;

			case 'REQUEST_COOKIE':
				unset($_COOKIE[$key]);
				return true;

		}
		return false;
	}

}
