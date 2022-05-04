<?php

class Si_Model_Fs extends Si_Model {

	private $_root;

	private function __construct () {}

	public static function load ($path) {
		$me = new self;
		$me->set_root($path);
		return $me;
	}

	public static function path ($path) {
		$me = new self;
		$env = new Si_Model_Env;
		$me->set_root($env->get(Si_Model_Env::PATH_ROOT));

		$full = self::self_resolve($path);
		$real = self::normalize_real($full);
		if (!file_exists($real)) {
			$me->mkdir($path);
		}
		$me->set_root($real);
		return $me;
	}

	public static function temp ($relative_path) {
		$me = new self;
		$me->set_root(sys_get_temp_dir());

		if (!$me->exists($me->resolve($relative_path))) {
			$path = $me->mkdir($relative_path);
			$me->set_root($path);
		}

		return $me;
	}

	/**
	 * Normalize path convenience method
	 *
	 * @param string $path Path to normalize
	 * @param bool $fake Whether this should resolve to an already existing FS item
	 *
	 * @return mixed Full path as string on success, (bool)false on failure
	 */
	public static function normalize ($path, $fake=false) {
		return $fake
			? self::normalize_any($path)
			: self::normalize_real($path)
		;
	}

	/**
	 * Normalizes path to an existing FS item
	 *
	 * @param string $path Path to normalize
	 *
	 * @return mixed Full path as string on success, (bool)false on failure
	 */
	public static function normalize_real ($path) {
		$path = self::normalize_any(realpath($path));
		return !empty($path) && file_exists($path)
			? $path
			: false
		;
	}

	/**
	 * Normalizes path to a potentially non-existent FS item
	 *
	 * @param string $path Path to normalize
	 *
	 * @return mixed Full path as string on success, (bool)false on failure
	 */
	public static function normalize_any ($path) {
		if (!is_string($path)) return false;
		return str_replace('\\', '/', $path);
	}

	/**
	 * Adds trailing slash to string
	 *
	 * @param string $str Source
	 *
	 * @return string Source with singular trailing slash
	 */
	public static function trailing ($str) {
		return self::untrailing($str) . '/';
	}

	/**
	 * Strips trailing slash from a string
	 *
	 * @param string $str Sournce
	 *
	 * @return string Source without trailing slashes
	 */
	public static function untrailing ($str) {
		return rtrim($str, '\\/');
	}

	/**
	 * Resolves a relative path to a location within distribution folder
	 *
	 * @param string $relative_path Path relative to distribution folder
	 *
	 * @return mixed Full path as string on success, (bool)false on failure
	 */
	public static function self_resolve ($relative_path) {
		$env = new Si_Model_Env;
		$path = $env->get(Si_Model_Env::PATH_ROOT);
		return !empty($path)
			? self::_resolve($relative_path, $path)
			: false
		;
	}

	protected static function _resolve ($relative_path, $root) {
		return self::normalize_any(self::trailing($root) . $relative_path);
	}

	/**
	 * Root path getter
	 *
	 * @return string Root path
	 */
	public function get_root () {
		return $this->_root;
	}

	/**
	 * Root path setter
	 *
	 * @param string $path Root path
	 *
	 * @return bool
	 */
	public function set_root ($path) {
		return !!$this->_root = self::normalize_any($path);
	}

	/**
	 * Change FS object root to a directory within it
	 *
	 * @param string $relative_path Root-relative path to chroot to
	 *
	 * @return bool
	 */
	public function chroot ($relative_path) {
		$root = $this->resolve($relative_path);
		return file_exists($root)
			? $this->set_root($root)
			: false
		;
	}

	/**
	 * Resolves a relative path to a root-relative location
	 *
	 * @param string $relative_path Path relative to instance root
	 *
	 * @return mixed Full path as string on success, (bool)false on failure
	 */
	public function resolve ($relative_path) {
		$root = $this->get_root();
		return !empty($root)
			? self::_resolve($relative_path, $root)
			: false
		;
	}

	/**
	 * Check whether a path exists
	 *
	 * @param string $relative_path Optional root-relative path to check
	 *
	 * @return bool
	 */
	public function exists ($relative_path=false) {
		$path = $this->resolve($relative_path);
		return file_exists($path);
	}

	/**
	 * Check whether a path pattern exists
	 *
	 * @param string $pattern Pattern to check
	 *
	 * @return bool
	 */
	public function exists_rx ($pattern) {
		$path = self::trailing($this->get_root()) . $pattern;
		$set = glob($path);

		return !empty($set);
	}

	/**
	 * Resolves the pattern regex to an actual file location
	 *
	 * @param string $pattern Pattern to check
	 *
	 * @return mixed Pattern as string, or (bool)false on failure
	 */
	public function resolve_rx ($pattern) {
		$path = self::trailing($this->get_root()) . $pattern;
		$set = glob($path);

		$file = !empty($set) && is_array($set)
			? reset($set)
			: false
		;

		if (empty($file)) return $file;

		return $this->relative($file);
	}

	/**
	 * Check if a directory is empty
	 *
	 * @param string $relative_path Optional root-relative path to check
	 *
	 * @return bool
	 */
	public function is_empty ($relative_path=false) {
		$path = $this->resolve($relative_path);
		$handle = opendir($path);
		while ($file = readdir($handle)) {
		    if (in_array($file, array('.', '..'))) continue;
		    return false;
		}
		return true;
	}

	/**
	 * Recursive directory creation
	 *
	 * @param string $relative_path Optional root-relative path
	 *
	 * @return string Final created path
	 */
	public function mkdir ($relative_path=false) {
		if ($this->exists($relative_path)) return $this->resolve($relative_path);

		$normal = explode('/', self::normalize_any($relative_path));
		$path = $this->get_root();
		foreach ($normal as $frag) {
			$path = self::_resolve($frag, $path);
			if (file_exists($path) && is_dir($path)) continue;
			mkdir($path);
		}

		return $path;
	}

	/**
	 * Recursively remove directory and its contents
	 *
	 * @param string $relative_path Optional root-relative path
	 *
	 * @return bool
	 */
	public function rmdir ($relative_path=false) {
		if (!$this->exists($relative_path)) return true;

		$path = $this->resolve($relative_path);
		$list = array_diff(scandir($path), array('.', '..'));

		$status = true;

		foreach ($list as $item) {
			$tmp = self::_resolve($item, $path);
			if (is_dir($tmp)) {
				$rel = $this->relative($tmp);
				$this->rmdir($rel);
				$res = rmdir($tmp);
				if (!$res) $status = false;
			} else if (is_file($tmp)) {
				$res = unlink($tmp);
				if (!$res) $status = false;
			}
		}

		return $status;
	}

	/**
	 * Removes the relative path (file)
	 *
	 * @param string $relative_path Optional root-relative path
	 *
	 * @return bool
	 */
	public function rm ($relative_path=false) {
		if (!$this->exists($relative_path)) return true;

		$path = $this->resolve($relative_path);
		if (!is_file($path) || !is_writable($path)) return false;

		return unlink($path);
	}

	/**
	 * List FS items in a path (recursively)
	 *
	 * @param string $relative_path Optional root-relative path
	 *
	 * @return array List of items
	 */
	public function ls ($relative_path=false) {
		$path = self::trailing($this->resolve($relative_path));

		$data = array_diff(scandir($path), array('.', '..'));

		$subs = array();
		foreach ($data as $key => $value) {
			$tmp = self::_resolve($value, $path);
			if (is_dir($tmp)) {
				unset($data[$key]);
				$rel = $this->relative($tmp);
				$subs[] = $this->ls($rel);
			} else if (is_file($tmp))  {
				$data[$key] = $tmp;
			}
		}

		if (!empty($subs)) {
			foreach ($subs as $sub) {
				$data = array_merge($data, $sub);
			}
		}

		asort($data);
		return $data;
	}

	/**
	 * Lists FS items matching a pattern within a directory
	 *
	 * Non-recursive
	 *
	 * @param string $pattern Pattern to match
	 * @param string $relative_path Optional root-relative pattern
	 *
	 * @return array
	 */
	public function glob ($pattern, $relative_path=false) {
		$path = self::trailing($this->resolve($relative_path));

		$data = glob("{$path}{$pattern}");
		if (!is_array($data)) $data = array();
		asort($data);

		return $data;
	}

	/**
	 * Copy the entire paths
	 *
	 * Similar to cp -R, hence the method name
	 *
	 * @param Si_Model_Fs $source_fs Source FS model
	 * @param string $relpath Relative path (both source and destination)
	 *
	 * @return bool
	 */
	public function cpr (Si_Model_Fs $source_fs, $relpath) {
		$source_fullpath = self::normalize_real($source_fs->resolve($relpath));

		if (empty($source_fullpath)) return false;
		if (!is_file($source_fullpath) || !is_readable($source_fullpath)) return false;

		$destination_fullpath = $this->resolve($relpath);
		$dirname = dirname($relpath);
		if ('.' !== $dirname) $this->mkdir($dirname);

		$status = copy($source_fullpath, $destination_fullpath);

		return $status;
	}

	/**
	 * Converts an absolute path to root-relative form
	 *
	 * @param string $full_path Absolute path
	 * @param bool $fail Failure flag (optional) - return (bool)false if path is not root-relative
	 *
	 * @return string Root-relative path
	 */
	public function relative ($full_path, $fail=false) {
		$full_path = self::normalize_any($full_path);
		$root = self::trailing($this->get_root());

		if ($fail && !preg_match('/^' . preg_quote($root, '/') . '/', $full_path)) return false;

		return preg_replace('/' . preg_quote($root, '/') . '/', '', $full_path);
	}
}
