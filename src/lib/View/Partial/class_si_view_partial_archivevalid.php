<?php

class Si_View_Partial_ArchiveValid extends Si_View {

	public function out ($params=array()) {
		?>
<div class="check archive-validity">
	<div class="check-title">
		<h4>Archive Validity</h4>
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
			Your archive failed the validity check.
			We encountered this error while trying to open your archive: <code><?php echo $params['value']; ?></code>
			Please, try re-downloading your backup archive.
		</p>
	<?php } ?>
	</div>
</div>
		<?php
		return false;
	}
}
