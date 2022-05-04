<?php

abstract class Si_View_Page_Deploy extends Si_View_Page {

	public function get_title () {
		return 'Deployment';
	}

	public function get_step () { return 3; }

	public function body ($params=array()) {
		$overall = !empty($params['status']);
		?>
	<div class="step-status">
	<?php if ($overall) { ?>
		<div class="warning"><span>In&nbsp;progress</span></div>
	<?php } else { ?>
		<div class="failed"><span>Failed</span></div>
	<?php } ?>
	</div>

	<div class="step-output">
	<?php
		if (!empty($overall)) {
			$this->_success($params);
		} else {
			$this->_failure($params);
		}
	?>
	</div>
		<?php
	}

	protected function _success ($params) {
		$progress = new Si_View_Partial_Progress;
		$progress_info = !empty($params['progress']) && is_array($params['progress'])
			? $params['progress']
			: array()
		;
		?>
		<div class="deployment">
			<header>
				<h3>Running Deployment</h3>
				<p>This will take a few minutes, please be patient.</p>
			</header>
			<?php $progress->out($progress_info); ?>
		</div>
	<?php if (!empty($params['next_url'])) { ?>
		<script>
		;(function () {
			setTimeout(function () {
				window.location = "<?php echo $params['next_url']; ?>";
			});
		})();
		</script>
<!--<a href="<?php echo $params['next_url']; ?>">Continue</a>-->
	<?php } ?>
		<?php
	}

	protected function _failure ($params) {
		$phase = !empty($params['progress']['action'])
			? $params['progress']['action']
			: false
		;
		$message = !empty($phase)
			? 'in &quot;' . $phase . '&quot; phase'
			: ''
		;
		$error = !empty($params['error'])
			? '<br />Reason: ' . $params['error']
			: 'due to an error'
		;
		?>
		<div class="deployment failure">
			<div class="error">
				<p>
					Snapshot failed to restore your website package
					<?php echo $message; ?>
					<?php echo $error; ?>
				</p>
			</div>
		<?php if (!empty($params['cleanup_url'])) { ?>
			<p><a class="button" href="<?php echo $params['cleanup_url']; ?>">Clean up files and try again</a></p>
		<?php } ?>
		</div>
		<?php
	}

}
