<?php

class Si_View_Partial_Mysqli extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>MySQLi</h4>
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
			<b>PHP MySQLi module not found</b>.
			Snapshot needs the MySQLi module to be installed and enabled
			on the target server. If you use a managed host, contact them
			directly to have this module installed and enabled.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
