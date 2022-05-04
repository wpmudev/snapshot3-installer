<?php

class Si_Controller_Finalize extends Si_Controller {

	public function run () {
		$target = Si_Model_Fs::temp($this->_env->get(Si_Model_Env::TEMP_DIR));
		if (!$target->exists()) {
			$status = false;
		}

		if ($this->_env->can_override()) {
			$this->_update_htaccess();
			$this->_update_config();
		}

		$this->_update_target();

		$this->get_view()->out(array(
			'status' => true,//$target->rmdir(),
			'progress' => array(
				'percentage' => 95,
				'action' => 'Finalizing installation'
			),
			'next_url' => $this->_request->get_query('state', 'done'),
			'this_url' => $this->_request->to_query(),
		));
	}

	private function _update_htaccess () {
		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));
		$htaccess = Si_Model_Htaccess::load($destination);
		$htaccess_changed = false;

		$base = $htaccess->get(Si_Model_Htaccess::REWRITE_BASE);
		$site_url = $this->_env->get(Si_Model_Env::TARGET_URL);
		$site_path = Si_Model_Fs::trailing(parse_url($site_url, PHP_URL_PATH));

		if ($base !== $site_path) {
			$htaccess->update_raw_base($site_path);
			$htaccess_changed = true;
		}

		if ($htaccess_changed) $htaccess->write();
	}

	private function _update_config () {
		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));
		$config = Si_Model_Wpconfig::load($destination);
		$config_changed = false;

		$_dbhost = $this->_env->get('_dbhost');
		if (!empty($_dbhost) && $_dbhost !== $config->get('DB_HOST')) {
			$config->update_raw('DB_HOST', $_dbhost);
			$config_changed = true;
		}

		$_dbuser = $this->_env->get('_dbuser');
		if (!empty($_dbuser) && $_dbuser !== $config->get('DB_USER')) {
			$config->update_raw('DB_USER', $_dbuser);
			$config_changed = true;
		}

		$_dbpassword = $this->_env->get('_dbpassword');
		if (!empty($_dbpassword) && $_dbpassword !== $config->get('DB_PASSWORD')) {
			$config->update_raw('DB_PASSWORD', $_dbpassword);
			$config_changed = true;
		}

		$_dbname = $this->_env->get('_dbname');
		if (!empty($_dbname) && $_dbname !== $config->get('DB_NAME')) {
			$config->update_raw('DB_NAME', $_dbname);
			$config_changed = true;
		}

		if ($config_changed) $config->write();
	}

	private function _update_target () {
		$destination = Si_Model_Fs::path($this->_env->get(Si_Model_Env::TARGET));
		$config = Si_Model_Wpconfig::load($destination);
		$db = Si_Model_Database::from_config($config);

		$site_url = $this->_env->get(Si_Model_Env::TARGET_URL);
		$pfx = $db->get_prefix();
		$db->query("UPDATE {$pfx}options SET option_value='{$site_url}' WHERE option_name='siteurl' LIMIT 1");
		$db->query("UPDATE {$pfx}options SET option_value='{$site_url}' WHERE option_name='home' LIMIT 1");

		// Delete Snapshot v4 running backup info
		$db->query("DELETE FROM {$pfx}options WHERE option_name='snapshot_running_backup'");
		$db->query("DELETE FROM {$pfx}options WHERE option_name='snapshot_running_backup_status'");
		$db->query("DELETE FROM {$pfx}sitemeta WHERE meta_key='snapshot_running_backup'");
		$db->query("DELETE FROM {$pfx}sitemeta WHERE meta_key='snapshot_running_backup_status'");

/*
		// Do the FS hardening step
		$files = $destination->ls();
		$pattern = '/' . preg_quote('/wp-content/', '/') . '/';
		foreach ($files as $file) {
			$perms = preg_match($pattern, $file)
				? (is_dir($file) ? 0766 : 0666)
				: (is_dir($file) ? 0755 : 0644)
			;
			@chmod($file, $perms);
		}
*/
	}
}
