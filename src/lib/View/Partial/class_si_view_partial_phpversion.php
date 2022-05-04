<?php

class Si_View_Partial_PhpVersion extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>PHP Version</h4>
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
			Your PHP version is out of date.
			Your current version is <?php echo $params['value']; ?> and we require 5.2 or newer.
			You'll need to update your PHP version to proceed.
			If you use a managed host, contact them directly to have it updated.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
