<?php

class Si_View_Page_Done extends Si_View_Page_Deploy {

	public function get_step () { return 4 ;}

	public function body ($params=array()) {
		?>
	<div class="step-status">
		<div class="success"><span>Complete</span></div>
	</div>

	<div class="step-output">
		<div class="deployment success">
			<div class="success">
				<p>
					Snapshot successfully deployed your new site!
					We recommend you quickly run the cleanup wizard.
				</p>
			</div>
		</div>
		<div class="deployment actions">
			<p><a class="button primary" href="<?php echo $params['cleanup_url']; ?>">Run Cleanup Wizard</a></p>
			<p><a class="button" href="<?php echo $params['view_url']; ?>">View website</a></p>
		</div>
	</div>
		<?php
	}
}
