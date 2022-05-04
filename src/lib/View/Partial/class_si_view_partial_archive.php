<?php

class Si_View_Partial_Archive extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check php-version">
	<div class="check-title">
		<h4>Archive</h4>
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
			<b>Source archive not found</b>.
			The installer needs to be able to find and recognize a full backup
			snapshot archive. Please, download an archive from your Hub page and
			place it in the same directory as installer script.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
