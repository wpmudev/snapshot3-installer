<?php

class Si_View_Partial_Zip extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>Zip</h4>
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
			<b>PHP Zip module not found</b>.
			To unpack the zip file, Snapshot needs the Zip module to be installed and enabled.
			If you use a managed host, contact them directly to have it updated.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
