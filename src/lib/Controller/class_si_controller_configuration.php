<?php

class Si_Controller_Configuration extends Si_Controller {

	public function run () {

		if (!empty($_POST)) {
			if (!empty($_POST['name'])) $this->_env->set('_dbname', $_POST['name']);
			else $this->_env->drop('_dbname');

			if (!empty($_POST['user'])) $this->_env->set('_dbuser', $_POST['user']);
			else $this->_env->drop('_dbuser');

			if (!empty($_POST['password'])) $this->_env->set('_dbpassword', $_POST['password']);
			else $this->_env->drop('_dbpassword');

			if (!empty($_POST['host']) || !empty($_POST['port'])) {
				$host = !empty($_POST['host']) ? $_POST['host'] : false;

				if (!empty($host)) {
					$port = !empty($_POST['port']) ? $_POST['port'] : false;
					if (!empty($port) && is_numeric($port)) {
						$host .= ':' . (int)$port;
					}
				}
				$this->_env->set('_dbhost', $host);
			} else $this->_env->drop('_dbhost');

			if (!empty($_POST['site-url'])) $this->_env->set(Si_Model_Env::TARGET_URL, $_POST['site-url']);
			else $this->_env->drop(Si_Model_Env::TARGET_URL);

			$this->reroute();
		}

		$archive = Si_Model_Archive::load($this->_env->get(Si_Model_Env::ARCHIVE));
		$source = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));

		$archive->extract_specific($source, array(
			'www/wp-config.php',
			Si_Model_Manifest::FILE_NAME,    // Snapshot v3
		));

		$manifest = Si_Model_Manifest::load($source);
		if ($manifest->has_file()) {
			// Snapshot v3
			$version = $manifest->get('SNAPSHOT_VERSION', '0.0');
			$files = $manifest->get_sources('fileset');
			$tables = $manifest->get_sources('tableset');

			$manifest_status = true
				&& version_compare($version, '3.0-alpha-1', 'ge')
				&& in_array('full', $files)
				&& !empty($tables)
			;
		} else {
			// Snapshot v4
			$manifest_status = true;
		}

		$source->chroot('www');
		$config = Si_Model_Wpconfig::load($source);

		$manifest_status = true;

		$config_status = true
			&& !!$config->has_file()
			&& !!strlen($config->get('DB_NAME', ''))
			&& !!strlen($config->get('DB_USER', ''))
			&& !!strlen($config->get('DB_HOST', ''))
			&& !!strlen($config->get('$table_prefix', ''))
		;

		// Now, try to connect
		$database_status = false;
		$db_connection_errno = false;
		$db_empty = false;
		if ($config_status) {
			$db = Si_Model_Database::from_config($config);
			$database_status = $db->connect();
			$db_connection_errno = $db->get_connection_error_code();
			$db_empty = $db->is_db_empty();
		}

		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));
		$database = array(
			'name' => $this->_env->get('_dbname', $config->get('DB_NAME', '')),
			'user' => $this->_env->get('_dbuser', $config->get('DB_USER', '')),
			'password' => $this->_env->get('_dbpassword', $config->get('DB_PASSWORD', '')),
			'host' => $this->_env->get('_dbhost', $config->get('DB_HOST', '')),
			'table_prefix' => $this->_env->get('_dbtable_prefix', $config->get('$table_prefix', '')),
		);

		return $this->get_view()->out(array(
			'status' => $manifest_status && $config_status && $database_status,
			'manifest_status' => $manifest_status,
			'has_manifest_file' => true,    // Snapshot v3/v4
			'deployment_directory' => $destination->get_root(),
			'site_url' => $this->_env->get(Si_Model_Env::TARGET_URL),
			'can_override' => $this->_env->can_override(),
			'config_status' => $config_status,
			'has_config_file' => $config->has_file(),
			'database_status' => $database_status,
			'db_connection_errno' => $db_connection_errno,
			'db_empty' => $db_empty,
			'config' => $config,
			'database' => $database,
			'next_url' => $this->_request->get_query('state', 'extract'),
			'this_url' => $this->_request->to_query(),
			'cleanup_url' => $this->_request->get_query('state', 'cleanup'),
		));
	}
}
