<?php

class Si_View_Partial_OpenBasedir extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>Open Base Dir</h4>
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
			<b><code>open_basedir</code> is enabled</b>.
			Issues can occur when this directive is enabled, and we recommend
			to disable this value in your php.ini file.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
