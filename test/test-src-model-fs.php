
<?php

if (!class_exists('Si_Model')) require_once(dirname(__FILE__) . '/../src/lib/class_si_model.php');
if (!class_exists('Si_Model_Fs')) require_once(dirname(__FILE__) . '/../src/lib/Model/class_si_model_fs.php');

class FsModelTest extends PHPUnit_Framework_TestCase {

	public function test_load () {
		$m = Si_Model_Fs::load(false);
		$this->assertTrue($m instanceof Si_Model_Fs);

		$m = Si_Model_Fs::load(dirname(__FILE__) . '/data/');
		$this->assertTrue($m instanceof Si_Model_Fs);

		$m = Si_Model_Fs::temp('/test/');
		$this->assertTrue($m instanceof Si_Model_Fs);
	}

	public function test_slashes () {
		$this->assertSame(
			Si_Model_Fs::untrailing('test/'), 'test'
		);
		$this->assertSame(
			Si_Model_Fs::trailing('test'), 'test/'
		);
		$this->assertSame(
			Si_Model_Fs::untrailing('test//'), 'test'
		);
	}

	public function test_normalize () {
		$any = array(
			'/test/path' => '/test/path',
			'/test\path' => '/test/path',
		);
		foreach ($any as $src => $exp) {
			$this->assertSame(Si_Model_Fs::normalize_any($src), $exp);
			$this->assertSame(Si_Model_Fs::normalize($src, true), $exp);
		}

		$path = dirname(__FILE__);
		$real = array(
			'/data/',
			'/../src/data/'
		);
		foreach ($real as $p) {
			$expected = realpath($path . $p);
			$this->assertSame(Si_Model_Fs::normalize($path . $p), $expected);
			$this->assertSame(Si_Model_Fs::normalize_real($path . $p), $expected);
		}
	}

	public function test_root () {
		$m = Si_Model_Fs::load(false);
		$this->assertFalse($m->get_root());

		$path = dirname(__FILE__) . '/data/';
		$m->set_root($path);
		$this->assertSame(
			$m->get_root(), $path
		);

		$m2 = Si_Model_Fs::load($path);
		$this->assertSame(
			$m2->get_root(), $path
		);
	}

	public function test_rx () {
		$m = Si_Model_Fs::load(dirname(__FILE__));
		$file_rx = 'snapshot_*.txt';

		$this->assertFalse($m->exists_rx($file_rx));
		$this->assertTrue($m->exists_rx("data/{$file_rx}"));

		$this->assertFalse($m->resolve_rx($file_rx));
		$this->assertSame(
			$m->resolve_rx("/data/{$file_rx}"),
			'/data/snapshot_manifest.txt'
		);
	}

	public function test_relative () {
		$path = realpath(dirname(__FILE__));
		$m = Si_Model_Fs::load($path);
		$test = array(
			"{$path}/data/" => 'data/',
			"/data/" => '/data/',
			"/test/path" => '/test/path'
		);
		foreach ($test as $src => $expected) {
			$this->assertSame($m->relative($src), $expected, $src);
		}
	}

	public function test_dir () {
		$path = realpath(dirname(__FILE__));
		$relpath = 'data/temp/temp2/temp3';
		$m = Si_Model_Fs::load($path);

		$this->assertFalse($m->is_empty());

		$this->assertFalse($m->exists($relpath));
		/*
		$this->assertSame(
			$m->mkdir($relpath), "{$path}/{$relpath}"
		);
		$this->assertTrue($m->exists($relpath));
		$this->assertTrue($m->is_empty($relpath));

		$this->assertTrue($m->rmdir($relpath));
		rmdir("{$path}/{$relpath}");
		$this->assertFalse($m->exists($relpath));
		 */
	}
}
