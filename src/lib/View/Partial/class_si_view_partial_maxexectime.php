<?php

class Si_View_Partial_MaxExecTime extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>Max Execution Time</h4>
	</div>

	<div class="check-status">
	<?php if (!empty($params['test'])) { ?>
		<div class="success"><span>Passed</span></div>
	<?php } else { ?>
		<div class="failed"><span>Failed</span></div>
	<?php } ?>
	</div>

	<div class="check-output">
	<?php if (empty($params['test'])) { ?>
		<p>
			<b><code>max_execution_time</code> is set to <?php echo $params['value']; ?> which is too low</b>.
			A minimum execution time of 150 seconds is recommended to give the migration process the
			best chance of succeeding. If you use a managed host, contact them directly to have it updated.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
