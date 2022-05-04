<?php

class Si_View_Page_Configuration extends Si_View_Page {

	public function get_title () {
		return 'Configuration';
	}

	public function get_step () { return 2; }


	public function body ($params=array()) {
		$overall = !empty($params['status']);
		$has_override = true; // @TODO Fix this

		// Map: 100 = success, 50 = warning, 0 = stop, -1 = error
		$server_state = $db_state = $empty_state = 100; // Set all to "success"
		if (!empty($params['db_connection_errno'])) {
			$server_state = $db_state = $empty_state = 0; // Set all to "stop"

			// Server connection error
			if ((int)$params['db_connection_errno'] > 2000) $server_state = -1;
			else $server_state = 100;

			// User/pass combo error
			if (1045 === (int)$params['db_connection_errno']) {
				$server_state = 100;
				$db_state = -1;
			}

			// Not an existing DB
			if (1049 === (int)$params['db_connection_errno']) {
				$server_state = 100;
				$db_state = 100;
				$empty_state = -1;
			}

		}
		// Additional check
		// Not an empty database
		if (empty($params['db_empty'])) {
			$server_state = 100;
			$db_state = 100;
			$empty_state = 50;
		}

		?>
	<div class="step-status">
	<?php if ($overall) { ?>
		<?php if (100 == $server_state && 100 == $db_state && 100 == $empty_state) { ?>
			<div class="success"><span>Passed</span></div>
		<?php } else { ?>
			<div class="warning"><span>Warning</span></div>
		<?php } ?>
	<?php } else { ?>
		<div class="failed"><span>Failed</span></div>
	<?php } ?>
	</div>

	<div class="step-output">
	<form method="post">
		<p>
			The first step is to connect to your database.
			By default we recommend creating a new database,
			but you can choose to overwrite an existing database
			once you've tested the connection.
		</p>

	<?php if (empty($params['has_config_file'])) { ?>
		<div class="error-message">
			<p>We could not locate the <code>wp-config.php</code> file in your backup.</p>
		</div>
	<?php } ?>
	<?php if (empty($params['has_manifest_file'])) { ?>
		<div class="error-message">
			<p>We could not locate the Snapshot manifest file in your backup.</p>
		</div>
	<?php } ?>

		<div class="config-database">
			<h3>Connect Database</h3>
			<?php
				$config = !empty($params['database'])
					? $params['database']
					: array()
				;
			?>
			<div class="config-item host">
				<?php
				// Get port and host info
				$config['host'] = !empty($config['host']) ? $config['host'] : '';
				$list = explode(':', $config['host'], 2);
				$host = $list[0];
				$port = !empty($list[1]) ? $list[1] : false;

				$cls = '';
				if (50 === $server_state) $cls = 'class="warning"';
				if (50 > $server_state) $cls = 'class="error"';
				?>
				<label for="host">
					<span>Database Host</span>
					<input id="host" <?php echo $cls; ?> name="host" value="<?php echo $host; ?>" placeholder="localhost" />
				</label>
				<label for="port">
					<span>Port</span>
					<input id="port" <?php echo $cls; ?> name="port" value="<?php echo $port; ?>" placeholder="3306" />
				</label>
			</div>

			<div class="config-item dbname">
				<?php
					$cls = '';
					if (100 === $server_state && 50 === $empty_state) $cls = 'class="warning"';
					if (100 === $server_state && 50 > $empty_state) $cls = 'class="error"';
				?>
				<label for="name">
					<span>Database Name</span>
					<input id="name" <?php echo $cls; ?> name="name" value="<?php echo !empty($config['name']) ? $config['name'] : false; ?>" placeholder="Enter a new or existing database" />
				</label>
			</div>

			<div class="config-item dbuser">
				<?php
					$cls = '';
					if (100 === $server_state && 50 === $db_state) $cls = 'class="warning"';
					if (100 === $server_state && 50 > $db_state) $cls = 'class="error"';
				?>
				<label for="user">
					<span>Database Username</span>
					<input id="user" <?php echo $cls; ?> name="user" value="<?php echo !empty($config['user']) ? $config['user'] : false; ?>" placeholder="Enter a valid database username" />
				</label>
			</div>

			<div class="config-item dbpassword">
				<label for="password">
					<span>Database Password</span>
					<input id="password" <?php echo $cls; ?> name="password" value="<?php echo !empty($config['password']) ? $config['password'] : false; ?>" placeholder="Enter the password for database user" />
				</label>
			</div>
		</div>

		<div class="config-settings">
			<h3>Settings</h3>
			<div class="config-item site-url">
				<label for="site-url">
					<span>New Site URL</span>
					<input id="site-url" name="site-url" value="<?php echo $params['site_url']; ?>" placeholder="Your new site URL" />
				</label>
			</div>
		</div>

<?php if (!empty($has_override)) { ?>
		<div class="config-test">
			<div class="results">

				<div class="result-item server">
					<div class="check-title">
						Connect to Server
					</div>

					<div class="check-status">
						<?php
							switch($server_state) {
								case 100: echo '<div class="success"><span>Connected</span></div>'; break;
								case 50: echo '<div class="warning"><span>Warning</span></div>'; break;
								case 0: echo '<div class="stopped"><span>Stopped</span></div>'; break;
								case -1: echo '<div class="failed"><span>Failed</span></div>'; break;
							}
						?>
					</div>

					<div class="check-output">
						<?php if (0 > $server_state) { ?>
							<p>We couldn't connect to the database host. Please check your details.</p>
							<?php if ((int)$params['db_connection_errno']) { ?>
								<p>Error code: <code><?php echo (int)$params['db_connection_errno']; ?></code>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

				<div class="result-item database">
					<div class="check-title">
						Connect to Database
					</div>

					<div class="check-status">
						<?php
							switch($db_state) {
								case 100: echo '<div class="success"><span>Connected</span></div>'; break;
								case 50: echo '<div class="warning"><span>Warning</span></div>'; break;
								case 0: echo '<div class="stopped"><span>Stopped</span></div>'; break;
								case -1: echo '<div class="failed"><span>Failed</span></div>'; break;
							}
						?>
					</div>

					<div class="check-output">
						<?php if (0 > $db_state) { ?>
							<p>The database username and/or password you entered were invalid.</p>
							<?php if ((int)$params['db_connection_errno']) { ?>
								<p>Error code: <code><?php echo (int)$params['db_connection_errno']; ?></code>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

				<div class="result-item check">
					<div class="check-title">
						Database Check
					</div>

					<div class="check-status">
						<?php
							switch($empty_state) {
								case 100: echo '<div class="success"><span>Connected</span></div>'; break;
								case 50: echo '<div class="warning"><span>Warning</span></div>'; break;
								case 0: echo '<div class="stopped"><span>Stopped</span></div>'; break;
								case -1: echo '<div class="failed"><span>Failed</span></div>'; break;
							}
						?>
					</div>

					<div class="check-output">
						<?php if (0 > $empty_state) { ?>
							<p>There doesn't seem to be such a database.</p>
							<?php if ((int)$params['db_connection_errno']) { ?>
								<p>Error code: <code><?php echo (int)$params['db_connection_errno']; ?></code>
							<?php } ?>
						<?php } else if (50 === $empty_state) { ?>
							<p>
								A database with this name already exists.
								By proceeding we will wipe and overwrite all existing data.
								We recommend creating a new database instead.
							</p>
							<?php if ((int)$params['db_connection_errno']) { ?>
								<p>Error code: <code><?php echo (int)$params['db_connection_errno']; ?></code>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

			</div>
		</div>
<?php } ?>

		<div class="config-actions">
			<button><?php echo empty($has_override) ? 'Test' : 'Re-test'; ?> connection</button>
		</div>

<?php if (!empty($overall)) { ?>
		<div class="continue">
			<p>
				By proceeding you are doing so at your own risk.
				We recommend you backup your database and files before continuing
				and if you are unsure about anything seek advice from our support staff.
			</p>
			<p><a class="button primary" href="<?php echo $params['next_url']; ?>">Deploy site</a></p>
		</div>
<?php } ?>
	</form>
	</div> <!-- .step-output -->
</div> <!-- .step -->
		<?php

		return $overall;
	}
}
