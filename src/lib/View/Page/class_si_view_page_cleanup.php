<?php

class Si_View_Page_Cleanup extends Si_View_Page {

	public function get_title () {
		return '';
	}
	public function get_step () { return -1; }

	public function body ($params=array()) {
		?>

		<div class="step-output">

			<div class="cleanup-status">
				<h3>File Clean Up</h3>
			<?php if (!empty($params['status'])) { ?>
				<div class="success"><span>Complete</span></div>
			<?php } else { ?>
				<div class="warning"><span>Incomplete</span></div>
			<?php } ?>
			</div>

			<div class="cleanup-results-root">
				<div class="results">

					<div class="result-item temp">
						<div class="result-item-title">
							<?php echo $params['temp_path']; ?>
						</div>
						<div class="result-item-status">
						<?php if ($params['temp_status']) { ?>
							<div class="success"><span>Removed</span></div>
						<?php } else { ?>
							<div class="failed"><span>Fail</span></div>
						<?php } ?>
						</div>
					</div>

					<div class="result-item source">
						<div class="result-item-title">
							<?php echo $params['source_path']; ?>
						</div>
						<div class="result-item-status">
						<?php if ($params['source_status']) { ?>
							<div class="success"><span>Removed</span></div>
						<?php } else { ?>
							<div class="failed"><span>Fail</span></div>
						<?php } ?>
						</div>
					</div>

					<div class="result-item self">
						<div class="result-item-title">
							<?php echo $params['self_path']; ?>
						</div>
						<div class="result-item-status">
						<?php if ($params['self_status']) { ?>
							<div class="success"><span>Removed</span></div>
						<?php } else { ?>
							<div class="failed"><span>Fail</span></div>
						<?php } ?>
						</div>
					</div>

				</div>
			</div>

			<?php if (!empty($params['status'])) { ?>
			<div class="cleanup success">
				<div class="success">
					<p>
						All files were successfully cleaned up. Happy coding!
					</p>
				</div>
			</div>
			<?php } else { ?>
			<div class="cleanup warning">
				<div class="warning">
					<p>
						Some files couldn't be cleaned up. Please manually remove these from the server.
					</p>
				</div>
			</div>
		<?php } ?>
			<div class="cleanup actions">
				<p><a class="button" href="<?php echo $params['view_url']; ?>">View website</a></p>
			</div>
		</div>
		<?php
	}

}
